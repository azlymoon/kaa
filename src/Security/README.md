# Kaa Security Module (Authorization)

Helps to check and grant or deny access to particular route depending on specific user.

The process of authorization has two different sides:

- The user receives a specific role when logging in (e.g. ROLE_ADMIN).
- You add code so that a resource (e.g. URL, controller) requires a specific "attribute" (e.g. a role like ROLE_ADMIN) in order to be accessed.

This module generates code based on attributes and/or YAML Security config file.

## User

Basic user has identifier and roles.

InMemoryUser also has password and a bool which represents if the user is banned.

When a user logs in, the getRoles() method is called on the User object to determine which roles this user has.
In the User class the roles are an array that is stored in the database and every user is always given at least one
role: `ROLE_USER`.

Instead of giving many roles to each user, you can define role inheritance rules by creating a role hierarchy in your
`security.yaml` file:

```yaml
security:
    # something here

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
```

## Token

Token is a session identifier which contains current user and his roles.

To learn more please visit Authentication documentation. 

## Voter

Voters are a very powerful way of managing permissions. They allow you to centralize all permission logic and reuse
it in many places.

Voter has method `vote` which returns one of three values:

- `SecurityVote::Grant`
- `SecurityVote::Abstain`
- `SecurityVote::Deny`


For now, only `RoleVoter` is implemented. It checks whether the user has all required roles.

You can develop your custom voters. They should implement [`Kaa\Security\Voter\RoleVoterInterface`](/src/Security/Voter/RoleVoterInterface.php)
or extend [`Kaa\Security\Voter\RoleVoter`](/src/Security/Voter/RoleVoter.php).

## Strategy

Strategy is a set of rules which is applied when choosing to grant or deny access to a user.

Strategies use voters to collect votes and then make the decision.

There are some default strategies:

- ```AffirmativeStrategy``` grants access if there is at least 1 granting voter;
- ```ConsensusStrategy``` grants access if number of granting voters is more/not less than denying voters;
- ```UnanimousStrategy``` grants access if there are no denying voters.
- `PriorityStretegy` grants/denies access according to the highest voter which grants/denies access. 

If none of the built-in strategies fits your use case, you can develop your own strategy, which should implement [`Kaa\Security\Strategy\SecurityStrategyInterface`](/src/Security/Strategy/SecurityStrategyInterface.php).
You should also define the strategy_service option in `security.yaml` to use a custom strategy:

```yaml
security:
    access_decision_manager:
        strategy_service: App\Security\MyCustomSecurityStrategy
        # something here
```
