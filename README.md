# PropelBundle

---

[![Github actions Status](https://github.com/gautrot/PropelBundle/actions/workflows/ci.yml/badge.svg?branch=7.1)](https://github.com/gautrot/PropelBundle/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/gautrot/PropelBundle/branch/7.1/graph/badge.svg?token=GsBNYniEtk)](https://codecov.io/gh/gautrot/PropelBundle)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892BF.svg)](https://php.net/)

This is the official implementation of [Propel](https://www.propelorm.org/) in Symfony.

## Installation

### Minimum requirements

- [Symfony](https://symfony.com/releases/6.0): **6.4**
- [PHP](https://www.php.net/releases/8.0/en.php): **8.2**
- [Propel](https://github.com/propelorm/Propel2): **2.0.0**

Additional notes:

- If you're using Symfony 6.4, it must be running with PHP 8.2

### Composer

```shell
composer require propel/propel "^2.0"
composer require gautrot/propel-bundle "7.1.0" # (or newer tag/release)
# or
composer require gautrot/propel-bundle "7.1.x-dev"
```

#### composer.json

Stable release:
```json
{
  "require": {
    "propel/propel": "^2.0",
    "gautrot/propel-bundle": "7.1.0"
  }
}
```

Nightly release:
```json
{
  "require": {
    "propel/propel": "^2.0",
    "gautrot/propel-bundle": "7.1.x-dev"
  }
}
```

## Features

* Generation of model classes based on an XML schema (not YAML) placed under `BundleName/Resources/*schema.xml`;
* Insertion of SQL statements;
* Runtime autoloading of Propel and generated classes;
* Propel runtime initialization through the XML configuration;
* [Propel Migrations](https://propelorm.org/documentation/09-migrations.html);
* Reverse engineering
  from [existing database](https://propelorm.org/documentation/cookbook/working-with-existing-databases.html);
* Integration to the Symfony Profiler;
* Load SQL, YAML and XML fixtures;
* Create/Drop databases;
* Integration with the Form component;
* Integration with the Security component;
* Propel ParamConverter can be used with Sensio Framework Extra Bundle.

## New to version 7.1

* Added support for Symfony 7.4
    * Removed support for Symfony 6.0 to 6.3 and 7.0 to 7.3
* Added support for PHP 8.4 and 8.5
    * Removed support for PHP 8.0 and PHP 8.1
    * Removed deprecated content introduced in PHP 8.4 and later
* Updated `propel/propel` minimum version to its official stable release (2.0.0)

You can also read the changelogs [here](CHANGELOG.md).

## Branching model

Since Propel 2 was officially released on June 23, 2026, we are migrating the branching model of this bundle in advance!

* The `1.0` branch contains Propel **1.6** integration for Symfony **2.0**.
    * The `1.1` branch contains Propel **1.6** integration for Symfony **2.1**.
    * The `1.2` branch contains Propel **1.6** integration for Symfony **2.2**.
* The `2.0` branch contains Propel **2** (branches **below 2.0.0-beta1**) integration for Symfony **2.5 - 2.8**.
* The `3.0` branch contains Propel **2** (branches **below 2.0.0-beta1**) integration for Symfony **2.8 - 3.x**.
* The `5.0` branch contains Propel **2** (branch **2.0.0-beta1**) integration for Symfony **4.x|5.x** and PHP **7.2** -
  **8.0**.
    * The `5.1` branch contains Propel **2** (branch **2.0.0-beta2**) integration for Symfony **4.x|5.x|6.x** and PHP
      **7.4** - **8.1**.
* The `6.0` branch contains Propel **2** (branch **2.0.0-beta2**) integration for Symfony **6.x**. and PHP **8.0.2+**
* The `7.0` branch contains Propel **2** (branches **2.0.0-beta2** to **2.0.0-beta4**) integration for Symfony
  **6.x|7.x**. and PHP **8.0.2+**
    * The `7.1` branch contains Propel **2** integration for Symfony **6.4|7.4**. and PHP **8.2+**

## And more...

You can read and learn Propel 2 from their official documentation [here](https://propelorm.org/documentation/). For
licenses, see: [LICENSE](Resources/meta/LICENSE)
