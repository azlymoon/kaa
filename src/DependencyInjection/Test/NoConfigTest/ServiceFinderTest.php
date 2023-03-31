<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\NoConfigTest;

use Exception;
use Kaa\DependencyInjection\Attribute\When;
use Kaa\DependencyInjection\Collection\Dependency\Dependency;
use Kaa\DependencyInjection\Collection\Dependency\DependencyCollection;
use Kaa\DependencyInjection\Collection\FactoryCollection;
use Kaa\DependencyInjection\Collection\Service\ServiceDefinition;
use Kaa\DependencyInjection\ServiceFinder\ServiceFinder;
use Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Service\InnerServiceA;
use Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Service\InnerServiceAInterface;
use Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Service\InnerServiceB;
use Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Service\SuperService;
use PHPUnit\Framework\TestCase;

class ServiceFinderTest extends TestCase
{
    private ?array $userConfig;

    private ?ServiceFinder $serviceFinder;

    protected function setUp(): void
    {
        $configPath = __DIR__ . '/TestApp/config.php';
        $this->userConfig = (require $configPath)->getUserConfig();
        $this->serviceFinder = new ServiceFinder();
    }

    protected function tearDown(): void
    {
        $this->userConfig = null;
        $this->serviceFinder = null;
    }

    /**
     * @throws Exception
     */
    public function testFindsThee(): void
    {
        $services = $this->serviceFinder->findServices($this->userConfig);
        $this->assertCount(3, $services);
    }

    /**
     * @throws Exception
     */
    public function testFindsCorrectServices(): void
    {
        $services = $this->serviceFinder->findServices($this->userConfig);
        $serviceNames = array_map(static fn(ServiceDefinition $s) => $s->name, $services);
        $this->assertEqualsCanonicalizing(
            [SuperService::class, InnerServiceA::class, InnerServiceB::class],
            $serviceNames
        );
    }

    /**
     * @throws Exception
     */
    public function testGeneratesCorrectDefinitions(): void
    {
        $services = $this->serviceFinder->findServices($this->userConfig);

        $superService = new ServiceDefinition(
            class: SuperService::class,
            name: SuperService::class,
            aliases: [SuperService::class],
            dependencies: new DependencyCollection([
                new Dependency(InnerServiceAInterface::class, 'innerServiceA'),
                new Dependency(InnerServiceB::class, 'innerServiceB'),
            ]),
            environments: [When::DEFAULT_ENVIRONMENT],
            factories: new FactoryCollection(),
            isSingleton: true
        );

        $innerServiceA = new ServiceDefinition(
            class: InnerServiceA::class,
            name: InnerServiceA::class,
            aliases: [InnerServiceA::class, InnerServiceAInterface::class],
            dependencies: new DependencyCollection([
                new Dependency(InnerServiceB::class, 'innerServiceB'),
            ]),
            environments: [When::DEFAULT_ENVIRONMENT],
            factories: new FactoryCollection(),
            isSingleton: true
        );

        $innerServiceB = new ServiceDefinition(
            class: InnerServiceB::class,
            name: InnerServiceB::class,
            aliases: [InnerServiceB::class],
            dependencies: new DependencyCollection(),
            environments: [When::DEFAULT_ENVIRONMENT],
            factories: new FactoryCollection(),
            isSingleton: true
        );

        $this->assertEqualsCanonicalizing([$superService, $innerServiceA, $innerServiceB], $services);
    }
}
