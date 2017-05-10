<?php
/**
 * Created by PhpStorm.
 * User: doriven
 * Date: 2017/05/08
 * Time: 23:46
 */

namespace InsertDataValidator;


interface ColumnMetaDataInterface
{
    const TYPE_INTEGER   = 0;
    const TYPE_DECIMAL   = 1;
    const TYPE_STRING    = 2;
    const TYPE_DATE      = 3;
    const TYPE_DATETIME  = 4;
    const TYPE_TIME      = 5;
    const TYPE_YEAR      = 6;

    public function getRoughlyType($dataType);
    public function getMaxValue($dataType);
    public function getMinValue($dataType);
    public function extractDataType();
    public function extractMaxLength();
    public function getColumnName();
    public function isAllowableNull();
    public function isUnsigned();

}