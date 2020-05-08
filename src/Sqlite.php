<?php

namespace Devorto\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Class Sqlite
 *
 * @package Devorto\Database
 */
class Sqlite extends PdoImplementation
{
    /**
     * @var string
     */
    protected $file;

    /**
     * Mysql constructor.
     *
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if (empty($this->connection)) {
            try {
                $this->connection = new PDO(
                    'sqlite:' . $this->file,
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]
                );
            } catch (PDOException $exception) {
                throw new RuntimeException('Connecting to database failed.', 0, $exception);
            }
        }

        return $this->connection;
    }
}
