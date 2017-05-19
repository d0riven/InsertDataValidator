<?php


use InsertDataValidator\MySql\ColumnMetaData;

class MySQL_ColumnMetaDataTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @test
     * @dataProvider extractDataTypeWithSchemaTypes
     */
    public function extractDataType_do_extract_data_type_from_schema_type_format($schemaType, $expected)
    {
        $sut = new ColumnMetaData('', $schemaType, '');
        $this->tester->assertEquals($expected, $sut->extractDataType());
    }

    public function extractDataTypeWithSchemaTypes()
    {
        return [
            'type with size' => [
                'schemaType' => 'tinyint(4)',
                'expected' => 'tinyint',
            ],
            'type with size and unsigned' => [
                'schemaType' => 'tinyint(3) unsigned',
                'expected' => 'tinyint',
            ],
            'only type' => [
                'schemaType' => 'float',
                'expected' => 'float',
            ],
            'type with unsigned' => [
                'schemaType' => 'float unsigned',
                'expected' => 'float',
            ],
            'type with enum' => [
                'schemaType' => "enum('one','two','three')",
                'expected' => 'enum',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider extractSizeWithSchemaTypes
     */
    public function extractSize_do_extract_data_size_from_schema_type_format($schemaType, $expected)
    {
        $sut = new ColumnMetaData('', $schemaType, '');
        $this->tester->assertEquals($expected, $sut->extractSize());
    }

    public function extractSizeWithSchemaTypes()
    {
        return [
            'type with size' => [
                'schemaType' => 'tinyint(4)',
                'expected' => 4,
            ],
            'type with size and unsigned' => [
                'schemaType' => 'tinyint(3) unsigned',
                'expected' => 3,
            ],
        ];
    }

    /**
     * @test
     */
    public function isUnsigned_return_true_if_schema_type_is_contained_unsigned()
    {
        $sut = new ColumnMetaData('', 'tinyint(3) unsigned', '');
        $this->tester->assertTrue($sut->isUnsigned());
    }

    /**
     * @test
     */
    public function isUnsigned_return_false_if_schema_type_is_not_contained_unsigned()
    {
        $sut = new ColumnMetaData('', 'tinyint(3)', '');
        $this->tester->assertFalse($sut->isUnsigned());
    }

    /**
     * @test
     * @dataProvider getRoughlyTypeWithDataType
     */
    public function getRoughlyType_return_roughly_type_of_data_type($dataType, $expected)
    {
        $sut = new ColumnMetaData('', '', '');
        $this->tester->assertEquals($expected, $sut->getRoughlyType($dataType));
    }

    public function getRoughlyTypeWithDataType()
    {
        return [
            ColumnMetaData::DATA_TYPE_TINYINT => [
                'dataType' => ColumnMetaData::DATA_TYPE_TINYINT,
                'expected' => ColumnMetaData::TYPE_INTEGER,
            ],
            ColumnMetaData::DATA_TYPE_SMALLINT => [
                'dataType' => ColumnMetaData::DATA_TYPE_SMALLINT,
                'expected' => ColumnMetaData::TYPE_INTEGER,
            ],
            ColumnMetaData::DATA_TYPE_MEDIUMINT => [
                'dataType' => ColumnMetaData::DATA_TYPE_MEDIUMINT,
                'expected' => ColumnMetaData::TYPE_INTEGER,
            ],
            ColumnMetaData::DATA_TYPE_INT => [
                'dataType' => ColumnMetaData::DATA_TYPE_INT,
                'expected' => ColumnMetaData::TYPE_INTEGER,
            ],
            ColumnMetaData::DATA_TYPE_BIGINT => [
                'dataType' => ColumnMetaData::DATA_TYPE_BIGINT,
                'expected' => ColumnMetaData::TYPE_INTEGER,
            ],
            ColumnMetaData::DATA_TYPE_FLOAT => [
                'dataType' => ColumnMetaData::DATA_TYPE_FLOAT,
                'expected' => ColumnMetaData::TYPE_DECIMAL,
            ],
            ColumnMetaData::DATA_TYPE_DOUBLE => [
                'dataType' => ColumnMetaData::DATA_TYPE_DOUBLE,
                'expected' => ColumnMetaData::TYPE_DECIMAL,
            ],
            ColumnMetaData::DATA_TYPE_DECIMAL => [
                'dataType' => ColumnMetaData::DATA_TYPE_DECIMAL,
                'expected' => ColumnMetaData::TYPE_DECIMAL,
            ],
            ColumnMetaData::DATA_TYPE_CHAR => [
                'dataType' => ColumnMetaData::DATA_TYPE_CHAR,
                'expected' => ColumnMetaData::TYPE_STRING,
            ],
            ColumnMetaData::DATA_TYPE_VARCHAR => [
                'dataType' => ColumnMetaData::DATA_TYPE_VARCHAR,
                'expected' => ColumnMetaData::TYPE_STRING,
            ],
            ColumnMetaData::DATA_TYPE_DATE => [
                'dataType' => ColumnMetaData::DATA_TYPE_DATE,
                'expected' => ColumnMetaData::TYPE_DATE,
            ],
            ColumnMetaData::DATA_TYPE_DATETIME => [
                'dataType' => ColumnMetaData::DATA_TYPE_DATETIME,
                'expected' => ColumnMetaData::TYPE_DATETIME,
            ],
            ColumnMetaData::DATA_TYPE_YEAR => [
                'dataType' => ColumnMetaData::DATA_TYPE_YEAR,
                'expected' => ColumnMetaData::TYPE_YEAR,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getRoughlyTypeWithUnsupportedDataType
     * @expectedException \InsertDataValidator\Exception\UnsupportedException
     */
    public function getRoughlyType_throw_exception_unsupported_data_type($dataType)
    {
        $sut = new ColumnMetaData('', '', '');
        $sut->getRoughlyType($dataType);
    }

    public function getRoughlyTypeWithUnsupportedDataType()
    {
        return [
            ColumnMetaData::DATA_TYPE_TIME => [
                'dataType' => ColumnMetaData::DATA_TYPE_TIME,
            ],
            ColumnMetaData::DATA_TYPE_BIT => [
                'dataType' => ColumnMetaData::DATA_TYPE_BIT,
            ],
            ColumnMetaData::DATA_TYPE_BINARY => [
                'dataType' => ColumnMetaData::DATA_TYPE_BINARY,
            ],
            ColumnMetaData::DATA_TYPE_VARBINARY => [
                'dataType' => ColumnMetaData::DATA_TYPE_VARBINARY,
            ],
            ColumnMetaData::DATA_TYPE_TINYTEXT => [
                'dataType' => ColumnMetaData::DATA_TYPE_TINYTEXT,
            ],
            ColumnMetaData::DATA_TYPE_TEXT => [
                'dataType' => ColumnMetaData::DATA_TYPE_TEXT,
            ],
            ColumnMetaData::DATA_TYPE_MEDIUMTEXT => [
                'dataType' => ColumnMetaData::DATA_TYPE_MEDIUMTEXT,
            ],
            ColumnMetaData::DATA_TYPE_LONGTEXT => [
                'dataType' => ColumnMetaData::DATA_TYPE_LONGTEXT,
            ],
            ColumnMetaData::DATA_TYPE_TINYBLOB => [
                'dataType' => ColumnMetaData::DATA_TYPE_TINYBLOB,
            ],
            ColumnMetaData::DATA_TYPE_BLOB => [
                'dataType' => ColumnMetaData::DATA_TYPE_BLOB,
            ],
            ColumnMetaData::DATA_TYPE_MEDIUMBLOB => [
                'dataType' => ColumnMetaData::DATA_TYPE_MEDIUMBLOB,
            ],
            ColumnMetaData::DATA_TYPE_LONGBLOB => [
                'dataType' => ColumnMetaData::DATA_TYPE_LONGBLOB,
            ],
            ColumnMetaData::DATA_TYPE_ENUM => [
                'dataType' => ColumnMetaData::DATA_TYPE_ENUM,
            ],
            ColumnMetaData::DATA_TYPE_SET => [
                'dataType' => ColumnMetaData::DATA_TYPE_SET,
            ],
        ];
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}