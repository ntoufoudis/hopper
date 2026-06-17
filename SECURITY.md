# Security Policy

## Supported versions

Security fixes are provided for the latest `1.x` release line.

| Version   | Supported          |
|-----------|--------------------|
| 1.0.x     | :white_check_mark: |
| < 1.0     | :x: (pre-release)  |

When a fix ships, it is released as a patch on the current minor (e.g. `1.0.1`). Please keep
up to date with the latest patch release.

## Reporting a vulnerability

**Please do not report security vulnerabilities through public GitHub issues, pull requests,
or discussions.**

Instead, use one of the private channels below:

1. **GitHub private vulnerability reporting (preferred).** Go to the repository's **Security**
   tab -> **Report a vulnerability**. This opens a private advisory visible only to the
   maintainers.
2. **Email.** [info@ntoufoudis.com](mailto:info@ntoufoudis.com)

Please include as much of the following as you can:

- A description of the issue and the impact you believe it has.
- The affected version(s) and environment (PHP / Laravel versions).
- Steps to reproduce, or a minimal proof of concept.
- Any suggested remediation, if you have one.

## What to expect

This is an open-source project maintained on a best-effort basis. The following are targets,
not contractual guarantees:

- **Acknowledgement** of your report within **3 business days**.
- An initial **assessment** (severity, affected versions, whether it is in scope) within
  **10 business days**.
- For confirmed issues, a **fix and coordinated disclosure** as quickly as is practical,
  typically within **90 days**, sooner for high-severity issues.

We follow coordinated disclosure: please give us a reasonable window to release a fix before
any public disclosure. With your permission, we are happy to credit you in the release notes
and the published advisory.

## Scope

**In scope:** vulnerabilities in the Hopper package code itself - for example, mishandling of
imported (untrusted) data, unsafe persistence, injection in generated output, or unsafe
defaults.

**Out of scope:**

- Vulnerabilities in applications that *use* Hopper (your validation rules, your models, your
  storage configuration).
- Vulnerabilities in dependencies (e.g. `maatwebsite/excel`, PhpSpreadsheet, the Laravel
  framework). Please report those to the respective projects; we will bump constraints once a
  fixed release is available.

## Security considerations when using Hopper

Hopper exists to ingest **external, untrusted data**, so a few usage notes are relevant to
security:

- **Validate every import.** Define `rules()` on your `ImportDefinition`; invalid rows are
  diverted to the failed-row store rather than persisted.
- **Persistence is allow-listed.** Only the fields declared by your import definition (default:
  the model's `$fillable`) are written to the target model. Do not widen `$fillable` to include
  columns an importer should never set (e.g. `is_admin`, `role`, foreign keys you don't intend
  to accept from a file).
- **Failed-row exports are injection-safe.** The CSV exporter neutralises spreadsheet formula
  injection (`= + - @`) in exported cells. If you build your own export of imported data,
  apply the same care.
- **Queued imports read files from storage.** When staging runs on a queue, point sources at
  durable, access-controlled storage rather than world-readable or temporary locations.

If you find a case where any of these protections can be bypassed, please report it via the
private channels above.
