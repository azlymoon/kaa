<?php

declare(strict_types=1);

namespace Kaa\Security\InterceptorGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\NewInstanceGeneratorInterface;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\Action;
use Kaa\Router\Interceptor\AvailableVars;
use Kaa\Router\Interceptor\InterceptorGeneratorInterface;
use Kaa\Security\UserContextInterface;

#[PhpOnly]
readonly class SecurityInterceptorGenerator implements InterceptorGeneratorInterface
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private array $roles,
    ) {
    }

    /**
     * @throws NoDependencyException
     */
    public function generate(
        AvailableVars $availableVars,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string {
        if (!$providedDependencies->has(NewInstanceGeneratorInterface::class)) {
            throw new NoDependencyException('No dependency');
        }

        /** @var NewInstanceGeneratorInterface $newInstanceGenerator */
        $newInstanceGenerator = $providedDependencies->get(NewInstanceGeneratorInterface::class);

        $code = [];
        $code[] = $newInstanceGenerator->getNewInstanceCode('securityUserContext', UserContextInterface::class);

        if (empty(array_intersect(implode("", $this->roles), $securityUserContext->getRoles()))) {
            throw new NoDependencyException('Access denied: roles do not match');

        }
    }
}
