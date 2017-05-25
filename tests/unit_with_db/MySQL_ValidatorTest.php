<?php


use InsertDataValidator\MySql\ColumnMetaData;
use InsertDataValidator\Validator;

class MySQL_ValidatorTest extends \Codeception\Test\Unit
{
    CONST TABLE_NAME_OF_RANGE  = 'test_type_range';
    CONST TABLE_NAME_OF_LENGTH = 'test_type_length';
    CONST TABLE_NAME_OF_NULL = 'test_null';

    /**
     * @var \UnitWithDbTester
     */
    protected $tester;
    /**
     * @var Validator
     */
    private $sut;
    /**
     * @var PDO
     */
    private $dbh;

    /**
     * @test
     * @expectedException
     * @dataProvider validate_does_not_throw_an_exception_values
     */
    public function validate_does_not_throw_an_exception_if_all_values_are_acceptable_range(array $value)
    {
        $this->sut->validate($this->dbh, self::TABLE_NAME_OF_RANGE, $value);
    }

    public function validate_does_not_throw_an_exception_values()
    {
        return array_merge(
            $this->lowerBoundsValue(), $this->lowerBoundsValueWithUnsigned(),
            $this->upperBoundsValue(), $this->upperBoundsValueWithUnsigned()
        );
    }

    public function lowerBoundsValue()
    {
        return [
            'lower bounds tinyint'   => [
                ['d_tinyint' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_TINYINT]]
            ],
            'lower bounds smallint'  => [
                ['d_smallint' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_SMALLINT]]
            ],
            'lower bounds_mediumint' => [
                ['d_mediumint' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_MEDIUMINT]]
            ],
            'lower bounds int'       => [
                ['d_int' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_INT]]
            ],
            'lower bounds bigint'    => [
                ['d_bigint' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_BIGINT]]
            ],
//            'lower bounds decimal' => [
//                ['d_decimal' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_SMALLINT]]
//            ],
            'lower bounds float'     => [
                ['d_float' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_FLOAT]]
            ],
            'lower bounds double'    => [
                ['d_double' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_DOUBLE]]
            ],
            'lower bounds date'      => [
                ['d_date' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_DATE]]
            ],
            'lower bounds datetime'  => [
                ['d_datetime' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_DATETIME]]
            ],
//            'lower bounds time' => [
//                ['d_time' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_TIME]]
//            ],
            'lower bounds timestamp' => [
                ['d_timestamp' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_TIMESTAMP]]
            ],
//            'lower bounds year(2)' => [
//                ['d_datetime' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_YEAR]]
//            ],
//            'lower bounds year(4)' => [
//                ['d_datetime' => ColumnMetaData::MIN_VALUE[ColumnMetaData::DATA_TYPE_YEAR]]
//            ],
        ];
    }

    public function lowerBoundsValueWithUnsigned()
    {
        return [
            'lower bounds unsigned tinyint'   => [
                ['d_utinyint' => 0],
            ],
            'lower bounds unsigned smallint'  => [
                ['d_usmallint' => 0],
            ],
            'lower bounds unsigned mediumint' => [
                ['d_umediumint' => 0],
            ],
            'lower bounds unsigned int'       => [
                ['d_uint' => 0],
            ],
            'lower bounds unsigned bigint'    => [
                ['d_ubigint' => 0],
            ],
//            'lower bounds unsigned decimal' => [
//                ['d_udecimal' => 0.0],
//            ],
            'lower bounds unsigned float'     => [
                ['d_ufloat' => 0.0],
            ],
            'lower bounds unsigned double'    => [
                ['d_udouble' => 0.0],
            ],
        ];
    }

    public function upperBoundsValue()
    {
        return [
            'upper bounds tinyint'   => [
                ['d_tinyint' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_TINYINT]]
            ],
            'upper bounds smallint'  => [
                ['d_smallint' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_SMALLINT]]
            ],
            'upper bounds_mediumint' => [
                ['d_mediumint' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_MEDIUMINT]]
            ],
            'upper bounds int'       => [
                ['d_int' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_INT]]
            ],
            'upper bounds bigint'    => [
                ['d_bigint' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_BIGINT]]
            ],
//            'upper bounds decimal' => [
//                ['d_decimal' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_SMALLINT]]
//            ],
            'upper bounds float'     => [
                ['d_float' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_FLOAT]]
            ],
            'upper bounds double'    => [
                ['d_double' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_DOUBLE]]
            ],
            'upper bounds date'      => [
                ['d_date' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_DATE]]
            ],
            'upper bounds datetime'  => [
                ['d_datetime' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_DATETIME]]
            ],
            'upper bounds time'      => [
                ['d_time' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_TIME]]
            ],
            'upper bounds timestamp' => [
                ['d_timestamp' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_TIMESTAMP]]
            ],
//            'upper bounds year(2)' => [
//                ['d_datetime' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_YEAR]]
//            ],
//            'upper bounds year(4)' => [
//                ['d_datetime' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_YEAR]]
//            ],
        ];
    }

    public function upperBoundsValueWithUnsigned()
    {
        return [
            'upper bounds unsigned tinyint'   => [
                ['d_utinyint' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_TINYINT] * 2 + 1]
            ],
            'upper bounds unsigned smallint'  => [
                ['d_usmallint' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_SMALLINT] * 2 + 1]
            ],
            'upper bounds unsigned_mediumint' => [
                ['d_umediumint' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_MEDIUMINT] * 2 + 1]
            ],
            'upper bounds unsigned int'       => [
                ['d_uint' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_INT] * 2 + 1]
            ],
            // TODO Fix. PHP not supported unsigned 64bit integer.
            'upper bounds unsigned bigint'    => [
                ['d_ubigint' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_BIGINT]]
            ],
//            'upper bounds unsigned decimal' => [
//                ['d_udecimal' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_SMALLINT]]
//            ],
            'upper bounds unsigned float'     => [
                ['d_ufloat' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_FLOAT]]
            ],
            'upper bounds unsigned double'    => [
                ['d_udouble' => ColumnMetaData::MAX_VALUE[ColumnMetaData::DATA_TYPE_DOUBLE]]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validate_does_not_throw_an_exception_if_all_values_are_acceptable_length_value
     */
    public function validate_does_not_throw_an_exception_if_all_values_are_acceptable_length($value)
    {
        $this->sut->validate($this->dbh, self::TABLE_NAME_OF_LENGTH, $value);
        $this->assertTrue(true);
    }

    public function validate_does_not_throw_an_exception_if_all_values_are_acceptable_length_value()
    {
        return [
            'char'  => [
                'value' => [
                    'd_char' => str_repeat('a', 32),
                ],
            ],
            'varchar' => [
                'value' => [
                    'd_varchar' => str_repeat('a', 32),
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validate_throw_an_exception_if_over_the_length_value
     * @expectedException \InsertDataValidator\Exception\ValidationException
     */
    public function validate_throw_an_exception_if_over_the_length($value)
    {
        $this->sut->validate($this->dbh, self::TABLE_NAME_OF_LENGTH, $value);
    }

    public function validate_throw_an_exception_if_over_the_length_value()
    {
        return [
            'char'  => [
                'value' => [
                    'd_char' => str_repeat('a', 33),
                ],
            ],
            'varchar' => [
                'value' => [
                    'd_varchar' => str_repeat('a', 33),
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function validate_does_not_throw_an_exception_if_data_has_null_but_nullable_type()
    {
        $this->sut->validate($this->dbh, self::TABLE_NAME_OF_NULL, [
            'd_null_allowable' => null,
            'd_null_block' => 1,
        ]);
        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException \InsertDataValidator\Exception\ValidationException
     */
    public function validate_throw_an_exception_if_data_has_null_but_nullable_type()
    {
        $this->sut->validate($this->dbh, self::TABLE_NAME_OF_LENGTH, [
            'd_null_allowable' => 1,
            'd_null_block' => null,
        ]);
    }

    /**
     * @test
     * @expectedException \InsertDataValidator\Exception\ValidationException
     */
    public function validate_throw_an_exception_if_one_of_value_is_violation()
    {
        $this->sut->validate($this->dbh, self::TABLE_NAME_OF_RANGE,
            ['d_tinyint' => 'string']
        );
    }

    /**
     * @test
     * @expectedException \InsertDataValidator\Exception\ValidationException
     */
    public function validate_throw_an_exception_if_insert_data_has_not_exist_column_data()
    {
        $this->sut->validate($this->dbh, self::TABLE_NAME_OF_RANGE,
            ['ghost' => '']
        );
    }

    /**
     * @test
     */
    public function validate_ignore_validation_unsupported_type()
    {
        // this data has violation of data type, but ignore
        $this->sut->validate($this->dbh, self::TABLE_NAME_OF_RANGE,
            ['d_text' => 'hello']
        );
        $this->assertTrue(true);
    }

    protected function _before()
    {
        $this->dbh = $this->getModule('Db')->dbh;
        $this->sut = new Validator();
    }

    protected function _after()
    {
    }
}