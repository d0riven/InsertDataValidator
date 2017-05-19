<?php

namespace InsertDataValidator\MySql;


use InsertDataValidator\FactoryInterface;

class Factory implements FactoryInterface
{
    const FIELD_COLUMN_NAME = 'Field';
    const TYPE_COLUMN_NAME = 'Type';
    const NULL_ALLOWABLE_COLUMN_NAME = 'Null';

    public function createColumnMetaData(array $metaData)
    {
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