Router component
======
When your application receives a request, it calls a controller action to generate the response.
The routing configuration defines which action to run for each incoming URL. It also provides other useful features, like generating SEO-friendly URLs (e.g. /read/intro-to-symfony instead of index.php?article_id=57).

This component realised routing (like Symphony routing) which will work in 
KPHP.


Content
---

1. [Getting Started](#getting-started)
2. [Before creating routes](#before-creating-routes)
3. [Creating Routes as Attributes](#creating-routes-as-attributes)
4. [Creating Routes using YAML](#creating-routes-using-yaml)
5. [Variables](#variables)
6. [Generate code](#generate-code)
## Getting Started

```angular2html
composer require kaa/kaa
```

## Before creating routes
Because KPHP don't support Reflexion instead of that we use code generation.
If we want to generate code for routing we should to set up `./config/router.yaml`

First of all, you need to set up global parameters of routers. Global parameters 
should be after `router` key.

Required global parameter which should be in config file is `controller_namespaces`
where you write namespaces where are located your controller classes.

This is the example of standard `router.yaml`
```yaml
router:
  controller_namespaces:
    - Kaa\SampleProject\Controller

```

## Creating routes as attributes
PHP attributes allow to define routes next to the code of the controllers associated to those routes. Attributes are native in PHP 8 and higher versions, so you can use them right away.

Suppose you want to define a get route for the `/healthcheck` URL. There is small example:
```php
<?php

namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;

class HealthCheckController
{
    #[Get('/healthcheck', 'healthcheck')]
    public function healthcheck(): ResponseInterface
    {
        // ...
    }
}
```
This configuration defines a route called `healthcheck` that matches when the user requests `GET` method for the '/blog' URL.
When the match occurs, the application runs the `healthcheck()` method of the `HealthCheckController` class.
> **⚠ WARNING**  
> Controller methods must return instance of some class and not union or intersection

You can add several routes for controller class method:

```php
<?php

namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;
use Kaa\Router\Attribute\Post;

class HealthCheckController
{
    #[Get('/healthcheck', 'healthcheck')]
    #[Post('/posthealthcheck', 'post_healthcheck')]
    public function healthcheck(): ResponseInterface
    {
        // ...
    }
}
```

You also can add `Route` for class, if you want to add some prefix for url:
```php
<?php

namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;
use Kaa\Router\Attribute\Route;

#[Route('/class')]
class HealthCheckController
{
    #[Get('/healthcheck', 'healthcheck')]
    public function healthcheck(): ResponseInterface
    {
        // ...
    }
}
```
Now we have defined behaviour for `GET/class/healthcheck` URL.
> **⚠ WARNING**  
> If you use `Route` for class routing you **can't** add any method of route names

> **⚠ WARNING**  
> If you don't define name for Get/Post/... attributes it will generate automatically

There are list of supported attributes:

| Attribute | Method  |
|-----------|---------|
| `Route`   | No      |
| `Get`     | GET     |
| `Post`    | POST    |
| `Head`    | HEAD    | 
| `Put`     | PUT     |
| `Patch`   | PATCH   |
| `Delete`  | DELETE  |


## Creating routes using YAML
You can also define routes in `./config/router.yaml`
The following example shows how to define in YAML a route called blog_list that associates the `/blog` URL with the `show()` action of the `BlogApiController`:

```yaml
router:
  controller_namespaces:
    - Kaa\SampleProject\Controller

api_post_show:
  path:       /blog
  controller: Kaa\SampleProject\Controller\BlogApiController::show
  methods:    GET|HEAD
```

> **⚠ WARNING**  
> You **must** explicitly indicate HTTP methods for routes


## Variables

Most times some parts of your path are variable, you can define this paths by wrapping 
variable parts in `{}` For example:

### Attribute example
```php
namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;

class HealthCheckController
{
    #[Get('/healthcheck/{id}', 'healthcheck')]
    public function healthcheck(): ResponseInterface
    {
        // ...
    }
}
```
### YAML example
```yaml
router:
  controller_namespaces:
    - Kaa\SampleProject\Controller

api_post_show:
  path:       /api/posts/{id}
  controller: Kaa\SampleProject\Controller\BlogApiController::show
  methods:    GET|HEAD
```

The name of the variable part ({id} in this example) is used to create a PHP variable where that route content is stored and passed to the controller.

## Generate code
After defining routes you need to generate some code for routing before running your app in KPHP:
```php
use Kaa\CodeGenKernel\CodeGenKernel;

(new CodeGenKernel(dirname(__DIR__)))->generate();
```

Run it using **PHP** (not KPHP) and after that you can run your app in KPHP.