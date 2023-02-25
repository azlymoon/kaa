Бандл интерцепторов для конвертации параметров запроса в объекты

* `JsonBodyParamConverter` - Вызывает `\JsonEncoder::decode',
  передавая в него данные из phpInput.

* `FormDataParamConverter` - Создаёт модель с помощью конструктора,
  заполняет её данными через сеттеры и аргументы конструктора, если они есть.
  Берёт данные из multipart/form-data ($_POST) с именами в точности совпадающими
  с именами параметров конструктора или свойств. Поддерживает только скалярные
  типы и массивы скалярных типов.

* `QueryParamConverter` - Создаёт модель с помощью конструктора,
  заполняет её данными через сеттеры и аргументы конструктора, если они есть.
  Берёт данные из параметров запроса ($_GET) с именами в точности совпадающими
  с именами параметров конструктора или свойств. Поддерживает только скалярные
  типы и массивы скалярных типов.

* `ResponseToJsonConverter` - Преобразует возвращаемое значение метода
  или всех метод контроллера в JsonResponse. Если метод контроллера возвращает
  наследника `ResponseInterface`, то его результат не изменяется.

Пример:

```php
use Kaa\CodeGen\ParamConverter\Attribute\JsonBodyParamConverter;
use Kaa\CodeGen\Router\Attribute\Post;
use Kaa\HttpKernel\Response\ResponseInterface;

class User
{
    private int $age;
    
    private string $name;
    
    public function getAge(): int
    {
        return $this->age;
    }
    
    public function setAge(int $age): void
    {
        $this->age = $age;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}

#[ResponseToJsonConverter]
class TestController
{

    #[Post('/user')]
    #[JsonBodyParamConverter('user')]
    public function createUser(User $user): ResponseInterface
    {
        // ...        
    }
}
```
