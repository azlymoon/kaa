<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenThrowsTest;

use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\GenerationManager;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\Exception\EnvImplementationNotFoundException;
use Kaa\DependencyInjection\Test\Utils;
use Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\Service\Factory\AbstractClass;
use Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\Service\When\WhenInterface;
use Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\SuperService;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    private ?InstanceProvider $instanceProvider;

    public function setUp(): void
    {
        $configPath = __DIR__ . '/TestApp/config.php';
        $generationManager = new GenerationManager(require $configPath);

        $this->instanceProvider = $generationManager->generate()->get(InstanceProviderInterface::class);
        $this->instanceProvider->provideInstanceCode(SuperService::class);
        $this->instanceProvider->dump();
    }

    public function tearDown(): void
    {
        $this->instanceProvider = null;
    }

    public function testThrowsOnWhen(): void
    {
        $abstractClassCode = $this->instanceProvider->provideInstanceCode(WhenInterface::class);

        $this->expectException(EnvImplementationNotFoundException::class);
        Utils::eval($abstractClassCode);
    }

    public function testThrowsOnWhenFactory(): void
    {
        $abstractClassCode = $this->instanceProvider->provideInstanceCode(AbstractClass::class);

        $this->expectException(EnvImplementationNotFoundException::class);
        Utils::eval($abstractClassCode);
    }
}
