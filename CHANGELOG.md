# CHANGELOG

---

<!-- TOC -->

* [5.2.0](#520)
    * [5.2.0.1](#5201)

<!-- TOC -->

---

## 5.2.0.1

* Fixed a potential SQL injection in AbstractDataDumper
  ([AIKIDO](https://app.aikido.dev/issues/45323423/detail?status=closed))
* Fixed font color in Symfony Profiler
* Fixed Dockerfiles

## 5.2.0

* Removed support for Symfony 5.0 to 5.3
* Added support for PHP 8.4 and 8.5
    * Removed support for PHP 7.2 and 7.3
    * Removed deprecated content introduced in PHP 8.4 and 8.5
* Updated `propel/propel` minimum version to latest beta release (2.0.0-beta4)
