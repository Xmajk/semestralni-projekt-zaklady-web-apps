<?php

namespace components\objects;
use DateTime;
use Exception;

require_once __DIR__ . "/Event.php";
require_once __DIR__ . "/User.php";
require_once __DIR__."/../dbconnector.php";

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
        $registration->id = $data['id'];
        $registration->id_user = $data['id_user'];
        $registration->id_event = $data['id_event'];
        $registration->event = Event::getById($registration->id_event);
        $registration->user = User::getUserById($registration->id_user);
        return $registration;
    }

    public static function getAllOrdered(): array
    {
        $registrations = [];
        $conn = connect();
        $sql = "SELECT * FROM registrations ORDER BY id DESC";

        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $registrations[] = self::hydrate($row);
            }
            $result->free();
        }

        $conn->close();
        return $registrations;
    }

    public static function numberOfRegistrationsByEventId(int $eventId): ?int
    {
        $conn = connect();
        $sql = "SELECT count(*) FROM registrations where id_event = ?";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->close();
            return null;
        }

        $stmt->bind_param("i", $eventId);
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

        $conn->close();
        return $row["count(*)"];
    }

    public static function existsByUserIdAndEventId(int $userId, int $eventId): bool|null{
        $conn = connect();
        $sql = "SELECT count(*) FROM registrations where id_event = ? and id_user = ?";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->close();
            return null;
        }

        $stmt->bind_param("ii", $eventId, $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            $conn->close();
            return null;
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $conn->close();
        return intval($row["count(*)"])!=0;
    }

    public static function createRegistration(Registration $registration): bool|null{
        $conn = connect();
        $conn->begin_transaction();

        try {
            $sqlEvent = "SELECT capacity FROM events WHERE id = ? FOR UPDATE";
            $stmtEvent = $conn->prepare($sqlEvent);
            $stmtEvent->bind_param("i", $registration->id_event);
            if (!$stmtEvent->execute()) {
                throw new Exception();
            }
            $resEvent = $stmtEvent->get_result();
            $eventData = $resEvent->fetch_assoc();
            $stmtEvent->close();

            if (!$eventData) {
                throw new Exception();
            }
            $capacity = (int)$eventData['capacity'];

            $sqlCheck = "SELECT count(*) FROM registrations WHERE id_event = ? AND id_user = ?";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bind_param("ii", $registration->id_event, $registration->id_user);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            $rowCheck = $resCheck->fetch_row();
            $stmtCheck->close();

            if ($rowCheck[0] > 0) {
                throw new Exception();
            }

            if ($capacity > 0) {
                $sqlCount = "SELECT count(*) FROM registrations WHERE id_event = ?";
                $stmtCount = $conn->prepare($sqlCount);
                $stmtCount->bind_param("i", $registration->id_event);
                $stmtCount->execute();
                $resCount = $stmtCount->get_result();
                $rowCount = $resCount->fetch_row();
                $stmtCount->close();

                if ($rowCount[0] >= $capacity) {
                    throw new Exception();
                }
            }

            $sql = "INSERT INTO registrations (id_event, id_user, register_time) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception();
            }

            $stmt->bind_param(
                "iis",
                $registration->id_event,
                $registration->id_user,
                $registration->registration_datetime
            );

            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $conn->close();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            $conn->close();
            return false;
        }
    }

    public static function deleteRegistration(Registration $registration): bool|null{
        $conn = connect();
        $conn->begin_transaction();

        try {
            $sql = "DELETE FROM registrations WHERE id_event=? and id_user=?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception();
            }

            $stmt->bind_param(
                "ii",
                $registration->id_event,
                $registration->id_user,
            );

            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $conn->close();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            $conn->close();
            return false;
        }
    }

    /*===PROFILE===*/
    public static function getEventsByUser(int $userId): array
    {
        $events = [];
        $conn = connect();

        $conn->autocommit(false);
        $conn->query("LOCK TABLES events e READ, registrations r READ");

        $sql = "
            SELECT e.* FROM events e 
            INNER JOIN registrations r ON e.id = r.id_event 
            WHERE r.id_user = ?
            ORDER BY e.start_datetime DESC
        ";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->rollback();
            $conn->query("UNLOCK TABLES");
            $conn->close();
            return [];
        }

        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $event = new Event();
                $event->id = (int)$row['id'];
                $event->name = $row['name'] ?? null;
                $event->description = $row['description'] ?? null;
                $event->location = $row['location'] ?? null;
                $event->start_datetime = $row['start_datetime'] ?? null;
                $event->registration_deadline = $row['registration_deadline'] ?? null;
                $event->image_filename = $row['image_filename'] ?? null;

                $events[] = $event;
            }
        }

        $stmt->close();
        $conn->commit();
        $conn->query("UNLOCK TABLES");
        $conn->close();

        return $events;
    }

    public static function isUserRegistered(int $userId, int $eventId): bool
    {
        $conn = connect();

        $conn->autocommit(false);
        $conn->query("LOCK TABLES registrations READ");

        $sql = "SELECT id FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->rollback();
            $conn->query("UNLOCK TABLES");
            $conn->close();
            return false;
        }

        $stmt->bind_param("ii", $userId, $eventId);
        $stmt->execute();
        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();
        $conn->commit();
        $conn->query("UNLOCK TABLES");
        $conn->close();

        return $exists;
    }

    public static function findEventRegistrationsByEventId(int $eventId): array
    {
        $users = [];
        $conn = connect();

        // Zachování konzistence transakcí a zámků jako v metodě getEventsByUser
        $conn->autocommit(false);
        // Zamykáme tabulky users a registrations pro čtení (s aliasy, pokud to DB driver podporuje, nebo bez)
        $conn->query("LOCK TABLES users u READ, registrations r READ");

        $sql = "
            SELECT u.*, r.id as 'r_id'FROM users u 
            JOIN registrations r ON u.id = r.id_user 
            WHERE r.id_event = ?
            ORDER BY u.id ASC
        ";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->rollback();
            $conn->query("UNLOCK TABLES");
            $conn->close();
            return [];
        }

        $stmt->bind_param("i", $eventId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                // Vytvoření instance User
                // Poznámka: Zde předpokládám, že třída User má veřejné vlastnosti
                // odpovídající sloupcům v databázi (stejně jako to děláš u Event).
                // Uprav přiřazení níže podle skutečných názvů sloupců v tabulce 'users'.

                $r_id = $row['r_id'];
                $user = new User();
                $user->fill($row);

                // Příklad mapování běžných polí (odkomentuj/uprav dle reality):
                // $user->username = $row['username'] ?? null;
                // $user->email = $row['email'] ?? null;
                // $user->name = $row['name'] ?? null;
                // $user->surname = $row['surname'] ?? null;

                // Pokud třída User nemá definované veřejné vlastnosti, ale používá magic metody,
                // nebo pokud chceš dynamicky přiřadit vše, co přišlo z DB:
                /*
                foreach ($row as $key => $value) {
                    if (property_exists($user, $key)) {
                        $user->$key = $value;
                    }
                }
                */

                $users[$r_id] = $user;
            }
        }

        $stmt->close();
        $conn->commit();
        $conn->query("UNLOCK TABLES");
        $conn->close();

        return $users;
    }

    public static function getRegistrationById(int $id): ?Registration
    {
        $conn = connect();
        $sql = "SELECT * FROM registrations WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->close();
            return null;
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                // Využijeme existující metodu hydrate, která se postará
                // i o načtení User a Event objektů
                $registration = self::hydrate($row);

                $stmt->close();
                $conn->close();
                return $registration;
            }
        }

        $stmt->close();
        $conn->close();
        return null;
    }
}