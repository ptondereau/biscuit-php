# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 0.3.x   | Yes                |
| < 0.3   | No                 |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

If you discover a security vulnerability in biscuit-php, please report it responsibly through one of the following channels:

1. **GitHub Private Vulnerability Reporting** — Use the [Security Advisories](https://github.com/ptondereau/biscuit-php/security/advisories/new) page to privately report the issue.
2. **Email** — Send a detailed report to [pierre.tondereau@protonmail.com](mailto:pierre.tondereau@protonmail.com).

### What to Include

- A description of the vulnerability and its potential impact
- Steps to reproduce or a proof of concept
- The version(s) affected
- Any suggested fix, if you have one

### What to Expect

- **Acknowledgement** within 48 hours of your report
- **Status updates** as we investigate and work on a fix
- **Credit** in the security advisory (unless you prefer to remain anonymous)

We aim to release a patch for confirmed vulnerabilities as quickly as possible.

## Scope

Since biscuit-php provides PHP bindings for [Biscuit](https://www.biscuitsec.org) authorization tokens, the following areas are particularly relevant:

- Token creation, parsing, and validation
- Cryptographic key handling (Ed25519, Secp256r1)
- Memory safety in the Rust/PHP FFI boundary
- Authorization policy evaluation

Vulnerabilities in upstream dependencies ([biscuit-auth](https://github.com/biscuit-auth/biscuit-rust), [ext-php-rs](https://github.com/extphprs/ext-php-rs)) should be reported to their respective maintainers, but feel free to notify us as well so we can track the impact on this project.

## Disclosure Policy

We follow a coordinated disclosure process:

1. The reporter submits the vulnerability privately.
2. We confirm and assess the issue.
3. We develop and test a fix.
4. We release the fix and publish a security advisory.
5. The vulnerability details are made public after users have had reasonable time to update.
