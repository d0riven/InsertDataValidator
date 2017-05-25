<?php

namespace InsertDataValidator\MySql;


use InsertDataValidator\FactoryInterface;

class Factory implements FactoryInterface
{
    const FIELD_COLUMN_NAME = 'field';
    const TYPE_COLUMN_NAME = 'type';
    const NULL_ALLOWABLE_COLUMN_NAME = 'null';

    public function createColumnMetaData(array $metaData)
    {
        $metaData = array_change_key_case($metaData, CASE_LOWER);
        return new ColumnMetaData(
            $metaData[self::FIELD_COLUMN_NAME],
            $metaData[self::TYPE_COLUMN_NAME],
            $metaData[self::NULL_ALLOWABLE_COLUMN_NAME]
        );
    }

    public function createMetaDataFetcher()
    {
        return new MetaDataFetcher();
    }
}