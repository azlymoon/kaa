<?php

declare(strict_types=1);

namespace Kaa\Validator\InterceptorGenerator;

use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Exception\InvalidArgumentException;
use Kaa\Validator\Exception\InvalidTypeException;
use Kaa\Validator\Exception\UnsupportedAssertException;
use Kaa\Validator\Exception\ValidatorReturnValueException;
use Kaa\Validator\GeneratorContext;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use Nette\Utils\Reflection;

class ValidatorModelGenerator
{
    private const ALLOW_TYPES = [
        'bool',
        'int',
        'float',
        'string',
        'null',
    ];
    public function __construct(
        private GeneratorContext $generatorContext,
        private string $violationList,
    ) {
    }

    /**
     * @throws InvalidTypeException
     */
    private function getTypeFromDocComment(\ReflectionProperty $reflectionProperty) : string {
        preg_match_all("/(?<=@var)(.+?)(?=\[\])/", $reflectionProperty->getDocComment(), $matches);
        return trim($matches[0][0]);
    }

    private function getFullTypeFromNamespace(\ReflectionClass $reflectionClass, string $type) : ?string {
        $useStatements = Reflection::getUseStatements($reflectionClass);
        if (array_key_exists($type, $useStatements)) {
            return $useStatements[$type];
        }
        return null;
    }

    /**
     * @throws \ReflectionException
     * @throws InvalidArgumentException
     * @throws UnsupportedAssertException
     * @throws ValidatorReturnValueException
     * @throws InaccessiblePropertyException|InvalidTypeException
     */
    public function generate(AvailableVar $varToValidate) : array {
        $generatedCode = [];
        $reflectionClass = new ReflectionClass($varToValidate->type);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $assertAttributes = $reflectionProperty->getAttributes(
                Assert::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );
            if ($reflectionProperty->getType()->getName() !== 'array') {
                foreach ($assertAttributes as $assertAttribute) {
                    $attribute = $assertAttribute->newInstance();

                    if ($attribute->supportsType($reflectionProperty->getType()->getName()) === false) {
                        $allowTypes = implode(", ", $attribute->getAllowTypes());

                        throw new InvalidArgumentException(
                            sprintf(
                                'Type of $%s is %s but should be %s.',
                                $reflectionProperty->getName(),
                                $reflectionProperty->getType()->getName(),
                                $allowTypes,
                            )
                        );
                    }

                    $generator = $this->generatorContext->getStrategy($attribute);
                    if ($generator === null) {
                        throw new UnsupportedAssertException(
                            sprintf(
                                'Could not find strategy that supports %s',
                                $attribute::class,
                            )
                        );
                    }

                    $constraintGeneratedCode = $generator->generateAssert(
                        $attribute,
                        $reflectionProperty,
                        $varToValidate,
                        $this->violationList,
                    );

                    $generatedCode[] = $constraintGeneratedCode;
                }
            }

            if ($reflectionProperty->getType()->getName() === 'array') {
                $typeOfElements = self::getTypeFromDocComment($reflectionProperty);
                if (in_array($typeOfElements, self::ALLOW_TYPES)) {
                    foreach ($assertAttributes as $assertAttribute) {
                        $attribute = $assertAttribute->newInstance();

                        if ($attribute->supportsType($typeOfElements) === false) {
                            $allowTypes = implode(", ", $attribute->getAllowTypes());

                            throw new InvalidArgumentException(
                                sprintf(
                                    'Type of $%s is %s but should be %s.',
                                    $reflectionProperty->getName(),
                                    $typeOfElements,
                                    $allowTypes,
                                )
                            );
                        }

                        $generator = $this->generatorContext->getStrategy($attribute);
                        if ($generator === null) {
                            throw new UnsupportedAssertException(
                                sprintf(
                                    'Could not find strategy that supports %s',
                                    $attribute::class,
                                )
                            );
                        }

                        $newVarToValidate = new AvailableVar(
                            $varToValidate->name . "_" . $reflectionProperty->getName(),
                            $typeOfElements,
                        );

                        $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $varToValidate->name);
                        $code = <<<'PHP'
foreach (%s as $%s) {
PHP;
                        $generatedCode[] = [
                            sprintf(
                                $code,
                                $accessCode,
                                $newVarToValidate->name,
                            )
                        ];

                        $constraintGeneratedCode = $generator->generateAssert(
                            $attribute,
                            $reflectionProperty,
                            $newVarToValidate,
                            $this->violationList,
                        );

                        $generatedCode[] = $constraintGeneratedCode;
                        $generatedCode[] = ['}'];

                    }
                } else {
                    $fullType = $this->getFullTypeFromNamespace($reflectionClass, $typeOfElements);
                    if ($fullType === null) {
                        $fullType = $reflectionClass->getNamespaceName() . "\\" . $typeOfElements;
                    }
                    $newVarToValidate = new AvailableVar(
                        $varToValidate->name . "_" . $reflectionProperty->getName(),
                        $fullType,
                    );

                    $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $varToValidate->name);
                    $code = <<<'PHP'
/**
* @var \%s $%s
*/
foreach (%s as $%s) {
    if ($%s !== null) {
PHP;
                    $generatedCode[] = [
                        sprintf(
                            $code,
                            $newVarToValidate->type,
                            $newVarToValidate->name,
                            $accessCode,
                            $newVarToValidate->name,
                            $newVarToValidate->name,
                        )
                    ];
                    $generatedCode[] = self::generate($newVarToValidate);
                    $generatedCode[] = ['}'];
                    $generatedCode[] = ['}'];
                }
            }

            $typeProperty = $reflectionProperty->getType();
            if (!$typeProperty instanceof ReflectionNamedType) {
                throw new ValidatorReturnValueException(
                    sprintf(
                        '%s::%s must have type and it must not be union or intersection',
                        $varToValidate->name,
                        $reflectionProperty->getName(),
                    )
                );
            }

            if (class_exists($typeProperty->getName()) && $varToValidate->type !== $typeProperty->getName()) {
                $fullType = $this->getFullTypeFromNamespace($reflectionClass, $typeProperty->getName());
                if ($fullType === null) {
                    $fullType = $reflectionClass->getNamespaceName() . "\\" . $typeProperty->getName();
                }

                $newVarToValidate = new AvailableVar(
                    $varToValidate->name . "_" . $reflectionProperty->getName(),
                    $fullType,
                );

                $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $varToValidate->name);
                $code = <<<'PHP'
/**
* @var \%s $%s
*/
if (%s !== null){
    $%s = %s;
PHP;
                $generatedCode[] = [
                    sprintf(
                    $code,
                    $fullType,
                    $newVarToValidate->name,
                    $accessCode,
                    $newVarToValidate->name,
                    $accessCode,
                    )
                ];
                $generatedCode[] = self::generate($newVarToValidate);
                $generatedCode[] = ['}'];
            }



        }
        return array_merge(...$generatedCode);
    }
}