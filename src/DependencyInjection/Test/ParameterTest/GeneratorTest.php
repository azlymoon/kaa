<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\ParameterTest;

use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\GenerationManager;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\Test\ParameterTest\TestApp\SuperService;
use Kaa\DependencyInjection\Test\Utils;
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

    public function testParam(): void
    {
        $superServiceCode = $this->instanceProvider->provideInstanceCode(SuperService::class);

        /** @var SuperService $superService */
        $superService = Utils::eval($superServiceCode);

        $this->assertSame('param_value', $superService->param);
    }

    public function testEnvParam(): void
    {
        $superServiceCode = $this->instanceProvider->provideInstanceCode(SuperService::class);

        /** @var SuperService $superService */
        $superService = Utils::eval($superServiceCode);

        $this->assertSame('env_param_value', $superService->envParam);
    }

    public function testBindedParam(): void
    {
        $superServiceCode = $this->instanceProvider->provideInstanceCode(SuperService::class);

        /** @var SuperService $superService */
        $superService = Utils::eval($superServiceCode);

        $this->assertSame(1, $superService->bindedParam);
    }
}
