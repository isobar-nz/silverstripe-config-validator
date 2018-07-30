# SilverStripe Config Validator

A module for validating SilverStripe configuration at build time.

## Installation

Require via composer and run `/dev/build?flush=all`. This is a zero-configuration installation.

`composer require littlegiant/silverstripe-config-validator`

## Validating configuration

Configuration is validated by implementing the `OwnConfigValidator` or `ClassConfigValidator` interfaces.

If class `MyClass` validates configuration of itself, it should implement `OwnConfigValidator`.

If class `MyClass` validates configuration of one or more other classes (e.g. `MyOtherClass`), it should implement 
`ClassConfigValidator` and its `getConfigValidatedClasses()` method should return an array containing `MyOtherClass::class`.
This is a good way to incrementally add validation to configuration for vendor-provided (e.g. core / module) classes without 
having to submit changes to those packages.

The implementation of these interfaces is **not** exclusive (i.e. a class can implement both interfaces and it will validate 
itself and the other classes.)

Configuration is validated by adding any errors via `ClassConfigValidationResult::addError()`. If any config validation fails, 
`/dev/build` will abort before database building, displaying all config validation errors to be corrected.
