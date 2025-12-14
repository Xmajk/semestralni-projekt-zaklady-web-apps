<?php

namespace components\objects;

use components\Database;
use DateTime;
use Exception;
use PDOException;

require_once __DIR__ . "/Event.php";
require_once __DIR__ . "/User.php";
require_once __DIR__ . "/../Database.php";

class Registration
{
    public int $id;
    public int $id_user;
    public User $user;
    public int $id_event;
    public Event $event;

    public string $registration_datetime;

    public static function hydrate(array $data): Registration
    {
        $registration = new Registration();
        $registration->id = (int)$data['id'];
        $registration->id_user = (int)$data['id_user'];
        $registration->id_event = (int)$data['id_event'];
        // Načtení objektů User a Event (může vyvolat další DB dotazy)
        $registration->event = Event::getById($registration->id_event);
        $registration->user = User::getUserById($registration->id_user);

        return $registration;
    }

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

    public static function createRegistration(Registration $registration): bool
    {
        $pdo = Database::getInstance()->getConnection();

        try {
            $pdo->beginTransaction();

            // Zámek řádku události pro kontrolu kapacity (FOR UPDATE)
            $stmtEvent = $pdo->prepare("SELECT capacity FROM events WHERE id = ? FOR UPDATE");
            $stmtEvent->execute([$registration->id_event]);
            $capacity = $stmtEvent->fetchColumn();

            if ($capacity === false) {
                throw new Exception("Událost nenalezena.");
            }
            $capacity = (int)$capacity;

            // Kontrola existence registrace
            $stmtCheck = $pdo->prepare("SELECT count(*) FROM registrations WHERE id_event = ? AND id_user = ?");
            $stmtCheck->execute([$registration->id_event, $registration->id_user]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Uživatel je již registrován.");
            }

            // Kontrola kapacity
            if ($capacity > 0) {
                $stmtCount = $pdo->prepare("SELECT count(*) FROM registrations WHERE id_event = ?");
                $stmtCount->execute([$registration->id_event]);
                if ($stmtCount->fetchColumn() >= $capacity) {
                    throw new Exception("Kapacita události je naplněna.");
                }
            }

            // Vložení registrace
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
                // Manuální hydratace Eventu, protože Event::hydrate je private
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
                $user->fill($row); // User má veřejnou metodu fill
                $users[$r_id] = $user;
            }
            return $users;

        } catch (PDOException $e) {
            throw new Exception("Chyba při hledání registrací: " . $e->getMessage());
        }
    }

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