# Kaa Security (Authorization) Developers Guide

Improving and widening functionality of Kaa Security (Authorization) module is discussed in this guide.

## Implementing new classes

If you want to implement a new class with custom logic, you should follow some rules depending on exact class you want
to write. There are the following options:

- a new `User`
- a new `Token`
- a new `Voter`
- a new `Strategy`
- a new `Exception`

### New `User` classes

A new `User` class should:

- implement `Kaa\Security\User\UserInterface` or extend `Kaa\Security\User\InMemoryUser` class;
- be placed in `Kaa\Security\User` namespace.

Implementing`Kaa\Security\User\UserInterface` means that your class should define the following methods:

```php
/**
 * @return string[]
 */
public function getAttributes(): array;

/**
 * @return string[]
 */
public function getRoles(): array;

public function getIdentifier(): string;
```

### New `Token` classes

A new `Token` class should:

- implement `Kaa\Security\Token\TokenInterface` or extend `Kaa\Security\Token\AbstractToken` abstract class;
- be placed in `Kaa\Security\Token` namespace.

Implementing`Kaa\Security\Token\TokenInterface` means that your class should define the following methods:

```php
public function getUserIdentifier(): ?string;

/**
 * @return string[]
 */
public function getRoles(): array;

/**
 * @return string[]
 */
public function getAttributes(): array;

public function getUser(): ?UserInterface;

public function setUser(UserInterface $user): void;
```

### New `Voter` classes

A new `Voter` class should:

- implement `Kaa\Security\Voter\RoleVoterInterface` or extend `Kaa\Security\Voter\RoleVoter` class;
- be placed in `Kaa\Security\Voter` namespace.

Implementing`Kaa\Security\Voter\RoleVoterInterface` means that your class should define the following method:

```php
/**
 * @param string[] $requiredRoles
 */
public function vote(TokenInterface $token, array $requiredRoles): SecurityVote;
```

### New `Strategy` classes

A new `Strategy` class should:

- implement `Kaa\Security\Strategy\SecurityStrategyInterface`;
- be placed in `Kaa\Security\Strategy` namespace.

Implementing`Kaa\Security\Strategy\StrategyInterface` means that your class should define the following method:

```php
/**
 * @param SecurityVote[] $results
 * @return bool
 */
public function decide(array $results): bool;
```

### New `Exception` classes

A new `Exception` class should:

- extend `Kaa\CodeGen\Exception\CodeGenException` or `Kaa\CodeGen\Exception\Exception` class depending on type of the
exception;
- be placed in `Kaa\Security\Exception` namespace.
