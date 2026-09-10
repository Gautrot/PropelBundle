# PropelBundle

---

[![Github actions Status](https://github.com/gautrot/PropelBundle/actions/workflows/ci.yml/badge.svg?branch=5.2)](https://github.com/gautrot/PropelBundle/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/gautrot/PropelBundle/branch/5.2/graph/badge.svg?token=GsBNYniEtk)](https://codecov.io/gh/gautrot/PropelBundle)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.0-8892BF.svg)](https://php.net/)

This is the official implementation of [Propel](https://www.propelorm.org/) in Symfony. This version is an extended LTS
version for Symfony 5.4 only.

## Installation

### Minimum requirements

- [Symfony](https://symfony.com/releases/6.0): **5.4**
- [PHP](https://www.php.net/releases/8.0/en.php): **8.0**.
    - If you are using PHP 7, you **must** upgrade to PHP 8.0 at least.
- [Propel](https://github.com/propelorm/Propel2): **2.0.0**

### Composer

```shell
composer require propel/propel "2.0.0-beta4"
composer require gautrot/propel-bundle "~5.2" # (or newer tag/release)
# or
composer require gautrot/propel-bundle "5.2.x-dev"
```

#### composer.json

Stable release:

```json
{
  "require": {
    "propel/propel": "2.0.0-beta4",
    "gautrot/propel-bundle": "~5.2"
  }
}
```

Nightly release:

```json
{
  "require": {
    "propel/propel": "2.0.0-beta4",
    "gautrot/propel-bundle": "5.2.x-dev"
  }
}
```

## Features

* Generation of model classes based on an XML schema only, placed under `BundleName/Resources/*schema.xml`;
* Insertion of SQL statements;
* Runtime autoloading of Propel and generated classes;
* Propel runtime initialization through the XML configuration;
* [Propel Migrations](https://propelorm.org/documentation/09-migrations.html);
* Reverse engineering
  from [existing database](https://propelorm.org/documentation/cookbook/working-with-existing-databases.html);
* Integration to the Symfony Profiler;
* Load SQL, YAML and XML fixtures;
* Create/drop databases;
* Integration with the Form component;
* Integration with the Security component;
* Propel ParamConverter can be used
  with [Sensio Framework Extra Bundle](https://github.com/sensiolabs/SensioFrameworkExtraBundle).
    * **Warning!**: This bundle has become deprecated as of Symfony **6.2**. If you are still using it, you must convert
      them to Symfony's [Attributes Overview](https://symfony.com/doc/current/reference/attributes.html).

## New to version 5.2

* Removed support for Symfony 5.0 to 5.3
* Added support for PHP 8.4 and 8.5
    * Removed support for PHP 7.2 to 7.4
    * Removed deprecated content introduced in PHP 8.4 and 8.5
* Updated `propel/propel` minimum version to latest beta release (2.0.0-beta4)

You can also read the changelogs [here](CHANGELOG.md).

## And more...

You can read and learn Propel 2 from their official documentation [here](https://propelorm.org/documentation/). For
licenses, see: [LICENSE](Resources/meta/LICENSE). If you want more details about PropelBundle, [go here](misc/MORE.md).
