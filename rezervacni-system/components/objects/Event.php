<?php

namespace components\objects;
require_once __DIR__."/../dbconnector.php";
require_once __DIR__."/../utils/date_time.php";

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


    public function fill(array $formData): array{
        $errors = [];
        $this->name = $formData["name"] ?? null;
        if(!isset($this->name)){
            $errors["name"] = "Toto pole je povinné";
        }
        $this->description = $formData["description"]??"";
        $this->location = $formData["location"]??null;
        if(!isset($this->location)){
            $errors["location"] = "Toto pole je povinné";
        }

        if(!isset($formData["start_datetime"])){
            $errors["start_datetime"] = "Toto pole je povinné";
        }else{
            $this->start_datetime = $formData["start_datetime"];
        }
        if(!isset($formData["registration_deadline"])){
            $errors["registration_deadline"] = "Toto pole je povinné";
        }else{
            $this->registration_deadline = $formData["registration_deadline"];
        }

        if(!isset($formData["capacity"])){
            $errors["capacity"] = "Toto pole je povinné.";
        }
        elseif(!is_numeric($formData["capacity"])){
            $errors["capacity"] = "Kapacita musí být číslo";
        }else{
            $this->capacity = intval($formData["capacity"]);
        }

        if(isset($formData["price"])){
            if(trim(strtolower($formData["price"]))=="zdarma"){
                $this->price=0;
                $formData["price"]=0;
            }
            if(!is_numeric($formData["price"])){
                $errors["price"] = "Cenam musí být číslo";
            }else{
                $this->price = intval($formData["price"]);
            }
        }else{
            $this->price = 0;
        }
        return array_merge($errors,$this->validate());
    }
    public function validate():array{
        $errors = [];

        if(isset($this->description)){
            if(strlen($this->description) > 1000){
                $errors["description"] = "Popis nesmí mít více jak 1000 znaků";
            }
        }

        if(isset($this->location)){
            if(strlen($this->location) > 100){
                $errors["location"] = "Místo nesmí mít více jak 100 znaků";
            }
        }

        if(isset($this->capacity)){
            if($this->capacity<0){
                $errors["capacity"] = "Kapacita musí být kladné číslo";
            }
        }

        if($this->price<0){
            $errors["price"] = "Cena musí být kladné číslo";
        }


        $pass=true;
        if(isset($this->registration_deadline)){
            if(convertStringToDateTime($this->registration_deadline)->getTimestamp()<=time()){
                $errors["registration_deadline"] = 'Datum a čas konce registrace musí být v budoucnosti';
                $pass=false;
            }
        }

        if(isset($this->start_datetime)){
            if(convertStringToDateTime($this->start_datetime)->getTimestamp()<= time()){
                $errors["start_datetime"] = 'Konec registrace musí být před začátkem';
                $pass=false;
            }
        }

        if ( $pass && isset($this->registration_deadline) && isset($this->start_datetime)){
            if(convertStringToDateTime($this->start_datetime)->getTimestamp()<convertStringToDateTime($this->registration_deadline)->getTimestamp()){
                $tmp = "Konec registrace musí být před začátkem";
                $errors["registration_deadline"] = $tmp;
            }
        }

        return $errors;
    }

    /*===DATABASE===*/
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

    public static function getAllOrdered(): array
    {
        $events = [];
        $conn = connect();
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

    public static function countEvents(): int
    {
        $events = [];
        $conn = connect();
        $sql = "SELECT count(*) FROM events";

        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $conn->close();
        return $row["count(*)"];
    }

    public static function getPage(int $pageSize, int $page): array{
        $page-=1;
        $events = [];
        $conn = connect();
        $sql = "SELECT * FROM events ORDER BY start_datetime DESC LIMIT " . (int)$pageSize . " OFFSET " . ((int)$page * (int)$pageSize);
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

    public function update(): bool
    {
        if ($this->id === null) {
            // Nelze aktualizovat záznam, který nemá ID
            return false;
        }

        $conn = connect();

        // Přidány sloupce price a capacity
        $sql = "
            UPDATE events SET 
                name = ?, 
                description = ?, 
                location = ?, 
                start_datetime = ?, 
                registration_deadline = ?, 
                image_filename = ?,
                price = ?,
                capacity = ?
            WHERE id = ?
        ";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->close();
            return false;
        }

        // Typy parametrů: s (string) x 6, i (integer) x 3 (price, capacity, id)
        $stmt->bind_param(
            "ssssssiii",
            $this->name,
            $this->description,
            $this->location,
            $this->start_datetime,
            $this->registration_deadline,
            $this->image_filename,
            $this->price,
            $this->capacity,
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