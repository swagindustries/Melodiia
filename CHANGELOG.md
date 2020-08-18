# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Support of more kind of FormError
- Removed the requirement to have CSRF protection enabled #25
- Behat test suite. Melodiia is officially battle tested!
- Add Collection model to render easily standard output with collections #12

### Changed
- New PHP minimum requirement is PHP 7.3
- Huge BC Break on namespaces. You need to rename all classes used to SwagIndustries instead of Biig
- BC Break: the form option `customDataMapper` is now `melodiiaDataMapper`
- BC Break: on CRUD classes. It's big changes time. So we made the security optional for crud controllers, this has a
  consequence on their constructor
- BC Break: `CrudableModelInterface` is now `MelodiiaModel`
- The ApiType now uses a custom request handler. It makes handling api request easier and removes some code
  in controllers. You should now use `handleRequest($request)` on your form. Here is a list of other changes:
    - An api form cannot be "not submitted" anymore after a call to `handleRequest`
    - In case the input data is invalid, a form error is add to the form
    - There is a new optional option on the ApiType: `clear_missing` but its value is automatically guessed by default
      to follow HTTP verbs.
- Removal of the dependency to zend json (thanks to php 7.3 upgrade)

## [0.6.0] 2020-06-01
### Added
- CRUD controllers now use an id resolver (feel free to redefine it)
- Melodiia now provides a convenient way to manage API collections with forms
- Complete support of Sf 5.0 

### Changed
- DataStore now uses a standard flush

### Removed
- Support for Sf 4.2

## [0.5.1] - 2020-03-31
### Fixed
- Update CRUD Controller was not working as expected anymore, this has been fixed, update asap

## [0.5.0] - 2020-03-28
### Added
- Full compatibility to Symfony 5.x

### Changed
- the crud controllers now allow partial data on patch by default

### Fixed
- Fix issue with subresources persistence
- FormError are more consistent
- CRUD controllers now output 400 error on malformed json instead or 500 error

## [0.4.4] - 2019-11-05
- Add possibility for Create/Update controller to configure the `clearMissing` parameter of Symfony `Form->submit()` method.

## [0.4.3] - 2019-10-30
- Add new `ErrorResponse` class which allow serializing custom errors

## [0.4.2] - 2019-10-17
- #33 add delete controller for CRUD
- Add new `remove` method to DoctrineDataStore
- Refacto check model class inside crud controllers

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
