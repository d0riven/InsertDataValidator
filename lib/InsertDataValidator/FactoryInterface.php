<?php

namespace InsertDataValidator;


interface FactoryInterface
{
    public function createColumnMetaData(array $metaData);
    public function createMetaDataFetcher();
}