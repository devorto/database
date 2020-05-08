<?php

namespace Devorto\Database;

use DateTime;

/**
 * Interface Database
 *
 * @package Devorto\Database
 */
interface Database
{
    /**
     * Gets the underlying connection.
     *
     * @return mixed
     */
    public function getConnection();

    /**
     * Gets multiple rows from the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return array[] array of database rows, [column => value].
     */
    public function getData(string $sql, array $parameters = []): array;

    /**
     * Gets a single row from the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return array database row, column => value.
     */
    public function getRow(string $sql, array $parameters = []): array;

    /**
     * Gets a single value from the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return null|string|int|float|DateTime
     */
    public function getValue(string $sql, array $parameters = []);

    /**
     * Gets multiple values from the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return array|string[]|int[]|float[]|DateTime[]
     */
    public function getValues(string $sql, array $parameters = []): array;

    /**
     * Inserts a row in the database.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @return int|null Insert ID if there is a single column primary key null otherwise.
     */
    public function insert(string $sql, array $parameters = []): ?int;

    /**
     * Execute a query on the database.
     *
     * @param string $sql
     * @param array $parameters
     */
    public function query(string $sql, array $parameters = []): void;

    /**
     * Starts a database transaction.
     */
    public function startTransaction(): void;

    /**
     * Rolls back a transaction.
     */
    public function rollbackTransaction(): void;

    /**
     * Commits transaction.
     */
    public function commitTransaction(): void;
}
