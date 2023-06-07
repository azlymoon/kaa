# KHttpClient
KHttpClient представляет собой компонент низкоуровнего HTTP клиента, использующий cURL.
## Требования
Библиотека требует PHP 7.4 и выше и последнюю версию [KPHP](https://github.com/VKCOM/kphp).
## Структура библиотеки
Ниже рассмотрены ключевые модули (классы) логики работы компонента:
- `CurlHttpClient` – модуль cURL реализации клиента,
- `CurlResponse` – класс единицы запроса,
- `CurlClientState` – класс состояний клиента,
- `ExtractedHttpClient` – портированный трейт функционала валидации запроса
## Синтаксис работы с KPHP 
## Хранение параметров запросов
Для корректной работы библиотеки требуется хранить параметры запросов. С данной целью был создан класс `Options`, хранящий следующие параметры:
- `string $authBasic` – 
- `string $authBearer` –
- `string[] $query` – 
- `string[] $headers` – 
- `array<string, array<string>> $normalizedHeaders` – 
- `string $proxy`
- `string $noProxy`
- `float $timeout`
- `string $noProxy`
- `string $bindTo`
- `mixed $localCert`
- `mixed $localPk`
- `mixed $userData`
- `int $maxRedirects`
- `string $httpVersion`
- `string $baseUri`
- `mixed $buffer`
- `?callable(mixed):mixed $onProgress`
- `string[] $resolve`
- `mixed $body`
- `string $json`

