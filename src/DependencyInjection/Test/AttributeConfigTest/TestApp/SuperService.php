<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp;



use Kaa\DependencyInjection\Attribute\Inject;
use Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\DynamicFactory\ServiceWithClassDynamicFactory;
use Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\DynamicFactory\ServiceWithNamedDynamicFactory;
use Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\Named\NamedServiceInterface;
use Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\StaticFactory\ServiceWithStaticFactory;
use Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\When\WhenParentInterface;
use Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\WhenFactory\AbstractService;

readonly class SuperService
{
    public function __construct(
        public ServiceWithClassDynamicFactory $serviceWithClassDynamicFactory,
        public ServiceWithNamedDynamicFactory $serviceWithNamedDynamicFactory,

        #[Inject('first')]
        public NamedServiceInterface $firstNamed,
        #[Inject('second')]
        public NamedServiceInterface $secondNamed,

        public ServiceWithStaticFactory $serviceWithStaticFactory,

        public WhenParentInterface $whenService,

        public AbstractService $whenFactoryService,
    ) {
    }
}
