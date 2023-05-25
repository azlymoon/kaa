## Документация разработчика к библиотеки Kaa\Validator

### Общая структура Validator

Обобщенная модель Validator состоит из нескольких блоков:
1. Интерцепторов валидатора
2. Стратегий генерации проверок
3. Проверок

Точкой входа является аттрибут Validator. В нем создается объект `ValidatorGenerator`. 
В дальнейшем внутри данного класса происходит перебор всех полей модели, составление списка
необходимых проверок, получение всех необходимых данных и генерация соответствующего кода.

### Добавление пользовательских правил валадиции

Добавление пользовательских правил валидации возможно благодаря редактированию исходного кода. 
Для этого необходимо выполнять ряд действий.

### Создание пользовательского `Assert`

Файл необходимо размещать в папке `Kaa\Validator\Assert`. Класс должен быть наследником 
`Kaa\Validator\Assert\Assert`.   

В конструкторе необходимо перечислить следующие параметры:
1. `public string $message` - Стандартное сообщение при провале проверки
2. `private array $allowTypes` - Список разрешенных типов для данной проверки, задается
по умолчанию.
3. `public int $value` - Вариативный параметр. Задается при необходимости взятия  
данных от пользователя. Тип может быть любым.

Необходимо реализовать все родительские методы. Для всех `Assert` они одинаковые,
поэтому достаточно взять из готовых решений.

```php
    public function supportsType(string $typeName): bool
    {
        return (in_array($typeName, $this->allowTypes, true));
    }
```

```php
    /**
     * @return string[]
     */
    public function getAllowTypes(): array
    {
        return $this->allowTypes;
    }
```

### Создание пользовательской стратегии генерации проверки

Файл необходимо размещать в папке `Kaa\Validator\Strategy`. Класс должен реализовать
интерфейс `Kaa\Validator\Strategy\AssertGeneratorInterface`.

Рассмотрим пример создания генератора на основе правила `GreaterThan`. 

В первую очередь необходимо реализовать метод `supports`, проверяющий принадлежность
генератора определенному `Assert`.

```php
    public function supports(Assert $assert): bool
    {
        return $assert instanceof GreaterThan;
    }
```

Далее реализуем основной метод `generateAssert`. Все входные параметры необходимо оставить
как в примере ниже.

`Assert $assert` - наше правило валидации.  
`ReflectionProperty $reflectionProperty` - рефлексия поля, которое валидируем.  
`AvailableVar $modelVar` - имя валидируемой модели.  
`string $violationListVarName` - имя списка с ошибками.  
`string $accessCode` - сгенерированный код для получения доступа к полю.

Далее формируем условие проверки нашего правила. В случае, если оно не выполняется, 
создаем объект ошибки и кладем в список с ошибками. Далее подставляем все необходимые 
параметры в условие и возвращаем массив со сгенерированным кодом.

Пример генератора проверки `GreaterThan`
```php
<?php

declare(strict_types=1);

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\GreaterThan;
use ReflectionException;
use ReflectionProperty;

#[PhpOnly]
class GreaterThanGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof GreaterThan;
    }

    /**
     * @param GreaterThan $assert
     * @throws InaccessiblePropertyException
     * @throws ReflectionException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName,
        string $accessCode,
    ): array {
        $code = <<<'PHP'
if (%s <= %s){
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
        $message = $assert->message;
        $message = preg_replace('/{{ compared_value }}/', (string)$assert->value, $message);
        $code = sprintf(
            $code,
            $accessCode,
            $assert->value,
            $violationListVarName,
            $modelVar->type,
            $reflectionProperty->name,
            $message
        );
        return [$code];
    }
}
```

В случае добавления правила с более сложной логикой общая идея будет такой же. За примером
можно обратиться к генератору `TypeGenerator`.

### Добавление генератора в контекст

После создания генератора его необходимо добавить в список, для того чтобы валидатор
при проверках `Assert` нашел его.  

Для этого необходимо добавить объект генератора в список `stretagies` в 
`Kaa\Validator\GeneratorContext`. 

После выполнения этих этапов можно добавлять аттрибут с Вашим правилом и проверять
его работоспособность. 

### Тестирование пользовательских правил

Для тестирования правил необходимо две вещи:
1. Добавить поле для проверки и аттрибут с Вашим правилом в тестовую модель.
2. Добавить тесты на корректность проверки.

Общая идея тестирования подобного вида кодогенерации заключается в том, что мы 
формируем код, который хотим получить, запускаем его с соблюдением всех условий и 
получаем на выходе массив с ошибками. Далее мы смотрим, получили ли мы ожидаемые ошибки
или нет. 

### Добавление поля в тестовую модель

Необходимо добавить собственные поля в модель `Kaa\Validator\Test\TestModel`. 

```php
    #[Assert\GreaterThan(18)]
    public int $GreaterThanFalse = 15;

    #[Assert\GreaterThan(18)]
    public int $GreaterThanTrue = 19;
```

### Добавление тестов для проверки правил

Добавлять тесты нужно в `Kaa\Validator\Test\ValidatorTest`. Интерфейс собирающий данные
из модели и генерирующий отсутствующую информацию уже реализован. Достаточно 
внутри тестирующей функции вызвать метод `getViolationList()`, который вернет список с ошибками.  
В качестве параметров нужно передать полное имя генератора, а также имя поля, проверяемое 
в тестирующей модели. 
В качестве третьего параметра можно передать `$testAttributeInstance`. Аттрибут необходим
для получения значения, с которым возможно идет сравнение. В сообщение об ошибке
может оказаться именно это число. Пример использования смотрите ниже.

Необходимо описать как минимум два теста, проверяющих случаи провала проверки и успеха.  

В случаи успеха список с ошибками должен быть пустым. Именно это и будем проверять.

```php
    public function testGreaterThanTrue(): void
    {

        $violationList = $this->getViolationList(
            GreaterThanGenerator::class,
            "GreaterThanTrue",
        );
        $this->assertCount(0, $violationList);
    }
```

В случаи провала проверки необходимо добавить ошибку в список. Будем проверять, что в
список попала нужная нам ошибка с нужным сообщением. 

```php
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
```

Для проверки более сложной логики валидаций нужно добавить большее количество тестов, 
проверяющих большое количество возможных исходов. 

Данное тестирование покрывает исключительно кодогенерацию и валидность генерируемого кода.





