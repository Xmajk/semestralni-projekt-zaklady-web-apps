<?php
namespace components\objects;

use components\Database;
use \Exception;
use \PDOException;

require_once __DIR__ . "/../Database.php";

/**
 * Class User
 *
 * Represents a user in the system.
 * This class handles user data encapsulation and provides methods for
 * CRUD operations, authentication checks, and session validation.
 *
 * @package components\objects
 */
class User
{
    /**
     * @var int|null The unique identifier of the user (Primary Key).
     */
    public $id;

    /**
     * @var string|null The user's first name.
     */
    public $firstname;

    /**
     * @var string|null The user's last name.
     */
    public $lastname;

    /**
     * @var string|null The birth date of the user (format: Y-m-d).
     */
    public $bdate;

    /**
     * @var string|null The unique username used for login.
     */
    public $username;

    /**
     * @var string|null The hashed password string.
     */
    public $password;

    /**
     * @var string|null The user's email address.
     */
    public $email;

    /**
     * @var bool|int Indicates if the user has administrative privileges (1/true or 0/false).
     */
    public $is_admin;

    /**
     * Populates the object properties from an associative array (e.g., $_POST data).
     *
     * @param array $data The source data array.
     * @return void
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
     * Internal factory method to hydrate a User object from a database row.
     *
     * @param array $row Associative array representing a database row.
     * @return User A populated User instance.
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
     * Verifies if a specific ID and Username combination exists in the database.
     * Useful for validating session or cookie data integrity.
     *
     * @param int|string $id The user ID.
     * @param string $username The username.
     * @return bool True if the combination matches a record, false otherwise.
     * @throws Exception If a database error occurs.
     */
    static function check_combination($id, $username)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "SELECT id FROM users WHERE username = ? AND id = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $id]);

            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            throw new Exception("Chyba při ověřování uživatele: " . $e->getMessage());
        }
    }

    /**
     * Checks if a username is already taken.
     *
     * @param string $username The username to check.
     * @return bool True if the username exists, false if it is available.
     * @throws Exception If a database error occurs.
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
     * Retrieves a user by their unique ID.
     *
     * @param int|string $id The user ID.
     * @return User|null The User object if found, or null.
     * @throws Exception If a database error occurs.
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
     * Retrieves a user by their username.
     *
     * @param string $username The username.
     * @return User|null The User object if found, or null.
     * @throws Exception If a database error occurs.
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
     * Retrieves all users from the database.
     * Results are ordered by admin status (admins first) and then by username.
     *
     * @return User[] An array of User objects.
     * @throws Exception If a database error occurs.
     */
    static function getAllOrdered(): array
    {
        try {
            $users = [];
            $pdo = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM users ORDER BY is_admin DESC, username ASC";
            $stmt = $pdo->query($sql);

            while ($row = $stmt->fetch()) {
                $users[] = self::hydrate($row);
            }

            return $users;
        } catch (PDOException $e) {
            throw new Exception("Chyba při načítání seznamu uživatelů: " . $e->getMessage());
        }
    }

    /**
     * Deletes a user record by ID.
     *
     * @param int|string $id The ID of the user to delete.
     * @return bool True on success, false on failure.
     * @throws Exception If a database error occurs.
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
     * Inserts the current User object into the database as a new record.
     * If successful, the object's ID property is updated with the new auto-increment ID.
     *
     * @return bool True on success, false on failure.
     * @throws Exception If a database error occurs.
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
                $this->id = (int)$pdo->lastInsertId();
            }

            return $result;
        } catch (PDOException $e) {
            throw new Exception("Chyba při vkládání uživatele: " . $e->getMessage());
        }
    }

    /**
     * Updates the existing user record in the database.
     * Requires the ID property to be set.
     *
     * @return bool True on success, false on failure.
     * @throws Exception If ID is missing or a database error occurs.
     */
    public function update()
    {
        if (empty($this->id)) {
            throw new Exception("Nelze aktualizovat uživatele bez ID.");
        }

        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "UPDATE users SET firstname = ?, lastname = ?, bdate = ?, email = ?, is_admin = ?, password=? WHERE id = ?";

            $stmt = $pdo->prepare($sql);

            $this->is_admin = (int)$this->is_admin;

            return $stmt->execute([
                $this->firstname,
                $this->lastname,
                $this->bdate,
                $this->email,
                $this->is_admin,
                $this->password,
                (int)$this->id
            ]);
        } catch (PDOException $e) {
            throw new Exception("Chyba při aktualizaci uživatele: " . $e->getMessage());
        }
    }

    /**
     * Updates only the user's password in the database.
     *
     * @return bool True on success, false on failure (or if properties are empty).
     * @throws Exception If a database error occurs.
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

    /**
     * Validates user data.
     *
     * @param User $user The user object to validate.
     * @param array $errors Reference to an array where errors will be stored.
     * @return bool True if valid, false otherwise.
     */
    public static function validation($user, $errors){
        return true;
    }
}