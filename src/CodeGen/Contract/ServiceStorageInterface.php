<?php

declare(strict_types=1);

namespace Kaa\CodeGen\Contract;

interface ServiceStorageInterface
{
    public function addService(ServiceInfo $serviceInfo): void;
}
