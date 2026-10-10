# Contributing

Thanks for considering a contribution. This document covers the workflow expectations and conventions for every package of this repository: the `biscuit` PHP extension, the stubs, the Symfony bundle and the Laravel package.

All contributors are expected to follow the [Code of Conduct](CODE_OF_CONDUCT.md).

## Reporting bugs and proposing features

Use the [biscuit-php issue tracker](https://github.com/ptondereau/biscuit-php/issues). Every package is developed in this repository under `packages/`; [biscuit-php-stubs](https://github.com/ptondereau/biscuit-php-stubs), [biscuit-sf-bundle](https://github.com/ptondereau/biscuit-sf-bundle) and [biscuit-laravel](https://github.com/ptondereau/biscuit-laravel) are read-only mirrors.

For security vulnerabilities, do not open a public issue. See [SECURITY.md](SECURITY.md).

## Development setup

```bash
git clone https://github.com/ptondereau/biscuit-php.git
cd biscuit-php
composer install
cargo build
```

The PHP packages need the extension. Load `target/debug/libbiscuit_php.so` with `-dextension=` or an `extension=` line in your `php.ini`.

## Quality gate

Every change must keep the checks of the packages it touches passing.

### Extension

```bash
cargo clippy -- -D warnings
php -dextension=target/debug/libbiscuit_php.so vendor/bin/phpunit
vendor/bin/mago format --dry-run
vendor/bin/mago lint
```

Run `vendor/bin/mago format` to fix formatting.

### Stubs

The stubs in [`packages/stubs/`](packages/stubs) are maintained by hand, one file per class. When changing the PHP API exposed from Rust, update the matching stub in the same pull request.

```bash
find packages/stubs -name '*.php' -print0 | xargs -0 -n1 php -l
composer validate --strict packages/stubs/composer.json
```

### Symfony bundle

```bash
cd packages/symfony-bundle
composer install
composer check
```

This runs `php-cs-fixer`, `phpstan` at level 8 and `phpunit`. Run `composer cs-fix` to fix style issues.

CI runs it against PHP 8.1 to 8.5 and Symfony 6.4, 7.4, 8.0 and 8.1. To reproduce a specific cell locally, set the relevant Symfony version constraint and run `composer update` before `composer check`.

### Laravel package

```bash
cd packages/laravel
composer install
composer check
```

This runs `mago` format, lint and analyze, `phpstan` at level max and `pest`. Run `composer format` to fix formatting.

CI runs it against PHP 8.2 to 8.5 and Laravel 12 and 13.

## Pull request expectations

- Branch from `main`. Keep the diff focused on one logical change per PR.
- Add or update tests for any behavior change. Tests for new features must exist before the implementation lands.
- Update the root `CHANGELOG.md` under `## [Unreleased]`, in the section of the package you changed. Use the appropriate Keep a Changelog category (`Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`).
- Update the package `README.md` if you add, remove, or rename public-facing API, configuration, services, commands, or attributes.
- The CI workflow must be green. PRs failing CI will not be reviewed.

## Commit messages

This project uses [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/). The commit history is parsed for release notes, so consistency matters.

The accepted types are: `feat`, `fix`, `docs`, `chore`, `refactor`, `test`, `ci`, `perf`, `style`, `build`, `revert`.

Use a scope when it clarifies the change, e.g. `feat(laravel): ...`, `fix(voter): ...`, `docs(readme): ...`.

Mark breaking changes with `!` after the type/scope, e.g. `refactor!: rename X to Y`. The PR description should explain the migration path.

Example commit messages from this repository:

```
feat(laravel): add the Laravel package
fix: add cookie extractor to the chain
fix(types): update stubs to match phpstan level
ci: split packages with splitsh-lite to keep commit authors
```

## Sign-off

Sign your commits with `git commit -s` to indicate that you have read and agreed to the [Developer Certificate of Origin](https://developercertificate.org/). The DCO is a lightweight statement that you have the right to submit the code under the project's license.

## Releasing

Every package shares the same version. Maintainers tag releases as `vX.Y.Z` on biscuit-php `main`. The split workflow pushes the tag to the mirrors, which triggers a Packagist update. The root `CHANGELOG.md` `## [Unreleased]` block is renamed to the new version on the tagging commit.
