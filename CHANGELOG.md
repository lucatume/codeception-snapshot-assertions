# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2023-10-14

### Added

- support a `snapshot`  configuration parameter in the `codeception.yml`, or `codeception.dist.yml`, file to set the
  snapshot `version` and `refresh` values; see the README.md file for details.

## [1.1.0] - 2023-06-28

### Changed

- Show the snapshot diff by default, deactivate setting the `showSnapshotDiff` property of a `SnapshotAssertions` trait
  user to `false`.
- Make the `SnapshotAssertions` trait `public` to allow its use in Actor classes.

### Fixed

- Ensure multiple snapshot assertions in the context of the same test method do not overwrite each other.

## [1.0.0] - 2023-06-16

### Changed

- Require PHP 8.0, Codeception 5.0.

## [0.4.0] - 2023-10-14

### Breaking change

- Codeception support reduced to version 4.0; versions 2 and 3 are no longer supported.

### Added

- support a `snapshot`  configuration parameter in the `codeception.yml`, or `codeception.dist.yml`, file to set the
  snapshot `version` and `refresh` values; see the README.md file for details.

## [0.3.0] - 2023-09-08

### Changed

- show diff on failure by default

## [0.2.4] - 2020-02-05

### Added

- data visitor support in all assertions to allow pre-processing of the expected and current values before assertions

## [0.2.3] - 2020-02-03

### Added

- data visitor support in all assertions to allow pre-processing of the expected and current values before assertions

## [0.2.2] - 2019-09-21

### Added

- directory snapshot

## [0.2.1] - 2019-05-24

### Fixed

- snapshot regeneration when running tests in debug mode

## [0.2.0] - 2019-05-24

### Added

- code snapshot assertions class and trait methods

## [0.1.0] - 2019-05-07

### Added

- this changelog file
- the first version of the package and README.md

[0.1.0]: https://github.com/lucatume/codeception-snapshot-assertions/releases/tag/0.1.0

[0.2.0]: https://github.com/lucatume/codeception-snapshot-assertions/compare/0.1.0...0.2.0

[0.2.1]: https://github.com/lucatume/codeception-snapshot-assertions/compare/0.2.0...0.2.1

[0.2.2]: https://github.com/lucatume/codeception-snapshot-assertions/compare/0.2.1...0.2.2

[0.2.3]: https://github.com/lucatume/codeception-snapshot-assertions/compare/0.2.2...0.2.3

[0.2.4]: https://github.com/lucatume/codeception-snapshot-assertions/compare/0.2.3...0.2.4

[0.3.0]: https://github.com/lucatume/codeception-snapshot-assertions/compare/0.2.4...0.3.0

[0.4.0]: https://github.com/lucatume/codeception-snapshot-assertions/compare/0.3.0...0.4.0

[1.0.0]: https://github.com/lucatume/codeception-snapshot-assertions/compare/0.4.0...1.0.0

[1.1.0]: https://github.com/lucatume/codeception-snapshot-assertions/compare/1.0.0...1.1.0

[1.2.0]: https://github.com/lucatume/codeception-snapshot-assertions/compare/1.1.0...1.2.0

[Unreleased]: https://github.com/lucatume/codeception-snapshot-assertions/compare/0.2.4...HEAD

