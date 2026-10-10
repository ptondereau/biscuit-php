# Contributing

Thanks for considering a contribution. This document covers the workflow expectations and conventions for this repository.

All contributors are expected to follow the [Code of Conduct](CODE_OF_CONDUCT.md).

## Reporting bugs and proposing features

Use the [issue tracker](https://github.com/ptondereau/biscuit-sf-bundle/issues). The issue templates collect the information that will get your report triaged the fastest.

For security vulnerabilities, do not open a public issue. See [SECURITY.md](SECURITY.md).

## Development setup

```bash
git clone https://github.com/ptondereau/biscuit-sf-bundle.git
cd biscuit-sf-bundle
pie install ptondereau/biscuit-php:0.4.0
composer install
```

## Quality gate

Every change must keep the full check passing:

```bash
composer check
```

This runs:

- `php-cs-fixer` (style enforcement)
- `phpstan` at level 8 (static analysis)
- `phpunit` (the test suite)

To auto-fix style issues:

```bash
composer cs-fix
```

## Pull request expectations

- Branch from `main`. Keep the diff focused on one logical change per PR.
- Add or update tests for any behavior change. Tests for new features must exist before the implementation lands.
- Update `CHANGELOG.md` under the `## [Unreleased]` section. Use the appropriate Keep a Changelog category (`Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`).
- Update the `README.md` if you add, remove, or rename public-facing configuration, services, commands, or attributes.
- The CI workflow must be green. PRs failing CI will not be reviewed.

## Commit messages

This project uses [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/). The commit history is parsed for release notes, so consistency matters.

The accepted types are: `feat`, `fix`, `docs`, `chore`, `refactor`, `test`, `ci`, `perf`, `style`, `build`, `revert`.

Use a scope when it clarifies the change, e.g. `fix(voter): ...`, `feat(extractor): ...`, `docs(readme): ...`.

Mark breaking changes with `!` after the type/scope, e.g. `refactor!: rename X to Y`. The PR description should explain the migration path.

Example commit messages from this repository:

```
feat: add resource string in voter
fix: add cookie extractor to the chain
fix(types): update stubs to match phpstan level
chore: align composer.json with v0.1.0 launch
```

## Sign-off

Sign your commits with `git commit -s` to indicate that you have read and agreed to the [Developer Certificate of Origin](https://developercertificate.org/). The DCO is a lightweight statement that you have the right to submit the code under the project's license.

## Local CI parity

The GitHub Actions workflow runs the same `composer check` against PHP 8.1, 8.2, 8.3, 8.4, 8.5 and Symfony 6.4, 7.4, 8.0. To reproduce a specific cell locally, set the relevant Symfony version constraint and run `composer update` before `composer check`.

## Releasing

Maintainers tag releases as `vX.Y.Z` on `main`. Tagging triggers a Packagist update. The `CHANGELOG.md` `## [Unreleased]` block is renamed to the new version on the tagging commit.
