<?php

namespace InsertDataValidator\MySql;


use InsertDataValidator\FactoryInterface;

class Factory implements FactoryInterface
{
    public function createColumnMetaData(array $metaData)
    {
        return new ColumnMetaData($metaData['field'], $metaData['type'], $metaData['null']);
    }

    public function createMetaDataFetcher()
    {
        return new MetaDataFetcher();
    }
}