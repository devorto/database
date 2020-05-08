<?php

namespace Devorto\Database;

use Exception;
use Throwable;

/**
 * Class SqlSrvException
 *
 * @package Devorto\Database
 */
class SqlSrvException extends Exception
{
    /**
     * SqlSrvException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        $error = sqlsrv_errors();
        if (!empty($error[0])) {
            $message = $error[0]['SQLSTATE'] . "\n\n" . $error[0]['message'];
            $code = is_numeric($error[0]['code']) ? (int)$error[0]['code'] : $code;
        } else {
            $message = empty($message) ? 'Unknown database error' : $message;
        }

        parent::__construct($message, $code, $previous);
    }
}
