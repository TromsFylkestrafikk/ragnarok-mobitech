# Change Log

## [Unreleased]

### Added
- Documentation of sink (`SINK.md`).

### Fixed
- Import failed for transactions with `tour_id` exceeding the INTEGER
  range. To prevent future problems, all INTEGER ID columns were
  widened to BIGINT and CHAR columns set to CHAR(255).

### Removed
- Removed lots of unused db columns.

## [0.1.0] – 2024-04-18
### Added
- Implemented sink API for stage 1: Raw retrieval (fetch)
- Implemented sink API for stage 2: DB import and removal.
- Implemented sink API for schemas and their descriptions.
- Implemented sink API for reverse chunk ID lookup.
