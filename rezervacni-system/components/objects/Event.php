<?php

namespace components\objects;
require_once __DIR__."/../dbconnector.php";

/**
 * Class Event
 * Reprezentuje jednu událost v systému.
 * Odpovídá tabulce 'events' v databázi.
 */
class Event
{
    // Vlastnosti odpovídající sloupcům v tabulce 'events'
    public ?int $id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $location = null;
    public ?string $start_datetime = null;
    public ?string $registration_deadline = null;
    public ?string $image_filename = null;
    public ?int $capacity = null;
    public ?int $price = null;

    /**
     * Interní pomocná funkce pro "hydrataci" objektu z databázového řádku.
     * @param array $row Asociativní pole dat z databáze.
     * @return Event Instance této třídy s naplněnými daty.
     */
    private static function hydrate(array $row): Event
    {
        $event = new Event();
        $event->id = (int)$row['id'];
        $event->name = $row['name'] ?? null;
        $event->description = $row['description'] ?? null;
        $event->location = $row['location'] ?? null;
        $event->start_datetime = $row['start_datetime'] ?? null;
        $event->registration_deadline = $row['registration_deadline'] ?? null;
        $event->image_filename = $row['image_filename'] ?? null;
        $event->capacity = (int)$row['capacity'] ?? null;
        $event->price = (int)$row['price'] ?? null;
        return $event;
    }

    /**
     * Načte jednu událost z databáze podle jejího ID.
     * @param int $id ID události.
     * @return Event|null Vrací objekt Event, nebo null, pokud událost nebyla nalezena.
     */
    public static function getById(int $id): ?Event
    {
        $conn = connect();
        $sql = "SELECT * FROM events WHERE id = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->close();
            return null;
        }

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $stmt->close();
            $conn->close();
            return null;
        }

        $result = $stmt->get_result();
        if (!$result || $result->num_rows === 0) {
            $stmt->close();
            $conn->close();
            return null;
        }

        $row = $result->fetch_assoc();
        $event = self::hydrate($row);

        $result->free();
        $stmt->close();
        $conn->close();

        return $event;
    }

    /**
     * Načte všechny události z databáze, seřazené podle data zahájení (nejnovější první).
     * @return array Pole objektů Event.
     */
    public static function getAllOrdered(): array
    {
        $events = [];
        $conn = connect();
        // Seřadíme události od nejnovějších po nejstarší
        $sql = "SELECT * FROM events ORDER BY start_datetime DESC";

        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $events[] = self::hydrate($row);
            }
            $result->free();
        }

        $conn->close();
        return $events;
    }

    /**
     * Uloží novou událost (nový záznam) do databáze.
     * @return bool Vrací true při úspěchu, false při selhání.
     */
    public function insert(): bool
    {
        $conn = connect();
        $sql = "
            INSERT INTO events (name, description, location, start_datetime, registration_deadline, image_filename, price, capacity)
            VALUES (?, ?, ?, ?, ?, ?,?,?)
        ";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->close();
            return false;
        }

        $stmt->bind_param(
            "ssssssii",
            $this->name,
            $this->description,
            $this->location,
            $this->start_datetime,
            $this->registration_deadline,
            $this->image_filename,
            $this->price,
            $this->capacity
        );

        $result = $stmt->execute();

        if ($result) {
            $this->id = $conn->insert_id;
        }else{
            return false;
        }

        $stmt->close();
        $conn->close();

        return true;
    }

    /**
     * Aktualizuje existující událost v databázi na základě ID tohoto objektu.
     * @return bool Vrací true při úspěchu, false při selhání.
     */
    public function update(): bool
    {
        if ($this->id === null) {
            // Nelze aktualizovat záznam, který nemá ID
            return false;
        }

        $conn = connect();
        $sql = "
            UPDATE events SET 
                name = ?, 
                description = ?, 
                location = ?, 
                start_datetime = ?, 
                registration_deadline = ?, 
                image_filename = ?
            WHERE id = ?
        ";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->close();
            return false;
        }

        $stmt->bind_param(
            "ssssssi",
            $this->name,
            $this->description,
            $this->location,
            $this->start_datetime,
            $this->registration_deadline,
            $this->image_filename,
            $this->id
        );

        $result = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $result;
    }

    /**
     * Smaže událost z databáze podle zadaného ID.
     * @param int $id ID události ke smazání.
     * @return bool Vrací true při úspěchu, false při selhání.
     */
    public static function deleteById(int $id): bool
    {
        $conn = connect();
        $sql = "DELETE FROM events WHERE id = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->close();
            return false;
        }

        $stmt->bind_param("i", $id);
        $result = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $result;
    }
}