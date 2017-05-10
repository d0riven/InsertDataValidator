<?php

namespace InsertDataValidator\MySql;


use InsertDataValidator\ColumnMetaDataInterface;
use InsertDataValidator\Exception\UnsupportedTypeException;

class ColumnMetaData implements ColumnMetaDataInterface
{
    const DATA_TYPE_BIT       = 'bit';
    const DATA_TYPE_TINYINT   = 'tinyint';
    const DATA_TYPE_SMALLINT  = 'smallint';
    const DATA_TYPE_MEDIUMINT = 'mediumint';
    const DATA_TYPE_INT       = 'int';
    const DATA_TYPE_BIGINT    = 'bigint';

    const DATA_TYPE_DECIMAL = 'decimal';
    const DATA_TYPE_FLOAT   = 'float';
    const DATA_TYPE_DOUBLE  = 'double';

    const DATA_TYPE_DATE      = 'date';
    const DATA_TYPE_DATETIME  = 'datetime';
    const DATA_TYPE_TIME      = 'time';
    const DATA_TYPE_TIMESTAMP = 'timestamp';
    const DATA_TYPE_YEAR      = 'year';

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


    /**
     * @var
     */
    private $columnName;
    /**
     * @var
     */
    private $schemaType;
    /**
     * @var
     */
    private $allowableNull;

    public function __construct($columnName, $schemaType, $allowableNull)
    {
        $this->columnName = strtolower($columnName);
        $this->schemaType = strtolower($schemaType);
        $this->allowableNull = strtolower($allowableNull);
    }

    public function extractDataType()
    {
        if (preg_match('/^(.*?)(\(.+\))?$/', $this->schemaType, $matches)) {
            return $matches[1];
        }
        assert(false, 'Unexpected schemaType format. schemaType = ' . $this->schemaType);
    }

    public function extractMaxLength()
    {
        if (preg_match('/^.*\((\d+)\).*$/', $this->schemaType, $matches)) {
            return (int)$matches[1];
        }
        assert(false, sprintf('Logic error. type = %s', $this->schemaType));
    }

    public function getMaxValue($dataType)
    {
        $maxValue = self::MAX_VALUE[$dataType];
        return $this->isUnsigned() ? $maxValue * 2 + 1 : $maxValue;
    }

    public function isUnsigned()
    {
        return stripos('unsigned', $this->schemaType) !== false;
    }

    public function getMinValue($dataType)
    {
        return $this->isUnsigned() ? 0 : self::MIN_VALUE[$dataType];
    }

    public function getRoughlyType($dataType)
    {
        if ($this->isTypeOfTinyInt($dataType) ||
            $this->isTypeOfSmallInt($dataType) ||
            $this->isTypeOfMediumInt($dataType) ||
            $this->isTypeOfInt($dataType) ||
            $this->isTypeOfBigInt($dataType)
        ) {
            return self::TYPE_INTEGER;
        }

        if ($this->isTypeOfFloat($dataType) ||
            $this->isTypeOfDouble($dataType) ||
            $this->isTypeOfDecimal($dataType)
        ) {
            return self::TYPE_DECIMAL;
        }

        if ($this->isTypeOfChar($dataType) ||
            $this->isTypeOfVarChar($dataType)
        ) {
            return self::TYPE_STRING;
        }
        if ($this->isTypeOfDate($dataType)) {
            return self::TYPE_DATE;
        }
        if ($this->isTypeOfDateTime($dataType)) {
            return self::TYPE_DATETIME;
        }
        if ($this->isTypeOfTime($dataType)) {
            return self::TYPE_TIME;
        }
        if ($this->isTypeOfYear($dataType)) {
            return self::TYPE_DATE;
        }
        if (
            $this->isTypeOfBit($dataType) ||
            $this->isTypeOfBinary($dataType) ||
            $this->isTypeOfVarBinary($dataType) ||
            $this->isTypeOfTinyText($dataType) ||
            $this->isTypeOfText($dataType) ||
            $this->isTypeOfMediumText($dataType) ||
            $this->isTypeOfLongText($dataType) ||
            $this->isTypeOfTinyBinary($dataType) ||
            $this->isTypeOfBlob($dataType) ||
            $this->isTypeOfMediumBlob($dataType) ||
            $this->isTypeOfLongBlob($dataType) ||
            $this->isTypeOfEnum($dataType) ||
            $this->isTypeOfSet($dataType)
        ) {
            throw new UnsupportedTypeException('Unsupported type. type = ' . $dataType);
        }

        throw new \UnexpectedValueException('Unexpected value. type = ' . $dataType);
    }

    public function isTypeOfTinyInt($dataType)
    {
        return $dataType === self::DATA_TYPE_TINYINT;
    }

    public function isTypeOfSmallInt($dataType)
    {
        return $dataType === self::DATA_TYPE_SMALLINT;
    }

    public function isTypeOfMediumInt($dataType)
    {
        return $dataType === self::DATA_TYPE_MEDIUMINT;
    }

    public function isTypeOfInt($dataType)
    {
        return $dataType === self::DATA_TYPE_INT;
    }

    public function isTypeOfBigInt($dataType)
    {
        return $dataType === self::DATA_TYPE_BIGINT;
    }

    public function isTypeOfFloat($dataType)
    {
        return $dataType === self::DATA_TYPE_FLOAT;
    }

    public function isTypeOfDouble($dataType)
    {
        return $dataType === self::DATA_TYPE_DOUBLE;
    }

    public function isTypeOfDecimal($dataType)
    {
        return $dataType === self::DATA_TYPE_DECIMAL;
    }

    public function isTypeOfChar($dataType)
    {
        return $dataType === self::DATA_TYPE_CHAR;
    }

    public function isTypeOfVarChar($dataType)
    {
        return $dataType === self::DATA_TYPE_VARCHAR;
    }

    public function isTypeOfDate($dataType)
    {
        return $dataType === self::DATA_TYPE_DATE;
    }

    public function isTypeOfDateTime($dataType)
    {
        return $dataType === self::DATA_TYPE_DATETIME;
    }

    public function isTypeOfTime($dataType)
    {
        return $dataType === self::DATA_TYPE_TIME;
    }

    public function isTypeOfYear($dataType)
    {
        return $dataType === self::DATA_TYPE_YEAR;
    }

    public function isTypeOfBit($dataType)
    {
        return $dataType === self::DATA_TYPE_BIT;
    }

    public function isTypeOfBinary($dataType)
    {
        return $dataType === self::DATA_TYPE_BINARY;
    }

    public function isTypeOfVarBinary($dataType)
    {
        return $dataType === self::DATA_TYPE_VARBINARY;
    }

    public function isTypeOfTinyText($dataType)
    {
        return $dataType === self::DATA_TYPE_TINYTEXT;
    }

    public function isTypeOfText($dataType)
    {
        return $dataType === self::DATA_TYPE_TEXT;
    }

    public function isTypeOfMediumText($dataType)
    {
        return $dataType === self::DATA_TYPE_MEDIUMTEXT;
    }

    public function isTypeOfLongText($dataType)
    {
        return $dataType === self::DATA_TYPE_LONGTEXT;
    }

    public function isTypeOfTinyBinary($dataType)
    {
        return $dataType === self::DATA_TYPE_TINYBLOB;
    }

    public function isTypeOfBlob($dataType)
    {
        return $dataType === self::DATA_TYPE_BLOB;
    }

    public function isTypeOfMediumBlob($dataType)
    {
        return $dataType === self::DATA_TYPE_MEDIUMBLOB;
    }

    public function isTypeOfLongBlob($dataType)
    {
        return $dataType === self::DATA_TYPE_LONGBLOB;
    }

    public function isTypeOfEnum($dataType)
    {
        return $dataType === self::DATA_TYPE_ENUM;
    }

    public function isTypeOfSet($dataType)
    {
        return $dataType === self::DATA_TYPE_SET;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function isAllowableNull()
    {
        return $this->allowableNull === 'yes';
    }

    public function isTypeOfTimeStamp($dataType)
    {
        return $dataType === self::DATA_TYPE_TIMESTAMP;
    }
}