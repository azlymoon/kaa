<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

/**
 * Помечает, что для создания сервиса будет нужна фабрика
 *
 * Если у сервиса есть фабрика, с указанием when, то при работе в окружении отличном от указанного в when,
 * будет вызвана фабрика без when, а если её нет, будет выброшено исключение
 */
#[PhpOnly]
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
readonly class Factory
{
    /**
     * @param string $factory Название сервиса фабрики
     *      (может быть именем класса или интерфейса, либо алиасом сервиса),
     *      либо название класса статической фабрики
     *
     * @param string $method Имя метода, который надо вызвать у фабрики для создания сервиса
     *
     * @param bool $isStatic Если true, то $factoryMethod будет вызван статически у класса $factoryService
     *
     * @param string[]|string $when Фабрика будет вызвана,
     *      только если в runtime config указан такой же app-env как в параметре
     *      (или такой же, как один из тех, что в параметре)
     */
    public function __construct(
        public string $factory,
        public string $method = '__invoke',
        public bool $isStatic = false,
        public array|string $when = [When::DEFAULT_ENVIRONMENT],
    ) {
    }
}
