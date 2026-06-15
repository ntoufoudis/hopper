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
- `Source` and `Resolver` contracts, the `Resolution` value object with the `ResolutionType` (Insert/Update/Skip) enum, and the `RunStatus` lifecycle enum (Pending -> Staging -> Ready -> Importing -> Completed, plus Failed/PartiallyCompleted).
- `ImportRun` model (status cast, `progress()` returning processed/total/percentage) and `StagingRow` model (payload-array and resolution-enum casts), both bound to the configured `hopper_*` table names.
- Minimal `ImportDefinition` (model/resolver/chunkSize) and `InsertOnlyResolver` (every row resolves to Insert).
- `CsvSource`: streams a CSV through `maatwebsite/excel`'s chunk reader, bridged to a pull-based generator via a PHP Fiber; exposes ordered headers, header-keyed rows numbered from 1, and a content-based fingerprint.
- `StagingWriter`: streams a source in chunks, resolves each row, and upserts it into `hopper_staging` keyed on `row_hash` (`hash(fingerprint + rowNumber)`), making re-staging idempotent; records the run's total row count.
- `Hopper` facade with `HopperManager::define()`, the `PendingImport` builder (`from()` / `stage()`), and the idempotent `StageChunk` job that drives `StagingWriter` and transitions a run Pending -> Staging -> Ready.
- `Committer` (chunked, per-chunk-transactional replay of uncommitted staging rows into the target, stamping `committed_at` and incrementing run counters), the `CommitChunk` job, and `ImportRun::commit()` which transitions a run to Importing -> Completed.
- GATE 1b checkpoint: end-to-end happy-path CSV import (stage -> commit) verified with correct run counts.
- Hardening test coverage for M1: idempotent re-staging, resumable commit with no double-inserts, and progress math - completing the resolve-once / replay-later core engine for CSV.
- Mapping contracts and value objects: the `MappingStrategy` contract, the readonly `MappingSuggestion` VO (field/confidence/strategy), and the iterable `ColumnMap` (source-header -> target-field).
- Mapping strategies - `ExactMatch` (case-insensitive header/field equality), `AliasMatch` (config-driven synonym dictionary), and `FuzzyMatch` (normalised Levenshtein with a configurable threshold) - plus the `AiMatch` premium seam (returns `null`, not wired into the default chain) and a `hopper.mapping` config block (aliases + `fuzzy_threshold`).
- `Mapper` service: runs registered strategies in priority order (first non-null wins), assembling a `ColumnMap` and exposing per-header `MappingSuggestion` confidence; bound with the default Exact->Alias->Fuzzy chain (AiMatch excluded).
- `hopper_mapping_templates` migration and `MappingTemplate` model (unique `source_signature` + `import_definition`, `column_map` json), plus template persistence on `Mapper` - `autoMap()` reuses a saved template before falling back to strategies and `saveTemplate()` records a confirmed map.
- Column-mapping wiring through staging: `ImportDefinition::fields()` (defaults to the model's fillable), `StagingWriter` remaps each row's headers to target fields when a `ColumnMap` is present, `StageChunk` carries the map, and the `Hopper` builder gains `->map()` (explicit) and `->autoMap()` (template-then-strategies, persisting new templates).
- `make:import` Artisan generator that scaffolds an `ImportDefinition` stub (`model()`, `rules()`, `pipes()`, `resolver()`) under the application's `App\Hopper` namespace.
- Integration coverage for M2: `autoMap()` stages a header-mismatched CSV into the correct target fields (and commits correctly), and a second import of the same source signature reuses the persisted template with zero re-mapping.
- The `Pipe` transformation contract (`handle($row, Closure $next)`, Illuminate `Pipeline`-compatible) and the `RowRejected` exception a pipe throws to drop a row with a reason destined for the failed-row report.
- `PipeRunner`: drives a definition's `pipes()` through Laravel's `Pipeline` (class-string or instance pipes), returning the transformed row and letting a `RowRejected` propagate to the caller.
- `ImportDefinition::rules()` (per-row Laravel validation rules) and `::pipes()` (ordered transformation pipes) defaults, both empty by default; the `make:import` stub now describes them as live behaviour.
- `hopper_failed_rows` migration (run_id, source_row_number, unique `row_hash`, JSON payload, reason) and the `FailedRow` model (payload-array cast, configured table name) - the diversion sink for rejected and invalid rows.
- The abstract `DatabaseResolver` base: batch-aware matching by a single field - `useModel()` + `prime()` issue one keyed `whereIn` per chunk and cache matches in memory, so `resolve()` never queries per row (the public single-row `Resolver` contract is unchanged).

### Fixed

- Staging on the `sync` queue no longer throws `FiberError: Cannot switch fibers in current execution context` on PHP 8.3. `PendingImport::stage()` now dispatches `StageChunk` via the bus instead of `StageChunk::dispatch()`, so the job runs in the normal call stack rather than inside `PendingDispatch::__destruct()` - PHP < 8.4 forbids switching `CsvSource`'s Fiber from within a destructor. Queue behaviour is unchanged on real connections.
