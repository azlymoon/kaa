Библиотека кодогенерации для Kaa Framework.

Делает очень простую вещь - запускает все модули генерации,
которые определены в конфиге.

Пример использования:

php-конфиг:

```php
require_once '../vendor/autoload.php';

$config = new \Kaa\CodeGen\Config\PhpConfig(
    generators: [
        new DependencyInjectionGenerator(),
        new RouterGenerator(),
    ],
    userConfig: [
        'code_gen_directory' => dir(__DIR__) . '/codegen',
        'code_gen_namespace' => 'App\\CodeGen',
        'router' => [
            'find_action_listener_class_name' => 'FindActionListener',
        ],
    ],
);

(new \Kaa\CodeGen\GenerationManager($config))->generate();
```

yaml-конфиг:
<b> Пока не реализован </b>

Конфиг модуля кодогенерации:
предоставляет информации о необходимых ключах конфигурации для модуля.
Возможно, о контрактах, которые должны быть исполнены до вызова этого модуля,
и контрактах, которые этот модуль предоставляет.
<b> Пока не реализован</b>

<br/>
<br/>

Метод `GeneratorInterface::generate` принимает два параметра:

1. `mixed[] $userConfig` - параметры, указанные пользователем в конфигурации,
   они общие для всех генераторов.
2. `array<string, object> $providedDependencies` - это объекты для кодогенерации,
   которые уже выполненные модули предоставляют следующим.

Например, генератор `DependencyInjectionGenerator`
сгенерировал класс `Container` со статическим методом `Container::getInstance(string $className)`,
позволяющим получить объект класса по имени класса или интерфейса.
Тогда его реализация может выглядеть так:

```php
public class DependencyInjectionGenerator implements \Kaa\CodeGen\GeneratorInterface
{
    public function generate(array $userConfig, array $providedDependencies): array
    {
        // Какой-то длинный код для кодогенерации...
        
        $newInstanceGenerator = new class($userConfig) : \Kaa\CodeGen\Contract\InstanceProviderInterface {
            public function __construct(
                private readonly array $userConfig,
            ) {
            }
            
            public function getNewInstanceCode(string $className): string {
                return sprintf('%s\\Container::getInstance(%s)', $this->userConfig['code_gen_namespace'], $className);
            }
        };
        
        return [\Kaa\CodeGen\Contract\InstanceProviderInterface::class => $newInstanceGenerator];
    }
}
```

Теперь `RouterGenerator`, который идёт после `DependencyInjectionGenerator` может использовать эту реализацию,
например, для создания объектов контроллеров:

```php
public class RouterGenerator implements \Kaa\CodeGen\GeneratorInterface
{
    public function generate(array $userConfig, array $providedDependencies): array
    {
        // Какой-то длинный код для кодогенерации...
        
        // Роутер не предоставляет ничего другим генераторам.
        return [];
    }
    
    /**
    * Будет вызван где-то внутри этого класса,
    * когда нужно будет сгенерировать код, создающий объекты контроллеров
    */
    private function getNewInstanceCode(string $className, array $providedDependencies): string
    {
        // Проверка нужна, потому что этот генератор может быть вызван раньше,
        // чем генераторы предоставляющие реализацию интерфейса, либо таких генераторов может вообще не быть в конфигурации
        if (isset($providedDependencies[\Kaa\CodeGen\Contract\InstanceProviderInterface::class])) {
            return $userConfig[\Kaa\CodeGen\Contract\InstanceProviderInterface::class]->getNewInstanceCode($className);
        }
        
        return "new $className()";
    }
}
```
