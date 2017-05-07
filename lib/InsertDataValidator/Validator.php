<?php

namespace InsertDataValidator;

use Exception;
use InsertDataValidator\Exception\UnsupportedTypeException;
use PDO;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as RespectValidation;

class Validator
{
    public function validate(PDO $pdo, $tableName, array $insertData, array $ignoreValidationColumns = [])
    {
        $errors = [];
        $schema = $this->fetchTableSchema($pdo, $tableName);
        // Format validation
        foreach ($schema as $c) {
            $columnName = $c['field'];
            if (array_key_exists($columnName, $insertData) === false ||
                in_array($columnName, $ignoreValidationColumns, true)
            ) {
                continue;
            }
            try {
                $this->validateByColumnMetaData($c, $insertData[$columnName], $columnName);
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
            // TODO implements original Exception
            throw new Exception(
                sprintf('Exists violation of table schema. table_name = %s, errors = %s',
                    $tableName, print_r($errors, true))
            );
        }
    }

    public function fetchTableSchema(PDO $pdo, $tableName)
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM $tableName");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $column = new Column();
        $dataType = $column->extractDataType($schemaType);
        try {
            $type = $column->getRoughlyType($dataType);
        } catch (UnsupportedTypeException $e) {
            // memo この例外の処理は利用者に任せるべきか？
            return;
        }
        $isUnsigned = $column->isUnsigned($schemaType);
        switch ($type) {
            case Column::TYPE_INTEGER:
                $v = $column->extractMaxLength($schemaType) === 1 ? RespectValidation::boolVal() : RespectValidation::intVal();
                break;
            case Column::TYPE_DECIMAL:
                $v = RespectValidation::floatVal();
                break;
            case Column::TYPE_STRING:
                // 文字列の場合は文字列長のルールも規定に入れる
                $v = RespectValidation::stringType()->length(null, $column->extractMaxLength($schemaType), true);
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
                $v = $column->extractMaxLength($schemaType) === 4 ? RespectValidation::date('Y') : RespectValidation::date('y');
                break;
        }
        assert($dataType !== null, 'Unexpected type. type = ' . $dataType);
        // 値域のルール
        $v->between($column->getMinValue($dataType, $isUnsigned), $column->getMaxValue($dataType, $isUnsigned), true);

        $v->check($insertValue);
    }
}