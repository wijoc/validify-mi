# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

### Changed

### Fixed

---

## [0.1.0] - 2024-09-03

## [0.1.1] - 2024-10-18

## [1.0.1] - 2025-02-11

### Added

- First stable release 🎉

## [1.0.2] - 2025-02-11

## [1.1.0] - 2025-02-13

## [1.1.1] - 2025-02-18

## [1.2.0] - 2025-02-20

## [1.2.1] - 2025-02-20

## [1.3.0] - 2025-06-18

### Added

- Query Builder :
  - add where not in subquery;
  - add where regexp;
  - add '<' in where;
  - add join subquery handle;
- Add flow: set sanitized as data after sanitizing process
- Add CHANGELOG.md

### Fixed

- Query Builder :
  - fix is not null where query;
- Validator, required and requiredif rules to handle boolean value
- Remove trailing whitespace

### Changed

- Query Builder :
  - adjust orderby method;
  - adjust update method;
  - adjust delete method;
  - adjust build upsert query;
  - adjust build update query;
  - adjust prepare query parameter method
- Adjust variable naming
