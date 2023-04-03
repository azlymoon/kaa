<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\NoConfigTest\TestApp\Service;

class InnerServiceA implements InnerServiceAInterface
{
    private InnerServiceB $innerServiceB;

    public function __construct(InnerServiceB $innerServiceB)
    {
        $this->innerServiceB = $innerServiceB;
    }

    public function getInnerServiceB(): InnerServiceB
    {
        return $this->innerServiceB;
    }
}
