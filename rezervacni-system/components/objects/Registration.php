<?php

namespace components\objects;
use DateTime;

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
       $count = 0;
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

    public static function findEventRegistrationsByEventId(int $eventId):array{
        return array();
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

        $sql = "
        INSERT INTO registrations (id_event, id_user, register_time)
        VALUES (?, ?, ?)
    ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "iis",
            $registration->id_event,
            $registration->id_user,
            $registration->registration_datetime
        );

        $result = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $result;
    }

    public static function deleteRegistration(Registration $registration): bool|null{
        $conn = connect();

        $sql = "
        DELETE FROM registrations WHERE id_event=? and id_user=?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "ii",
            $registration->id_event,
            $registration->id_user,
        );

        $result = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $result;
    }
}