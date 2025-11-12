<?php
namespace components\objects;

require_once __DIR__."/../dbconnector.php";

class User
{
    public $id;
    public $firstname;
    public $lastname;
    public $bdate;
    public $username;
    public $password;
    public $email;
    public $is_admin;

    static function check_credentials($username, $password)
    {
        return false;
    }

    static function check_combination($id,$username){
        $sql = "SELECT * FROM users WHERE username = '$username' AND id = $id LIMIT 1";
        $conn = connect();
        $result = $conn->query($sql);
        return $result->num_rows > 0;
    }

    static function getUserById($id)
    {
        try {
            $conn = connect();
            $id = (int)$id;
            // Prepared statement pro bezpečnost
            $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
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

            $user = new User();
            $user->id = isset($row['id']) ? (int)$row['id'] : null;
            $user->firstname = $row['firstname'] ?? null;
            $user->lastname = $row['lastname'] ?? null;
            $user->bdate = $row['bdate'] ?? null; // pokud chceš DateTime, můžeš zde použít new \DateTime(...)
            $user->username = $row['username'] ?? null;
            $user->password = $row['password'] ?? null;
            $user->email = $row['email'] ?? null;
            $user->is_admin = isset($row['is_admin']) ? (bool)$row['is_admin'] : false;

            $result->free();
            $stmt->close();
            $conn->close();

            return $user;
        } catch (\Throwable $e) {
            return null;
        }
    }


    static function getAllOrdered(): array
    {
        $users = [];

        try {
            $conn = connect();

            $sql = "SELECT * FROM users ORDER BY is_admin DESC, username ASC";
            $result = $conn->query($sql);

            if (!$result) {
                return $users;
            }

            while ($row = $result->fetch_assoc()) {
                $user = new User();
                $user->id = isset($row['id']) ? (int)$row['id'] : null;
                $user->firstname = $row['firstname'] ?? null;
                $user->lastname = $row['lastname'] ?? null;
                $user->bdate = $row['bdate'] ?? null;
                $user->username = $row['username'] ?? null;
                $user->password = $row['password'] ?? null;
                $user->email = $row['email'] ?? null;
                $user->is_admin = isset($row['is_admin']) ? (bool)$row['is_admin'] : false;

                $users[] = $user;
            }

            $result->free();
            $conn->close();
        } catch (\Throwable $e) {
            return $users;
        }

        return $users;
    }

    static function deleteById($id): bool
    {
        try {
            $conn = connect();
            $id = (int)$id;

            $sql = "DELETE FROM users WHERE id = ? LIMIT 1";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            $stmt->bind_param("i", $id);
            $result = $stmt->execute();

            $stmt->close();
            $conn->close();

            return $result;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insert() {
        $conn = connect();

        $sql = "
        INSERT INTO users (username, firstname, lastname, bdate, email, is_admin, password)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $this->is_admin = (int)$this->is_admin;

        $stmt->bind_param(
            "sssssis",
            $this->username,
            $this->firstname,
            $this->lastname,
            $this->bdate,
            $this->email,
            $this->is_admin,
            $this->password
        );

        $result = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $result;
    }

    /**
     * Aktualizuje data uživatele v databázi (bez hesla).
     * @return bool
     */
    public function update() {
        $conn = connect();

        $sql = "
        UPDATE users SET
            firstname = ?,
            lastname = ?,
            bdate = ?,
            email = ?,
            is_admin = ?
        WHERE id = ?
    ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $conn->close();
            return false;
        }

        $this->is_admin = (int)$this->is_admin;

        $stmt->bind_param(
            "ssssii",
            $this->firstname,
            $this->lastname,
            $this->bdate,
            $this->email,
            $this->is_admin,
            $this->id
        );

        $result = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $result;
    }

    /**
     * Aktualizuje pouze heslo uživatele.
     * @return bool
     */
    public function updatePassword() {
        if (empty($this->password) || empty($this->id)) {
            return false;
        }

        $conn = connect();
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $conn->close();
            return false;
        }

        $stmt->bind_param("si", $this->password, $this->id);
        $result = $stmt->execute();

        $stmt->close();
        $conn->close();

        return $result;
    }

    public function validateUser(){
        return true;
    }

}