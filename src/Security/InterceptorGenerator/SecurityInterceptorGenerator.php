<?php

declare(strict_types=1);

namespace Kaa\Security\InterceptorGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\Action;
use Kaa\Router\Interceptor\AvailableVars;
use Kaa\Router\Interceptor\InterceptorGeneratorInterface;
use Kaa\Security\User\InMemoryUser;

#[PhpOnly]
readonly class SecurityInterceptorGenerator implements InterceptorGeneratorInterface
{
    private array $roles;

    /**
     * @param string[] $roles
     */
    public function __construct(
        array $roles,
    ) {
        $this->roles = $roles;
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
        if (!$providedDependencies->has(InstanceProviderInterface::class)) {
            throw new NoDependencyException('No object of InstanceProviderInterface class provided');
        }

        /** @var InstanceProviderInterface $newInstanceGenerator */
        $newInstanceGenerator = $providedDependencies->get(InstanceProviderInterface::class);

        $code = [];

        $code[] = <<<PHP
/**
* @var \Kaa\Security\User\InMemoryUser $user
 */
PHP;
        $code[] = '$user = ' . $newInstanceGenerator->provideInstanceCode('InMemoryUser');

        $code[] = <<<PHP
/**
* @var string[] $requiredRoles
 */
PHP;
        foreach ($this->roles as $role) {
            $code[] = sprintf('$requiredRoles[] = \'%s\';', $role);
        }
//        $voters = $userConfig['security']['voters'];
//        if (empty(array_intersect(implode("", $this->roles), $securityUserContext->getRoles()))) {
//        if (empty(array_intersect($this->roles, $securityUserContext->getRoles()))) {
//            throw new NoDependencyException('Access denied: roles do not match');
//        }
        $code[] = <<<PHP
/**
* @var string[] $userRoles
 */
PHP;
        $code[] = '$userRoles = $user->getRoles()';

        $code[] = <<<PHP
/**
* @var \Kaa\Security\Voter\RoleVoter $voter
 */
PHP;
        $code[] = '$voter = ' . $newInstanceGenerator->provideInstanceCode('RoleVoter');

        $code[] = <<<PHP
/**
* @var \Kaa\Security\SecurityVote $verdict
 */
PHP;
        $code[] = '$verdict = $voter->vote($userRoles, $requiredRoles)';
        $code[] = <<<PHP
if ($verdict != SecurityVote::grant) {
    //??????????
}
PHP;

        return implode('\n', $code);
    }
}
