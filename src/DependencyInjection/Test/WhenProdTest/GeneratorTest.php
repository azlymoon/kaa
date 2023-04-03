<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenProdTest;

use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\GenerationManager;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\Test\Utils;
use Kaa\DependencyInjection\Test\WhenProdTest\TestApp\SuperService;
use Kaa\DependencyInjection\Test\WhenProdTest\TestApp\When\WhenImplementationProd;
use Kaa\DependencyInjection\Test\WhenProdTest\TestApp\WhenFactory\ProdService;
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

        $this->assertInstanceOf(WhenImplementationProd::class, $superService->whenService);
    }

    public function testWhenFactory(): void
    {
        $superServiceCode = $this->instanceProvider->provideInstanceCode(SuperService::class);

        /** @var SuperService $superService */
        $superService = Utils::eval($superServiceCode);

        $this->assertInstanceOf(ProdService::class, $superService->whenFactoryService);
    }
}
