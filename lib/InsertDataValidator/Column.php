<?php

namespace InsertDataValidator;


class Column
{
    const TYPE_INTEGER   = 0;
    const TYPE_DECIMAL   = 1;
    const TYPE_STRING    = 2;
    const TYPE_DATE      = 3;
    const TYPE_DATETIME  = 4;
    const TYPE_TIME      = 5;
    const TYPE_TIMESTAMP = 6;
    const TYPE_YEAR      = 7;

    const DATA_TYPE_BIT        = 'bit';
    const DATA_TYPE_TINYINT    = 'tinyint';
    const DATA_TYPE_SMALLINT   = 'smallint';
    const DATA_TYPE_MEDIUMINT  = 'mediumint';
    const DATA_TYPE_INT        = 'int';
    const DATA_TYPE_BIGINT     = 'bigint';

    const DATA_TYPE_DECIMAL    = 'decimal';
    const DATA_TYPE_FLOAT      = 'float';
    const DATA_TYPE_DOUBLE     = 'double';

    const DATA_TYPE_DATE       = 'date';
    const DATA_TYPE_DATETIME   = 'datetime';
    const DATA_TYPE_TIME       = 'time';
    const DATA_TYPE_TIMESTAMP  = 'timestamp';
    const DATA_TYPE_YEAR       = 'year';

    const DATA_TYPE_CHAR       = 'char';
    const DATA_TYPE_VARCHAR    = 'varchar';
    const DATA_TYPE_BINARY     = 'binary';
    const DATA_TYPE_VARBINARY  = 'varbinary';
    const DATA_TYPE_TINYTEXT   = 'tinytext';
    const DATA_TYPE_TEXT       = 'text';
    const DATA_TYPE_MEDIUMTEXT = 'mediumtext';
    const DATA_TYPE_LONGTEXT   = 'longtext';
    const DATA_TYPE_TINYBLOB   = 'tinyblob';
    const DATA_TYPE_BLOB       = 'blob';
    const DATA_TYPE_MEDIUMBLOB = 'mediumblob';
    const DATA_TYPE_LONGBLOB   = 'longblob';
    const DATA_TYPE_ENUM       = 'enum';
    const DATA_TYPE_SET        = 'set';

    // @see https://dev.mysql.com/doc/refman/5.6/ja/numeric-type-overview.html
    // @see https://dev.mysql.com/doc/refman/5.6/ja/date-and-time-type-overview.html
    const MAX_VALUE = [
        self::DATA_TYPE_TINYINT   => 127, // 2 ^ 8 / 2 - 1
        self::DATA_TYPE_SMALLINT  => 32767, // 2 ^ 16 / 2 - 1
        self::DATA_TYPE_MEDIUMINT => 8388607, // 2 ^ 24 / 2 - 1
        self::DATA_TYPE_INT       => 2147483647, // 2 ^ 32 / 2 - 1
        self::DATA_TYPE_BIGINT    => 9223372036854775807, // 2 ^ 64 / 2 - 1
        self::DATA_TYPE_FLOAT     => 3.402823466E+38,
        self::DATA_TYPE_DOUBLE    => 1.7976931348623157E+308,
        //self::DATA_TYPE_YEAR_2     => '69',
        //self::DATA_TYPE_YEAR_4     => '2155',
        self::DATA_TYPE_DATE      => '9999-12-31',
        // TODO support fraction with greater than MySQL5.6
        self::DATA_TYPE_DATETIME  => '9999-12-31 23:59:59',
        self::DATA_TYPE_TIME      => '838:59:59',
        self::DATA_TYPE_TIMESTAMP => '2038-01-19 03:14:07',
    ];

    const MIN_VALUE = [
        self::DATA_TYPE_TINYINT   => -128, // -(2 ^ 8 / 2)
        self::DATA_TYPE_SMALLINT  => -32768, // -(2 ^ 16 / 2)
        self::DATA_TYPE_MEDIUMINT => -8388608, // -(2 ^ 24 / 2)
        self::DATA_TYPE_INT       => -2147483648, // (2 ^ 32 / 2)
        self::DATA_TYPE_BIGINT    => -9223372036854775808, // -(2 ^ 64 / 2)
        self::DATA_TYPE_FLOAT     => -3.402823466E+38,
        self::DATA_TYPE_DOUBLE    => 1.7976931348623157E+308,
        //self::DATA_TYPE_YEAR_2     => '70',
        //self::DATA_TYPE_YEAR_4     => '1970',
        self::DATA_TYPE_DATE      => '1000-01-01',
        // TODO support fraction with greater than MySQL5.6
        self::DATA_TYPE_DATETIME  => '1000-01-01 00:00:00',
        self::DATA_TYPE_TIME      => '-838:59:59',
        self::DATA_TYPE_TIMESTAMP => '1970-01-01 00:00:01',
    ];
}