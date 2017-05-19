<?php

namespace InsertDataValidator;


use InsertDataValidator\Exception\UnsupportedDriverException;

class FactoryGenerator
{
    // @see http://php.net/manual/ja/pdo.drivers.php
    const DRIVER_NAME_MYSQL               = 'mysql';
    const DRIVER_NAME_PGSQL               = 'pgsql';
    const DRIVER_NAME_ORACLE              = 'oci';
    const DRIVER_NAME_SQLITE              = 'sqlite';
    const DRIVER_NAME_DBLIB_SYS_BASE      = 'sybase';
    const DRIVER_NAME_DBLIB_MS_SQL_SERVER = 'mssql';
    const DRIVER_NAME_DBLIB_FREE_TDS      = 'dblib';
    const DRIVER_NAME_FIRE_BIRD           = 'firebird';
    const DRIVER_NAME_IBM                 = 'ibm';
    const DRIVER_NAME_INFORMIX            = 'informix';
    const DRIVER_NAME_ODBC                = 'odbc';
    const DRIVER_NAME_MS_SQL_SERVER       = 'sqlsrv';
    const DRIVER_NAME_4D                  = '4D';

    public static function generateFactoryByPdo(\PDO $pdo)
    {
        $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        switch ($driverName) {
            case self::DRIVER_NAME_MYSQL:
                return new MySql\Factory();
                break;
            case self::DRIVER_NAME_PGSQL:
            case self::DRIVER_NAME_ORACLE:
            case self::DRIVER_NAME_SQLITE:
            case self::DRIVER_NAME_DBLIB_SYS_BASE:
            case self::DRIVER_NAME_DBLIB_MS_SQL_SERVER:
            case self::DRIVER_NAME_DBLIB_FREE_TDS:
            case self::DRIVER_NAME_FIRE_BIRD:
            case self::DRIVER_NAME_IBM:
            case self::DRIVER_NAME_INFORMIX:
            case self::DRIVER_NAME_ODBC:
            case self::DRIVER_NAME_MS_SQL_SERVER:
            case self::DRIVER_NAME_4D:
                throw new UnsupportedDriverException('unsupported driver. driver = ' . $driverName);
                break;
        }
        assert(false, 'unexpected driver. driver = ' . $driverName);
    }

}