<?php

declare(strict_types=1);

namespace Kaa\Validator\InterceptorGenerator;

use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Exception\InvalidArgumentException;
use Kaa\Validator\Exception\UnsupportedAssertException;
use Kaa\Validator\Exception\ValidatorReturnValueException;
use Kaa\Validator\GeneratorContext;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;

class ValidatorModelGenerator
{
    public function __construct(
        private GeneratorContext $generatorContext,
        private string $violationList,
    ) {
    }

    public function parseDocComment(string $docComment) : string {
        preg_match_all("/(?<=@var)(.+?)(?=\[\])/", $docComment, $matches);
        return trim($matches[0][0]);
    }

    /**
     * @throws \ReflectionException
     * @throws InvalidArgumentException
     * @throws UnsupportedAssertException
     * @throws ValidatorReturnValueException
     * @throws InaccessiblePropertyException
     */
    public function generate(AvailableVar $varToValidate) : array {
        $generatedCode = [];
        $reflectionClass = new ReflectionClass($varToValidate->type);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $assertAttributes = $reflectionProperty->getAttributes(
                Assert::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );

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

            $type = $reflectionProperty->getType();
            if (!$type instanceof ReflectionNamedType) {
                throw new ValidatorReturnValueException(
                    sprintf(
                        '%s::%s must have type and it must not be union or intersection',
                        $varToValidate->name,
                        $reflectionProperty->getName(),
                    )
                );
            }
            if (class_exists($type->getName()) && $varToValidate->type !== $type->getName()) {
                $modelName = $varToValidate->name . "_" . $reflectionProperty->getName();
                $newVarToValidate = new AvailableVar($modelName, $type->getName());
                $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $varToValidate->name);
                $code = <<<'PHP'
/**
* @var \%s $%s
*/
if (%s !== null){
    $%s = %s;
PHP;
                $generatedCode[] = [sprintf(
                    $code,
                    $newVarToValidate->type,
                    $newVarToValidate->name,
                    $accessCode,
                    $newVarToValidate->name,
                    $accessCode,
                )];
                $generatedCode[] = self::generate($newVarToValidate);
                $generatedCode[] = ['}'];
            } elseif ($type->getName() === 'array') {
                $modelName = $varToValidate->name . "_" . $reflectionProperty->getName();
                $modelType = self::parseDocComment($reflectionProperty->getDocComment());
                $newVarToValidate = new AvailableVar($modelName, $modelType);
                $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $varToValidate->name);
                $code = <<<'PHP'
/**
* @var %s $%s
*/
foreach (%s as $%s) {
PHP;
                $generatedCode[] = [sprintf(
                    $code,
                    $newVarToValidate->type,
                    $newVarToValidate->name,
                    $accessCode,
                    $newVarToValidate->name,
                )];
                $generatedCode[] = self::generate($newVarToValidate);
                $generatedCode[] = ['}'];
            }

        }
        return array_merge(...$generatedCode);
    }
}