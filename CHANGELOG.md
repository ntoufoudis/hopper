# Changelog

All notable changes to `hopper` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/).

Semantic versioning applies from **v1.0.0** onwards. Pre-1.0 releases may contain
breaking changes between any two versions - see upgrade notes per version.

## [Unreleased]

### Added

- Package manifest `composer.json` for `ntoufoudis/hopper` (MIT): PHP `^8.3`, Laravel `^13` dependencies, `maatwebsite/excel`, dev tooling (Testbench, Pest, Larastan, Pint), PSR-4 autoloading, and Laravel package auto-discovery.
- Package manifest `composer.json` for `ntoufoudis/hopper` (MIT): PHP `^8.3`, Laravel `^13` dependencies, `maatwebsite/excel`, dev tooling (Testbench, Pest, Larastan, Pint), PSR-4 autoloading, and Laravel package auto-discovery.
- `HopperServiceProvider` registering the publishable `config/hopper.php` stub (`queue_connection`, `default_chunk_size`, `audit.driver`, `tables` map) and loading the (empty) package migrations' directory.
