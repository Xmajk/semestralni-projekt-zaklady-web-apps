<?php

namespace components\objects;

use components\Database;
use \Exception;
use \PDOException;

// We assume the existence of these files
require_once __DIR__ . "/../Database.php";
require_once __DIR__ . "/../utils/date_time.php";

/**
 * Class Event
 *
 * Represents a single event in the system.
 * Maps directly to the 'events' table in the database.
 * Handles data validation, hydration, and CRUD operations.
 *
 * @package components\objects
 */
class Event
{
    /**
     * @var int|null The unique identifier of the event (Primary Key).
     */
    public ?int $id = null;

    /**
     * @var string|null The name or title of the event.
     */
    public ?string $name = null;

    /**
     * @var string|null Detailed description of the event.
     */
    public ?string $description = null;

    /**
     * @var string|null Physical location or address where the event takes place.
     */
    public ?string $location = null;

    /**
     * @var string|null The start date and time of the event (format: Y-m-d H:i:s).
     */
    public ?string $start_datetime = null;

    /**
     * @var string|null The deadline for user registration (format: Y-m-d H:i:s).
     */
    public ?string $registration_deadline = null;

    /**
     * @var string|null The filename of the associated image (e.g., 'event_123.jpg').
     */
    public ?string $image_filename = null;

    /**
     * @var int|null Maximum number of attendees allowed.
     */
    public ?int $capacity = null;

    /**
     * @var int|null The price of admission (0 usually implies free).
     */
    public ?int $price = null;


    /**
     * Populates the object properties using an associative array (usually from $_POST).
     * performs initial type checking and existence validation.
     *
     * @param array $formData The raw data from the form.
     * @param bool $update Indicates if this is an update operation (defaults to false).
     * @return array An associative array of error messages. If empty, population was successful.
     */
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

    /**
     * Validates the logical constraints of the object properties.
     * Checks string lengths, numeric ranges, and date logical order (e.g., start date > deadline).
     *
     * @param bool $update If set to true, certain date checks might be skipped or handled differently.
     * @return array An associative array of validation errors.
     */
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
     * Internal helper method to hydrate an object instance from a database row.
     *
     * @param array $row Associative array representing a database row.
     * @return Event A populated instance of the Event class.
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
     * Retrieves a single event from the database by its ID.
     *
     * @param int $id The unique identifier of the event.
     * @return Event|null Returns the Event object if found, or null if not.
     * @throws Exception If there is a database error.
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

    /**
     * Retrieves all events from the database, ordered by start datetime descending.
     *
     * @return Event[] An array of Event objects.
     * @throws Exception If there is a database error.
     */
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

    /**
     * Counts the total number of events in the database.
     *
     * @return int The total count of events.
     * @throws Exception If there is a database error.
     */
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

    /**
     * Retrieves a specific page of events for pagination.
     *
     * @param int $pageSize The number of events per page.
     * @param int $page The current page number (1-based index).
     * @return Event[] An array of Event objects for the requested page.
     * @throws Exception If there is a database error.
     */
    public static function getPage(int $pageSize, int $page): array{
        // 1. Input sanitization: Page must be at least 1
        if ($page < 1) {
            $page = 1;
        }

        // 2. Offset calculation (page 1 has offset 0)
        $offset = ($page - 1) * $pageSize;

        try {
            $events = [];
            $pdo = Database::getInstance()->getConnection();

            // LIMIT and OFFSET in PDO are safe if they are ints, but casting ensures safety.
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
     * Inserts the current event object as a new record in the database.
     * Upon success, the object's ID property is updated with the new insert ID.
     *
     * @return bool True on success, false on failure.
     * @throws Exception If there is a database insert error.
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

    /**
     * Updates an existing event record in the database.
     * The object must have a valid ID set.
     *
     * @return bool True on success, false on failure.
     * @throws Exception If the ID is missing or a database error occurs.
     */
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
     * Deletes an event from the database by its ID.
     *
     * @param int $id The ID of the event to delete.
     * @return bool True on success, false on failure.
     * @throws Exception If there is a database error.
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