<?php

namespace Devorto\Database;

use DateTime;
use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Class PdoImplementation
 *
 * @package Devorto\Database
 * For every PDO implementation only the getConnction() method (connection string) is different.
 */
abstract class PdoImplementation implements Database
{
    /**
     * @var PDO|null
     */
    protected $connection;

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
        try {
            $statement = $this->executeQuery($sql, $parameters);

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            if (empty($result)) {
                return [];
            }

            return array_map([$this, 'convertDatabaseData'], $result);
        } catch (PDOException $exception) {
            throw new RuntimeException('Query execution failed.', 0, $exception);
        }
    }

    /**
     * @param string $sql
     * @param array $parameters
     *
     * @return PDOStatement
     * @throws PDOException
     */
    protected function executeQuery(string $sql, array $parameters = []): PDOStatement
    {
        $statement = $this->getConnection()->prepare($sql);

        // Cleanup parameters to support extra types.
        foreach ($parameters as $key => $parameter) {
            $index = is_int($key) ? $key + 1 : $key;

            if (is_int($parameter)) {
                $statement->bindValue($index, $parameter, PDO::PARAM_INT);
            } elseif (is_bool($parameter)) {
                $statement->bindValue($index, $parameter, PDO::PARAM_BOOL);
            } elseif (is_string($parameter) || is_float($parameter)) {
                $statement->bindValue($index, $parameter, PDO::PARAM_STR);
            } elseif (null === $parameter) {
                $statement->bindValue($index, null, PDO::PARAM_NULL);
            } elseif ($parameter instanceof DateTime) {
                $statement->bindValue($index, $parameter->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            } else {
                throw new InvalidArgumentException('Unsupported parameter type.');
            }
        }

        $statement->execute();

        return $statement;
    }

    /**
     * @return PDO
     */
    abstract public function getConnection(): PDO;

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
        try {
            $statement = $this->executeQuery($sql, $parameters);

            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (empty($result)) {
                return [];
            }

            return array_map([$this, 'convertDatabaseValue'], $result);
        } catch (PDOException $exception) {
            throw new RuntimeException('Query execution failed.', 0, $exception);
        }
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
        try {
            $statement = $this->executeQuery($sql, $parameters);

            $data = $statement->fetch(PDO::FETCH_NUM);
            if (empty($data)) {
                return null;
            }

            return $this->convertDatabaseValue($data[0]);
        } catch (PDOException $exception) {
            throw new RuntimeException('Query execution failed.', 0, $exception);
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function convertDatabaseValue($value)
    {
        if (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $value) === 1) {
            try {
                return new DateTime($value);
            } catch (Exception $exception) {
                // Ignore it's probably just a string.
            }
        }

        return $value;
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
        try {
            $statement = $this->executeQuery($sql, $parameters);

            $data = [];

            $results = $statement->fetchAll(PDO::FETCH_NUM);
            if (!empty($results)) {
                foreach ($results as $result) {
                    $data[] = $this->convertDatabaseValue($result[0]);
                }
            }

            return $data;
        } catch (PDOException $exception) {
            throw new RuntimeException('Query execution failed.', 0, $exception);
        }
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
        try {
            $this->executeQuery($sql, $parameters);

            $lastId = $this->getConnection()->lastInsertId();

            return empty($lastId) ? null : (int)$lastId;
        } catch (PDOException $exception) {
            throw new RuntimeException('Query execution failed.', 0, $exception);
        }
    }

    /**
     * Execute a query on the database.
     *
     * @param string $sql
     * @param array $parameters
     */
    public function query(string $sql, array $parameters = []): void
    {
        try {
            $this->executeQuery($sql, $parameters);
        } catch (PDOException $exception) {
            throw new RuntimeException('Query execution failed.', 0, $exception);
        }
    }

    /**
     * Starts a database transaction.
     */
    public function startTransaction(): void
    {
        try {
            $this->getConnection()->beginTransaction();
        } catch (PDOException $exception) {
            throw new RuntimeException('Starting transaction failed.', 0, $exception);
        }
    }

    /**
     * Rolls back a transaction.
     */
    public function rollbackTransaction(): void
    {
        try {
            $this->getConnection()->rollBack();
        } catch (PDOException $exception) {
            throw new RuntimeException('Rolling back transaction failed.', 0, $exception);
        }
    }

    /**
     * Commits transaction.
     */
    public function commitTransaction(): void
    {
        try {
            $this->getConnection()->commit();
        } catch (PDOException $exception) {
            throw new RuntimeException('Committing transaction failed.', 0, $exception);
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function convertDatabaseData(array $data): array
    {
        return array_map([$this, 'convertDatabaseValue'], $data);
    }
}
