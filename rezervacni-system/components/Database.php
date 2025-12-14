<?php
namespace components;

/**
 * The hostname or IP address of the database server.
 */
define('DB_HOST', 'localhost');

/**
 * The name of the specific database to connect to.
 */
define('DB_NAME', 'hroudmi5');

/**
 * The username used for database authentication.
 */
define('DB_USER', 'hroudmi5');

/**
 * The password used for database authentication.
 */
define('DB_PASS', 'webove aplikace');

/**
 * The character set used for the database connection (utf8mb4 is recommended).
 */
define('DB_CHARSET', 'utf8mb4');

/**
 * Class Database
 *
 * A Singleton wrapper class for the PDO database connection.
 * This class ensures that only one database connection instance exists throughout
 * the lifecycle of a script execution, optimizing resource usage.
 *
 * @package components
 */
class Database {
    /**
     * @var Database|null The single instance of the Database class.
     */
    private static $instance = null;

    /**
     * @var \PDO The PHP Data Objects (PDO) connection instance.
     */
    private $pdo;

    /**
     * Database constructor.
     *
     * Initializes the PDO connection with specific options:
     * - ERRMODE_EXCEPTION: Throws exceptions on errors.
     * - DEFAULT_FETCH_MODE: Returns arrays indexed by column name.
     * - EMULATE_PREPARES: Disables emulation for better security and native type handling.
     *
     * The constructor is private to prevent direct instantiation (Singleton pattern).
     *
     * @throws \PDOException If the connection fails.
     */
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new \PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Retrieves the single instance of the Database class.
     *
     * If the instance does not exist, it creates it. Otherwise, it returns the existing one.
     *
     * @return Database The Singleton database instance.
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Retrieves the active PDO connection object.
     *
     * This object can be used to prepare statements and execute queries.
     *
     * @return \PDO The PDO connection instance.
     */
    public function getConnection() {
        return $this->pdo;
    }
}