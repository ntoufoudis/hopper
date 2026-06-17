# Contributing to Hopper

Thanks for your interest in improving Hopper. This document covers how to set up the project,
the quality bar a change has to clear, and how to get a pull request merged.

By contributing, you agree that your contributions are licensed under the project's
[MIT License](LICENSE).

## Before you start

- **Bugs and features:** open an issue first for anything non-trivial, so we can agree on the
  approach before code is written. Small, obviously-correct fixes (typos, doc tweaks) can go
  straight to a PR.
- **Security issues:** do **not** open a public issue. See [SECURITY.md](SECURITY.md).
- **Be respectful.** Assume good faith and keep discussion constructive.

## Scope and design intent

Hopper is a **headless, framework-agnostic import engine**. Two principles guide what belongs
here:

- **Parsing is delegated, orchestration is owned.** File parsing goes through
  `maatwebsite/excel`; Hopper owns mapping, validation, transformation, resolution, staging,
  preview, commit, and audit. Please don't reimplement parsing.
- **Correctness over cleverness.** The core guarantees - idempotent re-runnable staging,
  resumable and concurrency-safe commit, exact preview/commit parity - are the point of the
  package. Changes must preserve them.

## Requirements

- PHP `^8.2`
- Composer
- A supported Laravel version is pulled in via dev dependencies (`^12` or `^13`).

## Getting set up

```bash
git clone https://github.com/ntoufoudis/hopper.git
cd hopper
composer install
```

## The quality bar

Every change must pass the full check suite, which is exactly what CI runs:

```bash
composer test
```

That runs three gates, which you can also run individually:

| Command               | What it checks                          |
|-----------------------|-----------------------------------------|
| `composer test:lint`  | Code style (Laravel Pint)               |
| `composer test:types` | Static analysis (PHPStan, **level 10**) |
| `composer test:unit`  | Test suite (Pest)                       |

To auto-fix style issues: `composer lint`.

Non-negotiables:

- **Pint must pass** - no style deviations.
- **PHPStan level 10 must pass** - do **not** lower the level or add baseline entries to make
  an error disappear; fix the underlying type issue (add generics annotations, narrow types,
  etc.).
- **Pest must be green**, and your change must come with tests (see below).

CI runs the suite across a matrix of PHP `8.2`–`8.5` and Laravel `12.*`/`13.*`. A PR can't be
merged until that matrix is green.

## Tests

- **New behaviour needs tests.** Add Pest tests under `tests/` covering the new path.
- **Bug fixes need a regression test** that fails before your fix and passes after.
- **Don't weaken the core-guarantee tests.** If you touch staging, resolution, commit, or
  preview, make sure the idempotency, resume, concurrency, batching, and preview/commit parity
  tests still hold - and add to them if your change introduces a new edge.

## Coding conventions

- `declare(strict_types=1);` at the top of every PHP file.
- Favour `final` classes and `readonly` value objects, consistent with the existing code.
- Put things where they belong:

  | Directory         | Holds                                          |
  |-------------------|------------------------------------------------|
  | `src/Contracts/`  | Interfaces (`Source`, `Resolver`, `Pipe`, …)   |
  | `src/Enums/`      | Enums (`ResolutionType`, `RunStatus`)          |
  | `src/Sources/`    | Source implementations (CSV, Excel)            |
  | `src/Mapping/`    | Strategies, `Mapper`, `ColumnMap`, templates   |
  | `src/Pipeline/`   | Transformation pipe runner                     |
  | `src/Resolution/` | Resolvers and resolution value objects         |
  | `src/Staging/`    | `StagingWriter`, `PreviewBuilder`, `Committer` |
  | `src/Jobs/`       | Queued jobs (`StageChunk`, `CommitChunk`)      |
  | `src/Audit/`      | Audit driver(s) and `ImportEvent`              |
  | `src/Export/`     | Failed-row export                              |
  | `src/Models/`     | Eloquent models                                |
  | `src/Commands/`   | Artisan commands                               |

- Keep the public API small and intentional; new public surface should be discussed in an
  issue first.
- Update the **README** and **CHANGELOG** (`[Unreleased]` section) when your change affects
  usage or behaviour.

## Pull request workflow

1. Branch from the default branch (e.g. `feat/short-description` or `fix/short-description`).
2. Use clear, [Conventional Commits](https://www.conventionalcommits.org/) style messages.
3. Keep PRs focused - one logical change per PR.
4. Run `composer test` locally and make sure it's green.
5. Open the PR with a description of *what* changed and *why*, and link the issue it resolves.
6. Make sure CI (the full matrix) passes; address review feedback.

## Versioning

Hopper follows [Semantic Versioning](https://semver.org/) from `v1.0.0` onwards. Breaking
changes to the public API land only in a major release. If your change is breaking, say so
explicitly in the PR.

Thanks again - well-scoped, well-tested contributions are very welcome.
