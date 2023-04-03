<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Service;

class SuperService
{
    private InnerServiceAInterface $innerServiceA;
    private InnerServiceB $innerServiceB;

    public function __construct(
        InnerServiceAInterface $innerServiceA,
        InnerServiceB $innerServiceB,
    ) {
        $this->innerServiceA = $innerServiceA;
        $this->innerServiceB = $innerServiceB;
    }

    public function getInnerServiceA(): InnerServiceAInterface
    {
        return $this->innerServiceA;
    }

    public function getInnerServiceB(): InnerServiceB
    {
        return $this->innerServiceB;
    }
}
