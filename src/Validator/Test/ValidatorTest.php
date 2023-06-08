<?php

declare(strict_types=1);

namespace Kaa\Validator\Test;

use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Strategy\IsFalseGenerator;
use Kaa\Validator\Strategy\EmailGenerator;
use Kaa\Validator\Strategy\IsTrueGenerator;
use Kaa\Validator\Strategy\LessThanGenerator;
use Kaa\Validator\Strategy\LessThanOrEqualGenerator;
use Kaa\Validator\Strategy\NegativeGenerator;
use Kaa\Validator\Strategy\NegativeOrZeroGenerator;
use Kaa\Validator\Strategy\NotBlankGenerator;
use Kaa\Validator\Strategy\NotNullGenerator;
use Kaa\Validator\Strategy\PositiveGenerator;
use Kaa\Validator\Strategy\PositiveOrZeroGenerator;
use Kaa\Validator\Strategy\RangeGenerator;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use Kaa\Validator\Strategy\GreaterThanOrEqualGenerator;
use Kaa\Validator\Strategy\GreaterThanGenerator;
use Kaa\Validator\Strategy\BlankGenerator;

class ValidatorTest extends TestCase
{
    private const VIOLATION_LIST = 'violationList';
    private const NAME_TEST_MODEL = 'testModel';
    private const TYPE_TEST_MODEL = 'Kaa\Validator\Test\TestModel';

    private function createVarToValidate(): AvailableVar
    {
        return new AvailableVar(
            self::NAME_TEST_MODEL,
            self::TYPE_TEST_MODEL,
        );
    }

    private function initializingParameters(string $generator): array
    {
        return [
            $this->createVarToValidate(),
            new TestModel(),
            new $generator(),
            new ReflectionClass(self::TYPE_TEST_MODEL),
        ];
    }

    private function getAttributeAndProperty(
        \ReflectionClass $reflectionClass,
        AvailableVar $varToValidate,
        string $propertyName,
    ): ?array
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->name === $propertyName) {
                $assertAttributes = $reflectionProperty->getAttributes(
                    Assert::class,
                    ReflectionAttribute::IS_INSTANCEOF,
                );
                $testAttributeInstance = $assertAttributes[0]->newInstance();
                $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $varToValidate->name);
                return [$reflectionProperty, $testAttributeInstance, $accessCode];
            }
        }
        return null;
    }

    private function getViolationList(
        string $generator,
        string $propertyName,
        &$testAttributeInstance = null,
    ): array
    {
        $violationList = [];
        [$varToValidate, $testModel, $assertGenerator, $reflectionClass] = $this->initializingParameters(
            $generator
        );

        [$reflectionProperty, $testAttributeInstance, $accessCode] = $this->getAttributeAndProperty(
            $reflectionClass,
            $varToValidate,
            $propertyName,
        );
        $generatedCode[] = $assertGenerator->generateAssert(
            $testAttributeInstance,
            $reflectionProperty,
            $varToValidate,
            self::VIOLATION_LIST,
            $accessCode,
        );
        eval (array_merge(...$generatedCode)[0]);
        return $violationList;
    }

    /**
     * @throws \ReflectionException
     * @throws InaccessiblePropertyException
     */
    public function testGreaterThanFalse(): void
    {
        $violationList = $this->getViolationList(
            GreaterThanGenerator::class,
            "GreaterThanFalse",
            $testAttributeInstance,
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should be greater than $testAttributeInstance->value.", $violationList[0]->getMessage());
    }

    public function testGreaterThanTrue(): void
    {

        $violationList = $this->getViolationList(
            GreaterThanGenerator::class,
            "GreaterThanTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testGreaterThanEqualFalse(): void
    {
        $violationList = $this->getViolationList(
            GreaterThanGenerator::class,
            "GreaterThanEqualFalse",
            $testAttributeInstance,
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should be greater than $testAttributeInstance->value.", $violationList[0]->getMessage());
    }

    public function testBlankFalse(): void
    {
        $violationList = $this->getViolationList(
            BlankGenerator::class,
            "BlankFalse",
            $testAttributeInstance,
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should be blank.", $violationList[0]->getMessage());
    }

    public function testBlankTrue(): void
    {
        $violationList = $this->getViolationList(
            BlankGenerator::class,
            "BlankTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testGreaterThanOrEqualFalse(): void
    {
        $violationList = $this->getViolationList(
            GreaterThanOrEqualGenerator::class,
            "GreaterThanOrEqualFalse",
            $testAttributeInstance,
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should be greater than or equal to $testAttributeInstance->value.", $violationList[0]->getMessage());
    }

    public function testGreaterThanOrEqualTrue(): void
    {
        $violationList = $this->getViolationList(
            GreaterThanOrEqualGenerator::class,
            "GreaterThanOrEqualTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testIsFalse(): void
    {
        $violationList = $this->getViolationList(
            IsFalseGenerator::class,
            "IsFalse",
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals('This value should be false.', $violationList[0]->getMessage());
    }

    public function testIsTrue(): void
    {
        $violationList = $this->getViolationList(
            IsTrueGenerator::class,
            "IsTrue",
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals('This value should be true.', $violationList[0]->getMessage());
    }

    public function testLessThanFalse(): void
    {
        $violationList = $this->getViolationList(
            LessThanGenerator::class,
            "LessThanFalse",
            $testAttributeInstance,
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should be less than $testAttributeInstance->value.", $violationList[0]->getMessage());
    }

    public function testLessThanTrue(): void
    {
        $violationList = $this->getViolationList(
            LessThanGenerator::class,
            "LessThanTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testLessThanEqualFalse(): void
    {
        $violationList = $this->getViolationList(
            LessThanGenerator::class,
            "LessThanEqualFalse",
            $testAttributeInstance,
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should be less than $testAttributeInstance->value.", $violationList[0]->getMessage());
    }

    public function testLessThanOrEqualFalse(): void
    {
        $violationList = $this->getViolationList(
          LessThanOrEqualGenerator::class,
          "LessThanOrEqualFalse",
          $testAttributeInstance,
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should be less than or equal to $testAttributeInstance->value.", $violationList[0]->getMessage());
    }

    public function testLessThanOrEqualTrue(): void
    {
        $violationList = $this->getViolationList(
          LessThanOrEqualGenerator::class,
          "LessThanOrEqualTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testEmailTrue(): void
    {
        $violationList = $this->getViolationList(
          EmailGenerator::class,
          "EmailTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testEmailFalse(): void
    {
        $violationList = $this->getViolationList(
            EmailGenerator::class,
            "EmailFalse",
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value is not a valid email address.", $violationList[0]->getMessage());
    }

    public function testNegativeTrue(): void
    {
        $violationList = $this->getViolationList(
            NegativeGenerator::class,
            "NegativeTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testNegativeFalse(): void
    {
        $violationList = $this->getViolationList(
            NegativeGenerator::class,
            "NegativeFalse",
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should be negative.", $violationList[0]->getMessage());
    }

    public function testNegativeOrZeroTrue(): void
    {
        $violationList = $this->getViolationList(
            NegativeOrZeroGenerator::class,
            "NegativeOrZeroTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testNotBlankTrue(): void
    {
        $violationList = $this->getViolationList(
            NotBlankGenerator::class,
            "NotBlankTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testNotBlankFalse(): void
    {
        $violationList = $this->getViolationList(
            NotBlankGenerator::class,
            "NotBlankFalse",
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should not be blank.", $violationList[0]->getMessage());
    }

    public function testNotNullTrue(): void
    {
        $violationList = $this->getViolationList(
            NotNullGenerator::class,
            "NotNullTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testNotNullFalse(): void
    {
        $violationList = $this->getViolationList(
            NotNullGenerator::class,
            "NotNullFalse",
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should not be null.", $violationList[0]->getMessage());
    }

    public function testPositiveTrue(): void
    {
        $violationList = $this->getViolationList(
            PositiveGenerator::class,
            "PositiveTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testPositiveFalse(): void
    {
        $violationList = $this->getViolationList(
            PositiveGenerator::class,
            "PositiveFalse",
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals("This value should be positive.", $violationList[0]->getMessage());
    }

    public function testPositiveOrZeroTrue(): void
    {
        $violationList = $this->getViolationList(
            PositiveOrZeroGenerator::class,
            "PositiveOrZeroTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testRangeTrue(): void
    {
        $violationList = $this->getViolationList(
            RangeGenerator::class,
            "RangeTrue",
        );
        $this->assertCount(0, $violationList);
    }

    public function testRangeFalse(): void
    {
        $violationList = $this->getViolationList(
            RangeGenerator::class,
            "RangeFalse",
            $testAttributeInstance,
        );
        $this->assertCount(1, $violationList);
        $this->assertEquals(
            "The value must lie in the range from $testAttributeInstance->min to $testAttributeInstance->max",
            $violationList[0]->getMessage(),
        );
    }
}
