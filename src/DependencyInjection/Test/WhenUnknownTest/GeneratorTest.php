<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenUnknownTest;

use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\GenerationManager;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\Test\Utils;
use Kaa\DependencyInjection\Test\WhenUnknownTest\TestApp\SuperService;
use Kaa\DependencyInjection\Test\WhenUnknownTest\TestApp\When\DefaultWhenService;
use Kaa\DependencyInjection\Test\WhenUnknownTest\TestApp\WhenFactory\DefaultService;
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

    public function testWhenInterface(): void
    {
        $superServiceCode = $this->instanceProvider->provideInstanceCode(SuperService::class);

        /** @var SuperService $superService */
        $superService = Utils::eval($superServiceCode);

        $this->assertInstanceOf(DefaultWhenService::class, $superService->whenService);
    }

    public function testWhenFactory(): void
    {
        $superServiceCode = $this->instanceProvider->provideInstanceCode(SuperService::class);

        /** @var SuperService $superService */
        $superService = Utils::eval($superServiceCode);

        $this->assertInstanceOf(DefaultService::class, $superService->whenFactoryService);
    }
}
