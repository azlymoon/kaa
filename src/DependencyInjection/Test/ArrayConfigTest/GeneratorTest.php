<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace ArrayConfigTest;

use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\GenerationManager;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory\DynamicFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory\ServiceWithClassDynamicFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory\ServiceWithNamedDynamicFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\Named\FirstNamedImplementation;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\Named\NamedServiceInterface;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\Named\SecondNamedImplementation;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\StaticFactory\ServiceWithStaticFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\StaticFactory\StaticFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\SuperService;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\When\WhenImplementationProd;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\When\WhenParentInterface;
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

    public function testWorks(): void
    {
        $this->expectNotToPerformAssertions();
        $superServiceInstanceCode = $this->instanceProvider->provideInstanceCode(SuperService::class);
        Utils::eval($superServiceInstanceCode);
    }

    public function testClassDynamicFactory(): void
    {
        $serviceWithClassDynamicFactoryCode = $this->instanceProvider->provideInstanceCode(
            ServiceWithClassDynamicFactory::class
        );
        Utils::eval($serviceWithClassDynamicFactoryCode);

        $dynamicFactoryCode = $this->instanceProvider->provideInstanceCode(DynamicFactory::class);

        /** @var DynamicFactory $dynamicFactory */
        $dynamicFactory = Utils::eval($dynamicFactoryCode);

        $this->assertTrue($dynamicFactory->wasCalled);
    }

    public function testNamedDynamicFactory(): void
    {
        $serviceWithNamedDynamicFactoryCode = $this->instanceProvider->provideInstanceCode(
            ServiceWithNamedDynamicFactory::class
        );
        Utils::eval($serviceWithNamedDynamicFactoryCode);

        $namedDynamicFactoryCode = $this->instanceProvider->provideInstanceCode('factory.named');

        /** @var DynamicFactory $namedDynamicFactory */
        $namedDynamicFactory = Utils::eval($namedDynamicFactoryCode);

        $this->assertTrue($namedDynamicFactory->wasCalled);
    }

    public function testNamedServices(): void
    {
        $firstServiceCode = $this->instanceProvider->provideInstanceCode('first');
        $secondServiceCode = $this->instanceProvider->provideInstanceCode('second');
        $superServiceCode = $this->instanceProvider->provideInstanceCode(SuperService::class);

        /** @var NamedServiceInterface $firstService */
        $firstService = Utils::eval($firstServiceCode);

        /** @var NamedServiceInterface $secondService */
        $secondService = Utils::eval($secondServiceCode);

        /** @var SuperService $superService */
        $superService = Utils::eval($superServiceCode);

        $this->assertInstanceOf(FirstNamedImplementation::class, $firstService);
        $this->assertInstanceOf(SecondNamedImplementation::class, $secondService);

        $this->assertSame($superService->firstNamed, $firstService);
        $this->assertSame($superService->secondNamed, $secondService);
    }

    public function testStaticFactory(): void
    {
        $serviceWithStaticFactoryCode = $this->instanceProvider->provideInstanceCode(ServiceWithStaticFactory::class);
        Utils::eval($serviceWithStaticFactoryCode);

        $this->assertTrue(StaticFactory::$wasCalled);
    }

    public function testFactoryProvidesSingleton(): void
    {
        $serviceWithStaticFactoryCode1 = $this->instanceProvider->provideInstanceCode(ServiceWithStaticFactory::class);
        $serviceWithStaticFactoryCode2 = $this->instanceProvider->provideInstanceCode(ServiceWithStaticFactory::class);

        $service1 = Utils::eval($serviceWithStaticFactoryCode1);
        $service2 = Utils::eval($serviceWithStaticFactoryCode2);

        $this->assertSame($service1, $service2);
    }

    public function testWhenServiceProd(): void
    {
        $whenServiceCode = $this->instanceProvider->provideInstanceCode(WhenParentInterface::class);

        /** @var WhenParentInterface $whenService */
        $whenService = Utils::eval($whenServiceCode);
        $this->assertInstanceOf(WhenImplementationProd::class, $whenService);
    }
}
