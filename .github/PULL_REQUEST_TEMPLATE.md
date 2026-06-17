<!-- Thanks for contributing to Hopper! Please fill this in so reviews go quickly. -->

## What does this PR do?

<!-- A clear description of what changed and why. -->

## Related issue

<!-- e.g. Closes #123. Open an issue first for non-trivial changes. -->

## Type of change

- [ ] Bug fix (non-breaking)
- [ ] New feature (non-breaking)
- [ ] Breaking change (changes the public API or behaviour)
- [ ] Documentation only
- [ ] Internal / chore (no behaviour change)

## Checklist

- [ ] `composer test` passes locally (Pint, PHPStan **level 10**, Pest).
- [ ] I did **not** lower the PHPStan level or add baseline entries; type issues are fixed at the source.
- [ ] I added tests covering the change (a regression test for a bug fix; new tests for new behaviour).
- [ ] The core-guarantee tests still hold (idempotency, resume, commit concurrency, resolver batching, preview/commit parity).
- [ ] I updated the README where usage or behaviour changed.
- [ ] I added an entry to the `[Unreleased]` section of CHANGELOG.md.
- [ ] Commits follow Conventional Commits.

## Breaking changes

<!-- If this is a breaking change, describe what breaks and the migration path. Otherwise, write "None". -->
