# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.4.1] - 2019-08-25
- #29 Add new configuration for pagination. (dynamic max per page)
  Notice: this is a minor BC break but it should be smooth upgrade for everyone ATM.

## [0.4.0] - 2019-08-06
- #25 Improve compatibility of CRUD stuff, but breaks the compatibility

## [0.3.3] - 2019-08-01
- #22 Add possibility to define a custom data normalizer for ApiType 

## [0.3.2] - 2019-07-05
### Changed
- #19 Documentation is now using scheme of the application

## [0.3.1] - 2019-06-17
### Fixed
- #17 fixes issue with GetAll controller with no filters
- #16 adds a BC layer for Sf < 4.1 and removes deprecation usage triggers for new versions

## [0.3.0] - 2019-05-12
### Added
- Some tooling about filters that are now a nice thing to use
- Some documentation about crud and filters

### Changed
- BC break on FilterInterface
- BC break on DataStore interface

## [0.2.1] - 2019-04-18
### Added
- Api errors, now returns json

## [0.2.0] - 2019-03-20
### Added
- New dependency to pager fanta (experimental, could be removed)
- BC break on CRUD data provider (it now support complete CRUD)
- Add new CRUD controllers
- Add new responses and pagination management
