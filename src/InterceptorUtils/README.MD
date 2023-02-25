Набор утилит для упрощения кодогенерации интерцепторов для Kaa/Router.

1)

```php
InterceptorUtils::getParameterTypeByName(Action $action, string $parameterName)
```

Возвращает тип параметра метода из Action по имени этого параметра

Бросает ParamNotFoundException,
если у метода нет параметра с таким именем
или тип параметра является
объединением или пересечением

Пример:

```php
use Kaa\CodeGen\Router\Interceptor\InterceptorUtils;

class TestController
{
    public function testMethod(\Test\TestModel $model): void
    {
    }
}

$action = new Action(
            new ReflectionMethod(TestController::class, 'testMethod'),
            new ReflectionClass(TestController::class),
            [], [], [],[],
);

$type = InterceptorUtils::getParameterTypeByName($action, 'model');
// $type === 'Test\TestModel';
```

2)

```php
public static function generateSetStatement(
        ReflectionProperty $reflectionProperty,
        ReflectionClass $reflectionClass,
        string $objectName,
        string $value
): string
```

Генерирует строчку кода, которая устанавливает свойству `$reflectionProperty`
объекта с именем `$objectName`
значение `$value`

Пример:

```php

use Kaa\CodeGen\Router\Interceptor\InterceptorUtils;class TestModel
{
    public string $public;

    private stdClass $private;

    public function setPrivate(stdClass $private): void
    {
        $this->private = $private;
    }
}

$reflectionClass = new ReflectionClass(TestModel::class);

$reflectionPublicProperty = $reflectionClass->getProperty('public');
$publicSetCode = InterceptorUtils::generateSetStatement(
    $reflectionPublicProperty,
    'test',
    '"Hello"',
);

// $publicSetCode === '$test->public = "Hello";'

$reflectionPrivateProperty = $reflectionClass->getProperty('private');
$privateSetCode = InterceptorUtils::generateSetStatement(
    $reflectionPrivateProperty,
    'test',
    'new stdClass()',
);

// $privateSetCode === '$test->setPrivate(new stdClass())'
```

3)

```php
public static function generateGetCode(
        ReflectionProperty $reflectionProperty,
        string $objectName,
): string 
```

Генерирует код, который получает значение
свойства `$reflectionProperty` объекта
с именем `$objectName`

Возвращает код, который может быть использован с правой части от оператора присваивания
или передан в качестве аргумента при вызове метода.

Пример:

```php
use Kaa\CodeGen\Router\Interceptor\InterceptorUtils;class TestModel
{
    public string $public;

    private bool $privateBool;

    private string $private;

    public function isPrivateBool(): bool
    {
        return $this->privateBool;
    }

    public function getPrivate(): string
    {
        return $this->private;
    }
}

$reflectionClass = new ReflectionClass(TestModel::class);

$publicReflectionProperty = $reflectionClass->getProperty('public');
$publicGetCode = InterceptorUtils::generateGetCode($publicReflectionProperty, 'test');

// $publicGetCode === '$test->public'

$privateReflectionProperty = $reflectionClass->getProperty('private');
$privateGetCode = InterceptorUtils::generateGetCode($privateReflectionProperty, 'test');

// $privateGetCode === '$test->getPrivate()'

$privateBoolReflectionProperty = $reflectionClass->getProperty('privateBool');
$privateBoolGetCode = InterceptorUtils::generateGetCode($privateBoolReflectionProperty, 'test');

// $privateBoolGetCode === '$test->isPrivateBool()'
```
