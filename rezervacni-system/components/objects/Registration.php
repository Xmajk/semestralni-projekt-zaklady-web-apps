<?php

namespace components\objects;

use components\Database;
use DateTime;
use Exception;
use PDOException;

require_once __DIR__ . "/Event.php";
require_once __DIR__ . "/User.php";
require_once __DIR__ . "/../Database.php";

/**
 * Class Registration
 *
 * Manages the many-to-many relationship between Users and Events.
 * This class handles the creation, deletion, and retrieval of registration records,
 * including logic for enforcing event capacities and preventing duplicate sign-ups.
 *
 * @package components\objects
 */
class Registration
{
    /**
     * @var int The unique identifier for the registration record.
     */
    public int $id;

    /**
     * @var int The ID of the user associated with this registration.
     */
    public int $id_user;

    /**
     * @var User The full User object associated with this registration.
     */
    public User $user;

    /**
     * @var int The ID of the event associated with this registration.
     */
    public int $id_event;

    /**
     * @var Event The full Event object associated with this registration.
     */
    public Event $event;

    /**
     * @var string The timestamp of when the registration was created.
     */
    public string $registration_datetime;

    /**
     * Creates a Registration object instance from a database row.
     * Note: This method automatically fetches the associated User and Event objects.
     *
     * @param array $data Associative array containing database column data.
     * @return Registration The hydrated Registration object.
     */
    public static function hydrate(array $data): Registration
    {
        $registration = new Registration();
        $registration->id = (int)$data['id'];
        $registration->id_user = (int)$data['id_user'];
        $registration->id_event = (int)$data['id_event'];
        $registration->event = Event::getById($registration->id_event);
        $registration->user = User::getUserById($registration->id_user);

        return $registration;
    }

    /**
     * Retrieves all registrations existing in the system, ordered by ID descending.
     *
     * @return Registration[] An array of Registration objects.
     * @throws Exception If the database query fails.
     */
    public static function getAllOrdered(): array
    {
        try {
            $registrations = [];
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT * FROM registrations ORDER BY id DESC");

            while ($row = $stmt->fetch()) {
                $registrations[] = self::hydrate($row);
            }
            return $registrations;
        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání registrací: " . $e->getMessage());
        }
    }

    /**
     * Counts the total number of registrations for a specific event.
     *
     * @param int $eventId The ID of the event to count.
     * @return int|null The count of registrations, or null on error context.
     * @throws Exception If the database query fails.
     */
    public static function numberOfRegistrationsByEventId(int $eventId): ?int
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT count(*) FROM registrations where id_event = ?");
            $stmt->execute([$eventId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Chyba při počítání registrací: " . $e->getMessage());
        }
    }

    /**
     * Checks if a specific user is already registered for a specific event.
     *
     * @param int $userId The ID of the user.
     * @param int $eventId The ID of the event.
     * @return bool|null True if a registration exists, false otherwise.
     * @throws Exception If the database query fails.
     */
    public static function existsByUserIdAndEventId(int $userId, int $eventId): ?bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT count(*) FROM registrations where id_event = ? and id_user = ?");
            $stmt->execute([$eventId, $userId]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (PDOException $e) {
            throw new Exception("Chyba při kontrole registrace: " . $e->getMessage());
        }
    }

    /**
     * Creates a new registration record transactionally.
     * This method utilizes database row locking (FOR UPDATE) to prevent race conditions
     * where multiple users might try to register for the last available slot simultaneously.
     *
     *
     *
     * @param Registration $registration The registration object containing the user and event IDs.
     * @return bool True if the registration was successful.
     * @throws Exception If the event is not found, the user is already registered, or capacity is full.
     */
    public static function createRegistration(Registration $registration): bool
    {
        $pdo = Database::getInstance()->getConnection();

        try {
            $pdo->beginTransaction();

            $stmtEvent = $pdo->prepare("SELECT capacity FROM events WHERE id = ? FOR UPDATE");
            $stmtEvent->execute([$registration->id_event]);
            $capacity = $stmtEvent->fetchColumn();

            if ($capacity === false) {
                throw new Exception("Událost nenalezena.");
            }
            $capacity = (int)$capacity;

            $stmtCheck = $pdo->prepare("SELECT count(*) FROM registrations WHERE id_event = ? AND id_user = ?");
            $stmtCheck->execute([$registration->id_event, $registration->id_user]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Uživatel je již registrován.");
            }

            if ($capacity > 0) {
                $stmtCount = $pdo->prepare("SELECT count(*) FROM registrations WHERE id_event = ?");
                $stmtCount->execute([$registration->id_event]);
                if ($stmtCount->fetchColumn() >= $capacity) {
                    throw new Exception("Kapacita události je naplněna.");
                }
            }

            $stmtInsert = $pdo->prepare("INSERT INTO registrations (id_event, id_user, register_time) VALUES (?, ?, ?)");
            $stmtInsert->execute([
                $registration->id_event,
                $registration->id_user,
                $registration->registration_datetime
            ]);

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new Exception("Chyba při vytváření registrace: " . $e->getMessage());
        }
    }

    /**
     * Removes a registration record from the database.
     *
     * @param Registration $registration The registration object to delete.
     * @return bool True if the deletion was successful.
     * @throws Exception If the database operation fails.
     */
    public static function deleteRegistration(Registration $registration): bool
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("DELETE FROM registrations WHERE id_event=? and id_user=?");
            $stmt->execute([
                $registration->id_event,
                $registration->id_user,
            ]);

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new Exception("Chyba při mazání registrace: " . $e->getMessage());
        }
    }

    /*===PROFILE===*/

    /**
     * Retrieves a list of events that a specific user has registered for.
     *
     * @param int $userId The ID of the user.
     * @return Event[] An array of Event objects.
     * @throws Exception If the database query fails.
     */
    public static function getEventsByUser(int $userId): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            $sql = "
                SELECT e.* FROM events e 
                INNER JOIN registrations r ON e.id = r.id_event 
                WHERE r.id_user = ?
                ORDER BY e.start_datetime DESC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);

            $events = [];
            while ($row = $stmt->fetch()) {
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

                $events[] = $event;
            }
            return $events;

        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání událostí uživatele: " . $e->getMessage());
        }
    }

    /**
     * Determines if a user is currently registered for an event.
     *
     * @param int $userId The ID of the user.
     * @param int $eventId The ID of the event.
     * @return bool True if the user is registered, false otherwise.
     * @throws Exception If the database query fails.
     */
    public static function isUserRegistered(int $userId, int $eventId): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT count(*) FROM registrations WHERE id_user = ? AND id_event = ?");
            $stmt->execute([$userId, $eventId]);

            return ((int)$stmt->fetchColumn()) > 0;
        } catch (PDOException $e) {
            throw new Exception("Chyba při kontrole registrace: " . $e->getMessage());
        }
    }

    /**
     * Retrieves all users registered for a specific event.
     *
     * @param int $eventId The ID of the event.
     * @return User[] An array of User objects, keyed by the registration ID.
     * @throws Exception If the database query fails.
     */
    public static function findEventRegistrationsByEventId(int $eventId): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "
                SELECT u.*, r.id as 'r_id' FROM users u 
                JOIN registrations r ON u.id = r.id_user 
                WHERE r.id_event = ?
                ORDER BY u.id ASC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$eventId]);

            $users = [];
            while ($row = $stmt->fetch()) {
                $r_id = $row['r_id'];
                $user = new User();
                $user->fill($row);
                $users[$r_id] = $user;
            }
            return $users;

        } catch (PDOException $e) {
            throw new Exception("Chyba při hledání registrací: " . $e->getMessage());
        }
    }

    /**
     * Retrieves a single registration record by its ID.
     *
     * @param int $id The ID of the registration.
     * @return Registration|null The Registration object, or null if not found.
     * @throws Exception If the database query fails.
     */
    public static function getRegistrationById(int $id): ?Registration
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);

            $row = $stmt->fetch();
            if (!$row) return null;

            return self::hydrate($row);
        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání registrace: " . $e->getMessage());
        }
    }
}