# Changelog

All notable changes to `hopper` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/).

Semantic versioning applies from **v1.0.0** onwards. Pre-1.0 releases may contain
breaking changes between any two versions - see upgrade notes per version.

## [Unreleased]

### Added

- Package manifest `composer.json` for `ntoufoudis/hopper` (MIT): PHP `^8.3`, Laravel `^13` dependencies, `maatwebsite/excel`, dev tooling (Testbench, Pest, Larastan, Pint), PSR-4 autoloading, and Laravel package auto-discovery.
- `HopperServiceProvider` registering the publishable `config/hopper.php` stub (`queue_connection`, `default_chunk_size`, `audit.driver`, `tables` map) and loading the (empty) package migrations' directory.
- Quality tooling: `phpstan.neon` (Larastan, level 10, analyses `src`) and `pint.json` (Laravel preset).
- Pest test harness (Testbench `TestCase` with the provider registered, `tests/Pest.php`) and a smoke test asserting the provider boots, config merges, and the config file publishes.
- GitHub Actions CI (`.github/workflows/ci.yml`) running Pint (`--test`), PHPStan, and Pest across a PHP × Laravel matrix.
- Migrations for `hopper_runs` (status, `import_definition`, source fingerprint, nullable actor, total/processed/inserted/updated/skipped/failed counts, timings) and `hopper_staging` (run_id, source_row_number, unique `row_hash`, JSON payload, resolution verdict, resolved_key, `committed_at`).
- `Source` and `Resolver` contracts, the `Resolution` value object with the `ResolutionType` (Insert/Update/Skip) enum, and the `RunStatus` lifecycle enum (Pending → Staging → Ready → Importing → Completed, plus Failed/PartiallyCompleted).
- `ImportRun` model (status cast, `progress()` returning processed/total/percentage) and `StagingRow` model (payload-array and resolution-enum casts), both bound to the configured `hopper_*` table names.
- Minimal `ImportDefinition` (model/resolver/chunkSize) and `InsertOnlyResolver` (every row resolves to Insert).
- `CsvSource`: streams a CSV through `maatwebsite/excel`'s chunk reader, bridged to a pull-based generator via a PHP Fiber; exposes ordered headers, header-keyed rows numbered from 1, and a content-based fingerprint.
- `StagingWriter`: streams a source in chunks, resolves each row, and upserts it into `hopper_staging` keyed on `row_hash` (`hash(fingerprint + rowNumber)`), making re-staging idempotent; records the run's total row count.
- `Hopper` facade with `HopperManager::define()`, the `PendingImport` builder (`from()` / `stage()`), and the idempotent `StageChunk` job that drives `StagingWriter` and transitions a run Pending → Staging → Ready.
