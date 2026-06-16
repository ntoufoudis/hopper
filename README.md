# Hopper

A headless, framework-agnostic import engine for Laravel. Hopper owns mapping
persistence, preview/diff, idempotent re-runnable imports, and governed audit.
Parsing is delegated to [Laravel Excel](https://laravel-excel.com)
(`maatwebsite/excel`), so CSV and XLSX/XLS sources work out of the box.

## Requirements

- PHP `^8.3`
- Laravel `^13`

## Installation

```bash
composer require ntoufoudis/hopper
```

The service provider is auto-discovered. Publish the config if you need to
override defaults:

```bash
php artisan vendor:publish --tag=hopper-config
```

Hopper's migrations are loaded automatically by the service provider, so run
your usual migration command to create the `hopper_*` tables:

```bash
php artisan migrate
```

This creates `hopper_runs`, `hopper_staging`, `hopper_mapping_templates`,
`hopper_failed_rows`, and `hopper_audit`. Override any of these names via the
`hopper.tables` config map.

## Quick start

Define an import, point it at a source, auto-map the headers, stage it, preview
the diff, then commit:

```php
use Ntoufoudis\Hopper\Hopper;
use Ntoufoudis\Hopper\Sources\CsvSource;

$run = Hopper::define(CustomerImport::class)
    ->from(CsvSource::make(storage_path('app/imports/customers.csv')))
    ->autoMap()
    ->stage();

$preview = $run->preview();   // exact, free pre-commit counts
// $preview->total, ->valid, ->errors, ->inserts, ->updates, ->skips

$run->commit();               // replays staged rows into the target model
```

`stage()` returns an `ImportRun`; `preview()` reads the persisted verdicts only
(it never re-queries the target), so the preview counts are exactly what
`commit()` will do.

An `ImportDefinition` declares the target model, resolver, fields, validation
rules, and transformation pipes:

```php
use Ntoufoudis\Hopper\Contracts\Resolver;
use Ntoufoudis\Hopper\ImportDefinition;
use Ntoufoudis\Hopper\Resolution\UpsertResolver;

final class CustomerImport extends ImportDefinition
{
    public function model(): string
    {
        return Customer::class;
    }

    public function resolver(): Resolver
    {
        return UpsertResolver::by('email');
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email']];
    }

    public function pipes(): array
    {
        return [TrimWhitespace::class];
    }
}
```

Scaffold one with `php artisan make:import CustomerImport` (generated under your
application's `App\Hopper` namespace).

## Sources

`CsvSource::make($path)` and `ExcelSource::make($path)` both accept a path or an
`Illuminate\Http\UploadedFile` and stream the file in chunks through Laravel
Excel. They expose ordered headers, header-keyed rows numbered from 1, and a
content-based fingerprint used to keep re-staging idempotent.

## Mapping & resolvers

Headers are matched to target fields by a strategy chain - exact, alias
(config-driven synonyms in `hopper.mapping.aliases`), then fuzzy (Levenshtein
above `hopper.mapping.fuzzy_threshold`). `autoMap()` reuses a saved mapping
template for a source signature before falling back to strategies; `map()`
accepts an explicit `ColumnMap`.

Resolvers (in `Ntoufoudis\Hopper\Resolution`) decide each row's verdict
(Insert / Update / Skip):

- `InsertOnlyResolver` - every row inserts (`new InsertOnlyResolver`).
- `UpsertResolver::by($field)` - update on match, else insert.
- `MergeResolver::by($field)` - field-level merge (non-blank incoming wins).
- `CallbackResolver` - closure escape hatch for custom verdicts.

## Validation, pipes & failed rows

Each row runs `map -> transform (pipes) -> validate (rules) -> resolve -> stage`.
Rows rejected by a pipe (`RowRejected`) or failing validation are diverted to
`hopper_failed_rows` with a reason and never staged. Export them as CSV:

```php
use Ntoufoudis\Hopper\Export\FailedRowExporter;

$csv = app(FailedRowExporter::class)->export($run);
```

The exporter renders the union of payload columns plus a final `error` column,
and neutralises spreadsheet formula injection by tab-prefixing any cell that
begins with `=`, `+`, `-`, or `@`.

## Audit

Import lifecycle events (`run.created`, `mapping.resolved`, `row.rejected`,
`preview.generated`, `commit.started` / `commit.completed` / `commit.failed`)
are recorded through the configured `AuditDriver`.

- **`database`** (default) - writes each event to `hopper_audit`.

Select the driver via `hopper.audit.driver` (or `HOPPER_AUDIT_DRIVER`):

```php
'audit' => ['driver' => 'chronicle'],
```

## Configuration

Published to `config/hopper.php`: `queue_connection`, `default_chunk_size`,
`audit.driver`, `mapping.aliases` / `mapping.fuzzy_threshold`, and the
`hopper_*` table-name map.

## Limitations

<!-- TODO(5b): commit re-entry / concurrency guarantees - finalised in Session 5b -->
<!-- TODO(5b): insert/update lifecycle asymmetry - finalised in Session 5b -->
<!-- TODO(5c): queued-source durability requirement - finalised in Session 5c -->

## Versioning

Semantic versioning applies from v1.0.0 onwards.

## License

MIT. See [LICENSE](LICENSE).
