# PropelBundle

---

[![Github actions Status](https://github.com/gautrot/PropelBundle/actions/workflows/ci.yml/badge.svg?branch=6.1)](https://github.com/gautrot/PropelBundle/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/gautrot/PropelBundle/branch/6.1/graph/badge.svg?token=GsBNYniEtk)](https://codecov.io/gh/gautrot/PropelBundle)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)

This is the official implementation of [Propel](https://www.propelorm.org/) in Symfony. This version is a LTS support of
Symfony 6.4 only.

## Installation

### Minimum requirements

- [Symfony](https://symfony.com/releases/6.0): **6.4**
- [PHP](https://www.php.net/releases/8.0/en.php): **8.1**
- [Propel](https://github.com/propelorm/Propel2): **2.0.0**

### Composer

```shell
composer require propel/propel "~2.0"
composer require gautrot/propel-bundle "~6.1" # (or newer tag/release)
# or
composer require gautrot/propel-bundle "6.1.x-dev"
```

#### composer.json

Stable release:

```json
{
  "require": {
    "propel/propel": "~2.0",
    "gautrot/propel-bundle": "~6.1"
  }
}
```

Nightly release:

```json
{
  "require": {
    "propel/propel": "~2.0",
    "gautrot/propel-bundle": "6.1.x-dev"
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
* Integration with the Security component.

## New to version 6.1

* Removed support for Symfony 6.0 to 6.3
* Added support for PHP 8.4 and 8.5
    * Removed support for PHP 8.0
    * Removed deprecated content introduced in PHP 8.4 and 8.5
* Updated `propel/propel` minimum version to its official stable release (2.0.0)

You can also read the changelogs [here](CHANGELOG.md).

## And more...

You can read and learn Propel 2 from their official documentation [here](https://propelorm.org/documentation/). For
licenses, see: [LICENSE](Resources/meta/LICENSE). If you want more details about PropelBundle, [go here](misc/MORE.md).
