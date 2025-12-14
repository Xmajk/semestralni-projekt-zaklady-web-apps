<?php
namespace components\objects;

use components\Database;
use \Exception;
use \PDOException;

// Předpokládáme, že Database.php je ve správné cestě, případně uprav require
require_once __DIR__ . "/../Database.php";

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

    /**
     * Naplní objekt daty z pole (např. z formuláře)
     */
    public function fill(array $data)
    {
        $this->id = $data["id"] ?? null;
        $this->firstname = $data["firstname"] ?? null;
        $this->lastname = $data["lastname"] ?? null;
        $this->bdate = $data["bdate"] ?? null;
        $this->username = $data["username"] ?? null;
        $this->password = $data["password"] ?? null;
        $this->email = $data["email"] ?? null;
        $this->is_admin = $data["is_admin"] ?? 0;
    }

    /**
     * Pomocná metoda pro převedení řádku z DB na objekt User
     */
    private static function hydrate(array $row): User
    {
        $user = new User();
        $user->id = isset($row['id']) ? (int)$row['id'] : null;
        $user->firstname = $row['firstname'] ?? null;
        $user->lastname = $row['lastname'] ?? null;
        $user->bdate = $row['bdate'] ?? null;
        $user->username = $row['username'] ?? null;
        $user->password = $row['password'] ?? null;
        $user->email = $row['email'] ?? null;
        $user->is_admin = isset($row['is_admin']) ? (bool)$row['is_admin'] : false;
        return $user;
    }

    /**
     * Zkontroluje, zda sedí ID a username (pro ověření session/cookie)
     */
    static function check_combination($id, $username)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "SELECT id FROM users WHERE username = ? AND id = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $id]);

            // fetchColumn vrátí hodnotu sloupce nebo false
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            throw new Exception("Chyba při ověřování uživatele: " . $e->getMessage());
        }
    }

    /**
     * Zkontroluje, zda uživatelské jméno již existuje
     */
    static function check_username($username)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "SELECT id FROM users WHERE username = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);

            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            throw new Exception("Chyba při kontrole uživatelského jména: " . $e->getMessage());
        }
    }

    /**
     * Najde uživatele podle ID
     */
    static function getUserById($id)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$id]);

            $row = $stmt->fetch();

            if (!$row) {
                return null;
            }

            return self::hydrate($row);
        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání uživatele podle ID: " . $e->getMessage());
        }
    }

    /**
     * Najde uživatele podle Username
     */
    static function getUserByUsername($username)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);

            $row = $stmt->fetch();

            if (!$row) {
                return null;
            }

            return self::hydrate($row);
        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání uživatele podle jména: " . $e->getMessage());
        }
    }

    /**
     * Vrátí seznam všech uživatelů seřazený podle admin práv a jména
     */
    static function getAllOrdered(): array
    {
        try {
            $users = [];
            $pdo = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM users ORDER BY is_admin DESC, username ASC";
            $stmt = $pdo->query($sql); // Zde stačí query, nemáme parametry

            while ($row = $stmt->fetch()) {
                $users[] = self::hydrate($row);
            }

            return $users;
        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání seznamu uživatelů: " . $e->getMessage());
        }
    }

    /**
     * Smaže uživatele podle ID
     */
    static function deleteById($id): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? LIMIT 1");
            $result = $stmt->execute([(int)$id]);
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Chyba při mazání uživatele: " . $e->getMessage());
        }
    }

    /**
     * Vloží nového uživatele do databáze
     */
    public function insert()
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "INSERT INTO users (username, firstname, lastname, bdate, email, is_admin, password) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $this->is_admin = (int)$this->is_admin;

            $result = $stmt->execute([
                $this->username,
                $this->firstname,
                $this->lastname,
                $this->bdate,
                $this->email,
                $this->is_admin,
                $this->password
            ]);

            if ($result) {
                // Nastavíme ID nově vytvořeného záznamu
                $this->id = (int)$pdo->lastInsertId();
            }

            return $result;
        } catch (PDOException $e) {
            throw new Exception("Chyba při vkládání uživatele: " . $e->getMessage());
        }
    }

    /**
     * Aktualizuje data uživatele v databázi (bez hesla).
     */
    public function update()
    {
        if (empty($this->id)) {
            throw new Exception("Nelze aktualizovat uživatele bez ID.");
        }

        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "UPDATE users SET firstname = ?, lastname = ?, bdate = ?, email = ?, is_admin = ? WHERE id = ?";

            $stmt = $pdo->prepare($sql);

            $this->is_admin = (int)$this->is_admin;

            return $stmt->execute([
                $this->firstname,
                $this->lastname,
                $this->bdate,
                $this->email,
                $this->is_admin,
                (int)$this->id
            ]);
        } catch (PDOException $e) {
            throw new Exception("Chyba při aktualizaci uživatele: " . $e->getMessage());
        }
    }

    /**
     * Aktualizuje pouze heslo uživatele.
     */
    public function updatePassword()
    {
        if (empty($this->password) || empty($this->id)) {
            return false;
        }

        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);

            return $stmt->execute([
                $this->password,
                (int)$this->id
            ]);
        } catch (PDOException $e) {
            throw new Exception("Chyba při změně hesla: " . $e->getMessage());
        }
    }

    public static function validation($user, $errors){
        // Zde můžeš doplnit validaci, aktuálně vrací true
        return true;
    }
}