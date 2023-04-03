<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\NoConfigTest;

use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\GenerationManager;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\DependencyInjection\Exception\DependencyNotFoundException;
use Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Model\NotAService;
use Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Service\InnerServiceA;
use Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Service\InnerServiceB;
use Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Service\SuperService;
use Kaa\DependencyInjection\Test\Utils;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    private ?ProvidedDependencies $providedDependencies;

    /**
     * @throws NoDependencyException
     */
    protected function setUp(): void
    {
        $configPath = __DIR__ . '/TestApp/config.php';
        $generationManager = new GenerationManager(require $configPath);
        $this->providedDependencies = $generationManager->generate();

        $instanceProvider = $this->providedDependencies->get(InstanceProviderInterface::class);

        $instanceProvider->provideInstanceCode(SuperService::class);
        $instanceProvider->provideInstanceCode(InnerServiceA::class);
        $instanceProvider->provideInstanceCode(InnerServiceB::class);

        $instanceProvider->dump();
    }

    protected function tearDown(): void
    {
        $this->providedDependencies = null;
    }

    /**
     * @throws NoDependencyException
     */
    public function testWontProvideExcluded(): void
    {
        $instanceProvider = $this->providedDependencies->get(InstanceProviderInterface::class);

        $this->expectException(DependencyNotFoundException::class);
        $instanceProvider->provideInstanceCode(NotAService::class);
    }

    /**
     * @throws NoDependencyException
     */
    public function testProvidesSingletons(): void
    {
        $instanceProvider = $this->providedDependencies->get(InstanceProviderInterface::class);
        $superServiceInstanceCode = $instanceProvider->provideInstanceCode(SuperService::class);
        $innerServiceAInstanceCode = $instanceProvider->provideInstanceCode(InnerServiceA::class);
        $innerServiceBInstanceCode = $instanceProvider->provideInstanceCode(InnerServiceB::class);

        /** @var SuperService $superService */
        $superService = Utils::eval($superServiceInstanceCode);
        $superServiceTwo = Utils::eval($superServiceInstanceCode);

        /** @var InnerServiceA $innerServiceA */
        $innerServiceA = Utils::eval($innerServiceAInstanceCode);

        /** @var InnerServiceB $innerServiceB */
        $innerServiceB = Utils::eval($innerServiceBInstanceCode);

        $this->assertSame($superService, $superServiceTwo);
        $this->assertSame($superService->getInnerServiceA(), $innerServiceA);
        $this->assertSame($superService->getInnerServiceB(), $innerServiceB);
        $this->assertSame($superService->getInnerServiceB(), $superService->getInnerServiceA()->getInnerServiceB());
    }
}
