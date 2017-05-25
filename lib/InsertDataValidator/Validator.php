<?php

namespace InsertDataValidator;

use InsertDataValidator\Exception\UnsupportedTypeException;
use InsertDataValidator\Exception\ValidationException;
use InsertDataValidator\MySql\ColumnMetaData;
use Respect\Validation\Validator as RespectValidation;

class Validator
{
    /**
     * @param \PDO $pdo
     * @param $tableName
     * @param array $insertData
     * @param array $ignoreValidationColumns
     * @throws ValidationException
     */
    public function validate(\PDO $pdo, $tableName, array $insertData, array $ignoreValidationColumns = [])
    {
        if ($insertData === []) {
            throw new ValidationException('Insert data is empty');
        }

        $errors = [];
        $factory = FactoryGenerator::generateFactoryByPdo($pdo);

        $columnMetaDataSet = array_map(function ($metaData) use ($factory) {
            return $factory->createColumnMetaData($metaData);
        }, $factory->createMetaDataFetcher()->fetchTableDefinition($pdo, $tableName));

        // Format validation
        /** @var $c ColumnMetaData */
        foreach ($columnMetaDataSet as $c) {
            $columnName = $c->getColumnName();
            if (array_key_exists($columnName, $insertData) === false ||
                in_array($columnName, $ignoreValidationColumns, true)
            ) {
                continue;
            }
            try {
                $this->validateByColumnMetaData($c, $insertData[$columnName]);
            } catch (UnsupportedTypeException $e) {
                // Ignore validation of this column if is unsupported type exception.
                continue;
            } catch (\Respect\Validation\Exceptions\ValidationException $e) {
                $errors[] = sprintf('`%s` is not valid. reason = [%s]', $columnName, $e->getMainMessage());
            }
        }

        // ghost column validation
        $tableColumns = array_map(function (ColumnMetaDataInterface $c) {
            return $c->getColumnName();
        }, $columnMetaDataSet);
        $ghostColumns = array_diff(array_keys($insertData), $tableColumns);
        if (count($ghostColumns) > 0) {
            $errors[] = sprintf('Insert data has ghost column. ghosts = [`%s`]', implode('`,`', $ghostColumns));
        }

        if ($errors !== []) {
            throw new ValidationException(
                sprintf('Exists violation of table schema. table_name = %s, errors = %s',
                    $tableName, print_r($errors, true))
            );
        }
    }

    private function validateByColumnMetaData(ColumnMetaDataInterface $metaData, $insertValue)
    {
        if ($metaData->isAllowableNull() && $insertValue === null) {
            // validation does not enable if data is null, so cannot validate value of null.
            return;
        }
        if ($metaData->isAllowableNull() === false && $insertValue === null) {
            throw new ValidationException(sprintf('`%s` column must be not null', $metaData->getColumnName()));
        }

        $dataType = $metaData->extractDataType();

        $v = $this->buildValidationByDataType($dataType, $metaData);

        $v->check($insertValue);
    }

    private function buildValidationByDataType($dataType, ColumnMetaDataInterface $metaData)
    {
        /** @var RespectValidation $v */
        $type = $metaData->getRoughlyType($dataType);
        switch ($type) {
            case ColumnMetaDataInterface::TYPE_INTEGER:
                $v = $metaData->extractSize() === 1 ? RespectValidation::boolVal() : RespectValidation::intVal();
                break;
            case ColumnMetaDataInterface::TYPE_DECIMAL:
                $v = RespectValidation::floatVal();
                break;
            case ColumnMetaDataInterface::TYPE_STRING:
                // 文字列の場合は文字列長のルールも規定に入れる
                $v = RespectValidation::stringType()->length(null, $metaData->extractSize(), true);
                break;
            case ColumnMetaDataInterface::TYPE_DATE:
                $v = RespectValidation::date('Y-m-d');
                break;
            case ColumnMetaDataInterface::TYPE_DATETIME:
                $v = RespectValidation::date('Y-m-d H:i:s');
                break;
//            case ColumnMetaDataInterface::TYPE_TIME:
//                $v = RespectValidation::date('B:i:s');
//                break;
            case ColumnMetaDataInterface::TYPE_YEAR:
                $v = $metaData->extractSize() === 4 ? RespectValidation::date('Y') : RespectValidation::date('y');
                break;
        }
        if ($type !== ColumnMetaDataInterface::TYPE_STRING) {
            $v->between($metaData->getMinValue($dataType), $metaData->getMaxValue($dataType), true);
        }
        return $v;
    }
}