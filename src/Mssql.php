<?php

namespace Devorto\Database;

use DateTime;
use RuntimeException;

/**
 * Class Mssql
 *
 * @package Devorto\Database
 */
class Mssql implements Database
{
    /**
     * @var resource|null
     */
    protected $connection;

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
     * Mssql constructor.
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
     * Gets multiple rows from the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return array[] array of database rows, [column => value].
     */
    public function getData(string $sql, array $parameters = []): array
    {
        $connection = $this->getConnection();

        $resource = sqlsrv_query($connection, $sql, $parameters);
        if (false === $resource) {
            throw new RuntimeException('Query execution failed.', 0, new SqlSrvException());
        }

        $data = [];

        if (sqlsrv_has_rows($resource)) {
            while ($row = sqlsrv_fetch_array($resource, SQLSRV_FETCH_ASSOC)) {
                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * @return resource
     */
    public function getConnection()
    {
        if (empty($this->connection)) {
            $this->connection = sqlsrv_connect(
                $this->host,
                [
                    'Database' => $this->database,
                    'UID' => $this->username,
                    'PWD' => $this->password,
                    'CharacterSet' => 'UTF-8'
                ]
            );
            if (!is_resource($this->connection)) {
                throw new RuntimeException('Connecting to database failed.', 0, new SqlSrvException());
            }
        }

        return $this->connection;
    }

    /**
     * Gets a single row from the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return array database row, column => value.
     */
    public function getRow(string $sql, array $parameters = []): array
    {
        $connection = $this->getConnection();

        $resource = sqlsrv_query($connection, $sql, $parameters);
        if (false === $resource) {
            throw new RuntimeException('Query execution failed.', 0, new SqlSrvException());
        }

        if (sqlsrv_has_rows($resource)) {
            return sqlsrv_fetch_array($resource, SQLSRV_FETCH_ASSOC);
        }

        return [];
    }

    /**
     * Gets a single value from the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return null|string|int|float|DateTime
     */
    public function getValue(string $sql, array $parameters = [])
    {
        $connection = $this->getConnection();

        $resource = sqlsrv_query($connection, $sql, $parameters);
        if (false === $resource) {
            throw new RuntimeException('Query execution failed.', 0, new SqlSrvException());
        }

        if (sqlsrv_has_rows($resource)) {
            sqlsrv_fetch($resource);

            return sqlsrv_get_field($resource, 0);
        }

        return null;
    }

    /**
     * Gets multiple values from the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return array|string[]|int[]|float[]|DateTime[]
     */
    public function getValues(string $sql, array $parameters = []): array
    {
        $connection = $this->getConnection();

        $resource = sqlsrv_query($connection, $sql, $parameters);
        if (false === $resource) {
            throw new RuntimeException('Query execution failed.', 0, new SqlSrvException());
        }

        $data = [];

        if (sqlsrv_has_rows($resource)) {
            while ($row = sqlsrv_fetch_array($resource, SQLSRV_FETCH_NUMERIC)) {
                $data[] = $row[0];
            }
        }

        return $data;
    }

    /**
     * Inserts a row in the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return int|null Insert ID if there is a single column primary key null otherwise.
     */
    public function insert(string $sql, array $parameters = []): ?int
    {
        $connection = $this->getConnection();

        $resource = sqlsrv_query($connection, $sql . "; SELECT SCOPE_IDENTITY()", $parameters);
        if (false === $resource) {
            throw new RuntimeException('Query execution failed.', 0, new SqlSrvException());
        }

        sqlsrv_next_result($resource);
        sqlsrv_fetch($resource);

        $result = sqlsrv_get_field($resource, 0);

        return empty($result) ? null : (int)$result;
    }

    /**
     * Execute a query on the database.
     *
     * @param string $sql
     * @param array $parameters
     */
    public function query(string $sql, array $parameters = []): void
    {
        $connection = $this->getConnection();

        if (false === sqlsrv_query($connection, $sql, $parameters)) {
            throw new RuntimeException('Query execution failed.', 0, new SqlSrvException());
        }
    }

    /**
     * Starts a database transaction.
     */
    public function startTransaction(): void
    {
        if (false === sqlsrv_begin_transaction($this->getConnection())) {
            throw new RuntimeException('Starting transaction failed.', 0, new SqlSrvException());
        }
    }

    /**
     * Rolls back a transaction.
     */
    public function rollbackTransaction(): void
    {
        if (false === sqlsrv_rollback($this->getConnection())) {
            throw new RuntimeException('Rolling back transaction failed.', 0, new SqlSrvException());
        }
    }

    /**
     * Commits transaction.
     */
    public function commitTransaction(): void
    {
        if (false === sqlsrv_commit($this->getConnection())) {
            throw new RuntimeException('Committing transaction failed.', 0, new SqlSrvException());
        }
    }
}
