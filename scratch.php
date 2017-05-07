<?php

/**
 * クラスを使わない構成で一旦書いてみて、実装の手順を確認する
 */

require_once 'vendor/autoload.php';

use InsertDataValidator\Column;
use InsertDataValidator\Exception\UnsupportedTypeException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as RespectValidation;

// tests at local development
$database = 'test';
$host = 'localhost';
$dsn = sprintf('mysql:dbname=%s;host=%s', $database, $host);
$pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_CASE              => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false
    ]
);

validate($pdo, 'a', ['id' => '1', 'data' => '4'], []);

function validate(PDO $pdo, $tableName, array $insertData, array $ignoreValidationColumns = [])
{
    $errors = [];
    $schema = fetchTableSchema($pdo, $tableName);
    // Format validation
    foreach ($schema as $c) {
        $columnName = $c['field'];
        if (array_key_exists($columnName, $insertData) === false ||
            in_array($columnName, $ignoreValidationColumns, true)
        ) {
            continue;
        }
        try {
            validateByColumnMetaData($c, $insertData[$columnName], $columnName);
        } catch (ValidationException $e) {
            $errors[] = sprintf('`%s` is not valid. reason = [%s]', $columnName, $e->getMainMessage());
        }
    }
    // ghost column validation
    $tableColumns = array_map(function ($c) {
        return strtolower($c);
    }, array_column($schema, 'field'));
    $ghostColumns = array_diff(array_keys($insertData), $tableColumns);
    if (count($ghostColumns) > 0) {
        $errors[] = sprintf('Insert data has ghost column. ghosts = [`%s`]', implode('`,`', $ghostColumns));
    }

    if ($errors !== []) {
        throw new Exception(
            sprintf('Exists violation of table schema. table_name = %s, errors = %s',
                $tableName, print_r($errors, true))
        );
    }
}

function fetchTableSchema(PDO $pdo, $tableName)
{
    $stmt = $pdo->query("SHOW COLUMNS FROM $tableName");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function extractDataType($schemaTypeOf)
{
    if (preg_match('/^(.*?)(\(.+\))?$/', $schemaTypeOf, $matches)) {
        return strtolower($matches[1]);
    }
    assert(false, 'Unexpected schemaType format. schemaType = ' . $schemaTypeOf);
}

function isUnsigned($schemaTypeOf)
{
    return stripos('unsigned', $schemaTypeOf) !== false;
}

function getRoughlyType($dataType)
{
    if (isTypeOfTinyInt($dataType) ||
        isTypeOfSmallInt($dataType) ||
        isTypeOfMediumInt($dataType) ||
        isTypeOfInt($dataType) ||
        isTypeOfBigInt($dataType)
    ) {
        return Column::TYPE_INTEGER;
    }

    if (isTypeOfFloat($dataType) ||
        isTypeOfDouble($dataType) ||
        isTypeOfDecimal($dataType)
    ) {
        return Column::TYPE_DECIMAL;
    }

    if (isTypeOfChar($dataType) ||
        isTypeOfVarChar($dataType)
    ) {
        return Column::TYPE_STRING;
    }
    if (isTypeOfDate($dataType) ||
        isTypeOfDateTime($dataType) ||
        isTypeOfTime($dataType) ||
        isTypeOfTimeStamp($dataType) ||
        isTypeOfYear($dataType)
    ) {
        return Column::TYPE_DATE;
    }
    if (
        isTypeOfBit($dataType) ||
        isTypeOfBinary($dataType) ||
        isTypeOfVarBinary($dataType) ||
        isTypeOfTinyText($dataType) ||
        isTypeOfText($dataType) ||
        isTypeOfMediumText($dataType) ||
        isTypeOfLongText($dataType) ||
        isTypeOfTinyBinary($dataType) ||
        isTypeOfBlob($dataType) ||
        isTypeOfMediumBlob($dataType) ||
        isTypeOfLongBlob($dataType) ||
        isTypeOfEnum($dataType) ||
        isTypeOfSet($dataType)
    ) {
        // TODO Implements Unsupported Type Exception
        throw new UnsupportedTypeException('Unsupported type. type = ' . $dataType);
    }

    throw new UnexpectedValueException('Unexpected value. type = ' . $dataType);
}

function isFixedLength($dataType)
{
    if (strtolower($dataType) === Column::DATA_TYPE_CHAR ||
        strtolower($dataType) === Column::DATA_TYPE_DATE ||
        strtolower($dataType) === Column::DATA_TYPE_DATETIME ||
        strtolower($dataType) === Column::DATA_TYPE_TIME ||
        strtolower($dataType) === Column::DATA_TYPE_YEAR
    ) {
        return true;
    }
    return false;
}

function extractMaxLength($schemaType)
{
    if (preg_match('/^.*\((\d+)\).*$/', $schemaType, $matches)) {
        return (int)$matches[1];
    }
    assert(false, sprintf('Logic error. type = %s', $schemaType));
}

function getMaxValue($dataType, $isUnsigned)
{
    $maxValue = Column::MAX_VALUE[$dataType];
    return $isUnsigned ? $maxValue * 2 + 1 : $maxValue;
}

function getMinValue($dataType, $isUnsigned)
{
    if ($isUnsigned) {
        return 0;
    }
    return Column::MIN_VALUE[$dataType];
}

function validateByColumnMetaData($metaData, $insertValue, $columnName)
{
    //$c['field'], $c['type'], $c['null'], $c['default'];
    /** @var RespectValidation $v */
    $v = null;
    // null許容のルール
    if ($metaData['null'] === 'YES' && $insertValue === null) {
        // データがnullならvalidationを掛けると逆に怒られるので早期リターン
        return;
    }
    if ($metaData['null'] === 'NO' && $insertValue === null) {
        throw new ValidationException(sprintf('`%s` column must be not null', $columnName));
    }
    // ここから下はnullじゃないデータがはいってくるはず

    // 型のルール
    $schemaType = $metaData['type'];
    $dataType = extractDataType($schemaType);
    try {
        $type = getRoughlyType($dataType);
    } catch (UnsupportedTypeException $e) {
        // memo この例外の処理は利用者に任せるべきか？
        return;
    }
    $isUnsigned = isUnsigned($schemaType);
    switch ($type) {
        case Column::TYPE_INTEGER:
            $v = extractMaxLength($schemaType) === 1 ? RespectValidation::boolVal() : RespectValidation::intVal();
            break;
        case Column::TYPE_DECIMAL:
            $v = RespectValidation::floatVal();
            break;
        case Column::TYPE_STRING:
            // 文字列の場合は文字列長のルールも規定に入れる
            $v = RespectValidation::stringType()->length(null, extractMaxLength($schemaType), true);
            break;
        case Column::TYPE_DATE:
            $v = RespectValidation::date('Y-m-d');
            break;
        case Column::TYPE_DATETIME:
        case Column::DATA_TYPE_TIMESTAMP:
            $v = RespectValidation::date('Y-m-d H:i:s');
            break;
        case Column::TYPE_TIME:
            $v = RespectValidation::date('H:i:s');
            break;
        case Column::TYPE_YEAR:
            $v = extractMaxLength($schemaType) === 4 ? RespectValidation::date('Y') : RespectValidation::date('y');
            break;
    }
    assert($dataType !== null, 'Unexpected type. type = ' . $dataType);
    // 値域のルール
    $v->between(getMinValue($dataType, $isUnsigned), getMaxValue($dataType, $isUnsigned), true);

    $v->check($insertValue);
}

function isValidByRule($rule, $data)
{
}

// -- TO column
function isTypeOfTinyInt($dataType)
{
    return $dataType === Column::DATA_TYPE_TINYINT;
}

function isTypeOfSmallInt($dataType)
{
    return $dataType === Column::DATA_TYPE_SMALLINT;
}

function isTypeOfMediumInt($dataType)
{
    return $dataType === Column::DATA_TYPE_MEDIUMINT;
}

function isTypeOfInt($dataType)
{
    return $dataType === Column::DATA_TYPE_INT;
}

function isTypeOfBigInt($dataType)
{
    return $dataType === Column::DATA_TYPE_BIGINT;
}

function isTypeOfDecimal($dataType)
{
    return $dataType === Column::DATA_TYPE_DECIMAL;
}

function isTypeOfFloat($dataType)
{
    return $dataType === Column::DATA_TYPE_FLOAT;
}

function isTypeOfDouble($dataType)
{
    return $dataType === Column::DATA_TYPE_DOUBLE;
}

function isTypeOfDate($dataType)
{
    return $dataType === Column::DATA_TYPE_DATE;
}

function isTypeOfDateTime($dataType)
{
    return $dataType === Column::DATA_TYPE_DATETIME;
}

function isTypeOfTime($dataType)
{
    return $dataType === Column::DATA_TYPE_TIME;
}

function isTypeOfTimeStamp($dataType)
{
    return $dataType === Column::DATA_TYPE_TIMESTAMP;
}

function isTypeOfYear($dataType)
{
    return $dataType === Column::DATA_TYPE_YEAR;
}

function isTypeOfSet($dataType)
{
    return $dataType === Column::DATA_TYPE_SET;
}

function isTypeOfEnum($dataType)
{
    return $dataType === Column::DATA_TYPE_ENUM;
}

function isTypeOfLongBlob($dataType)
{
    return $dataType === Column::DATA_TYPE_LONGBLOB;
}

function isTypeOfMediumBlob($dataType)
{
    return $dataType === Column::DATA_TYPE_MEDIUMBLOB;
}

function isTypeOfBlob($dataType)
{
    return $dataType === Column::DATA_TYPE_BLOB;
}

function isTypeOfTinyBinary($dataType)
{
    return $dataType === Column::DATA_TYPE_TINYBLOB;
}

function isTypeOfLongText($dataType)
{
    return $dataType === Column::DATA_TYPE_LONGTEXT;
}

function isTypeOfMediumText($dataType)
{
    return $dataType === Column::DATA_TYPE_MEDIUMTEXT;
}

function isTypeOfText($dataType)
{
    return $dataType === Column::DATA_TYPE_TEXT;
}

function isTypeOfTinyText($dataType)
{
    return $dataType === Column::DATA_TYPE_TINYTEXT;
}

function isTypeOfVarBinary($dataType)
{
    return $dataType === Column::DATA_TYPE_VARBINARY;
}

function isTypeOfBinary($dataType)
{
    return $dataType === Column::DATA_TYPE_BINARY;
}

function isTypeOfBit($dataType)
{
    return $dataType === Column::DATA_TYPE_BIT;
}

function isTypeOfVarChar($dataType)
{
    return $dataType === Column::DATA_TYPE_VARCHAR;
}

function isTypeOfChar($dataType)
{
    return $dataType === Column::DATA_TYPE_CHAR;
}
