# Kaa Security Module

Helps to check and grant or deny access to particular route to users.

Generates code based on attributes and/or YAML Security config file.

## User

## Voter

For now, only RoleVoter is implemented. It checks whether the user has all required roles.

Voter has method ```vote``` which returns one of three values:

- ```SecurityVote::Grant```
- ```SecurityVote::Abstain```
- ```SecurityVote::Deny```

## Strategy

Strategy is a set of rules which is applied when choosing to grant or deny access to a user.

Strategies use voters to collect votes and then make the decision.

There are some default strategies:

- ```AffirmativeStrategy``` grants access if there is at least 1 granting voter;
- ```ConsensusStrategy``` grants access if number of granting voters is more/not less than denying voters;
- ```UnanimousStrategy``` grants access if there are no denying voters.

You can develop your own strategy, which should implement ```SecurityStrategyInterface```.