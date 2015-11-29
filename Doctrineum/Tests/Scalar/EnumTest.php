<?php
namespace Doctrineum\Tests\Scalar;

use Doctrineum\Scalar\Enum;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_create_instance()
    {
        $enumClass = $this->getEnumClass();
        $instance = $enumClass::getEnum('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf($enumClass, $instance);
    }

    /**
     * @return \Doctrineum\Scalar\Enum
     */
    protected function getEnumClass()
    {
        return Enum::getClass();
    }

    /**
     * @test
     */
    public function same_instance_for_same_name_is_returned()
    {
        $enumClass = $this->getEnumClass();
        $firstInstance = $enumClass::getEnum($firstValue = 'foo');
        $secondInstance = $enumClass::getEnum($secondValue = 'bar');
        $thirdInstance = $enumClass::getEnum($firstValue);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertNotSame(
            $firstInstance,
            $secondInstance,
            "Instance of enum $enumClass with value $firstValue should not be same as instance with value $secondValue"
        );
        $this->assertSame($firstInstance, $thirdInstance);
    }

    /** @test */
    public function returns_same_value_as_created_with()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('foo', $enum->getValue());
    }

    /** @test */
    public function as_string_is_of_same_value_as_created_with()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('foo', (string)$enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\CanNotBeCloned
     */
    public function can_not_be_cloned()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum('foo');
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone $enum;
    }

    /** @test */
    public function with_to_string_object_is_of_same_value_as_object()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum(new WithToStringTestObject('foo'));
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('foo', $enum->getValue());
        $this->assertSame('foo', (string)$enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function object_without_to_string_cause_exception()
    {
        $enumClass = $this->getEnumClass();
        $enumClass::getEnum(new \stdClass());
    }

    /**
     * inner namespace test
     */

    /** @test */
    public function inherited_enum_with_same_value_lives_in_own_inner_namespace()
    {
        $enumClass = $this->getEnumClass();

        $enum = $enumClass::getEnum($value = 'foo');
        $this->assertInstanceOf($enumClass, $enum);
        $this->assertSame($value, $enum->getValue());
        $this->assertSame($value, (string)$enum);

        $inDifferentNamespace = $this->getInheritedEnum($value);
        $this->assertInstanceOf($enumClass, $inDifferentNamespace);
        $this->assertSame($enum->getValue(), $inDifferentNamespace->getValue());
        $this->assertNotSame($enum, $inDifferentNamespace);
    }

    protected function getInheritedEnum($value)
    {
        return new TestInheritedEnum($value);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\EnumIsAlreadyBuilt
     */
    public function adding_an_existing_enum_cause_exception()
    {
        TestInvalidExistingEnumUsage::forceGetting(false);
        TestInvalidExistingEnumUsage::forceAdding(true);
        // getting twice to internally add twice
        TestInvalidExistingEnumUsage::getEnum('foo');
        TestInvalidExistingEnumUsage::getEnum('foo');
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\EnumIsNotBuilt
     */
    public function getting_an_non_existing_enum_cause_exception()
    {
        TestInvalidExistingEnumUsage::forceAdding(false);
        TestInvalidExistingEnumUsage::forceGetting(true);
        TestInvalidExistingEnumUsage::getEnum('bar');
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function using_invalid_value_without_casting_cause_exception()
    {
        TestInvalidEnumValueTest::getEnum(new \stdClass());
    }
}

/** inner */
class TestInvalidExistingEnumUsage extends Enum
{
    private static $forceAdding = false;
    private static $forceGetting = false;

    public static function forceAdding($force = true)
    {
        self::$forceAdding = $force;
    }

    public static function forceGetting($force = true)
    {
        self::$forceGetting = $force;
    }

    protected static function getEnumFromNamespace($enumValue, $namespace)
    {
        $finalValue = static::convertToEnumFinalValue($enumValue);
        if (self::$forceAdding) {
            static::addBuiltEnum(static::createByValue($finalValue), $namespace);
        }

        if (self::$forceGetting) {
            return static::getBuiltEnum($finalValue, $namespace);
        }

        return null;
    }

    protected function getInheritedEnum($value)
    {
        return new TestInheritedEnum($value);
    }
}

/** inner */
class TestInheritedEnum extends Enum
{

}

class TestInvalidEnumValueTest extends Enum
{

    protected static function convertToEnumFinalValue($value)
    {
        // intentionally no conversion at all
        return $value;
    }
}
