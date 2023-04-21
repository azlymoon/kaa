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
    private const SECURITY_INTERCEPTOR_INIT_CODE = <<<'PHP'

$token = %tokenCode%;

$voter = %voterCode%;

/**
 * $@var string[] $userRoles
 */
$userRoles = $token->getRoles();

/**
 * @var string[] $requiredRoles
 */
$requiredRoles = [];
PHP;

    private const SECURITY_INTERCEPTOR_VOTER_CODE = <<<'PHP'

$verdict = $voter->vote($userRoles, $requiredRoles);

if ($verdict != SecurityVote::grant) {
    if ($token->getUser == null) {
        \Kaa\Security\Exception\AccessDeniedException::throw('Access denied: you have to log in.');
    }
    \Kaa\Security\Exception\AccessDeniedException::throw('Access denied: user \'%s\' does not have required roles.',
                                                         $token->getUserIdentifier);
    throw new \Kaa\Security\Exception\AccessDeniedException("Access denied");
}
PHP;

    /**
     * @var string[] $roles
     */
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

//        $voters = $userConfig['security']['voters'];
//        if (empty(array_intersect(implode("", $this->roles), $securityUserContext->getRoles()))) {
//        if (empty(array_intersect($this->roles, $securityUserContext->getRoles()))) {
//            throw new NoDependencyException('Access denied: roles do not match');
//        }
        $newInstanceGenerator->provideInstanceCode('RoleVoter');

        $replacements = [
            '%tokenCode%' => $newInstanceGenerator->provideInstanceCode('AbstractToken'),
            '%voterCode%' => $newInstanceGenerator->provideInstanceCode('RoleVoter'),
        ];

        /**
         * @param string[] $code
         */
        $code = [];

        $code[] = strtr(self::SECURITY_INTERCEPTOR_INIT_CODE, $replacements);

        foreach ($this->roles as $role) {
            $code[] = sprintf('$requiredRoles[] = \'%s\';', $role);
        }

        $code[] = self::SECURITY_INTERCEPTOR_VOTER_CODE;

        return implode("\n", $code);
    }
}
