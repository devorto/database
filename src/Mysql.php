<?php

namespace Devorto\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Class Mysql
 *
 * @package Devorto\Database
 */
class Mysql extends PdoImplementation
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $database;

    /**
     * Mysql constructor.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     */
    public function __construct(string $host, string $username, string $password, string $database)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if (empty($this->connection)) {
            try {
                $this->connection = new PDO(
                    sprintf('mysql:dbname=%s;host=%s', $this->database, $this->host),
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                    ]
                );
            } catch (PDOException $exception) {
                throw new RuntimeException('Connecting to database failed.', 0, $exception);
            }
        }

        return $this->connection;
    }
}
