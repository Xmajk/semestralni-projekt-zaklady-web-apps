<?php

namespace components\objects;

use components\Database;
use \Exception;
use \PDOException;

// Předpokládáme existenci těchto souborů
require_once __DIR__ . "/../Database.php";
require_once __DIR__ . "/../utils/date_time.php";

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


    public function fill(array $formData, bool $update=false): array{
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
        return array_merge($errors,$this->validate($update));
    }

    public function validate($update=false):array{
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
        if(!$update){
            // Předpokládáme, že funkce convertStringToDateTime existuje v utils/date_time.php
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
        $event->capacity = isset($row['capacity']) ? (int)$row['capacity'] : null;
        $event->price = isset($row['price']) ? (int)$row['price'] : null;
        return $event;
    }

    /**
     * Načte jednu událost z databáze podle jejího ID.
     * @param int $id ID události.
     * @return Event|null Vrací objekt Event, nebo null, pokud událost nebyla nalezena.
     */
    public static function getById(int $id): ?Event
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$id]);

            $row = $stmt->fetch();

            if (!$row) {
                return null;
            }

            return self::hydrate($row);
        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání události: " . $e->getMessage());
        }
    }

    public static function getAllOrdered(): array
    {
        try {
            $events = [];
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT * FROM events ORDER BY start_datetime DESC");

            while ($row = $stmt->fetch()) {
                $events[] = self::hydrate($row);
            }

            return $events;
        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání seznamu událostí: " . $e->getMessage());
        }
    }

    public static function countEvents(): int
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT count(*) FROM events");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Chyba při počítání událostí: " . $e->getMessage());
        }
    }

    public static function getPage(int $pageSize, int $page): array{
        // 1. Ošetření vstupu: Stránka musí být minimálně 1
        if ($page < 1) {
            $page = 1;
        }

        // 2. Výpočet offsetu (strana 1 má offset 0)
        $offset = ($page - 1) * $pageSize;

        try {
            $events = [];
            $pdo = Database::getInstance()->getConnection();

            // LIMIT a OFFSET v PDO jsou bezpečné pokud jsou int, ale pro jistotu je přetypujeme
            // Některé ovladače mají problém s bindováním LIMITu, takže vložení (int) hodnoty je bezpečné a spolehlivé
            $sql = "SELECT * FROM events ORDER BY start_datetime DESC LIMIT " . (int)$pageSize . " OFFSET " . (int)$offset;

            $stmt = $pdo->query($sql);

            while ($row = $stmt->fetch()) {
                $events[] = self::hydrate($row);
            }

            return $events;
        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání stránky událostí: " . $e->getMessage());
        }
    }

    /**
     * Uloží novou událost (nový záznam) do databáze.
     * @return bool Vrací true při úspěchu, false při selhání.
     */
    public function insert(): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "INSERT INTO events (name, description, location, start_datetime, registration_deadline, image_filename, price, capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $result = $stmt->execute([
                $this->name,
                $this->description,
                $this->location,
                $this->start_datetime,
                $this->registration_deadline,
                $this->image_filename,
                $this->price,
                $this->capacity
            ]);

            if ($result) {
                $this->id = (int)$pdo->lastInsertId();
                return true;
            }

            return false;
        } catch (PDOException $e) {
            throw new Exception("Chyba při vkládání události: " . $e->getMessage());
        }
    }

    public function update(): bool
    {
        if ($this->id === null) {
            throw new Exception("Nelze aktualizovat událost bez ID.");
        }

        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "UPDATE events SET name=?, description=?, location=?, start_datetime=?, registration_deadline=?, image_filename=?, price=?, capacity=? WHERE id=?";

            $stmt = $pdo->prepare($sql);

            return $stmt->execute([
                $this->name,
                $this->description,
                $this->location,
                $this->start_datetime,
                $this->registration_deadline,
                $this->image_filename,
                $this->price,
                $this->capacity,
                (int)$this->id
            ]);
        } catch (PDOException $e) {
            throw new Exception("Chyba při aktualizaci události: " . $e->getMessage());
        }
    }

    /**
     * Smaže událost z databáze podle zadaného ID.
     * @param int $id ID události ke smazání.
     * @return bool Vrací true při úspěchu, false při selhání.
     */
    public static function deleteById(int $id): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? LIMIT 1");
            return $stmt->execute([(int)$id]);
        } catch (PDOException $e) {
            throw new Exception("Chyba při mazání události: " . $e->getMessage());
        }
    }
}