# Laravel Package Skeleton

Create your own laravel package with this skeleton.

# requirements

For Laravel 10

* PHP 8.1

For Laravel 11 and above

* PHP 8.2 or higher

## Installation

```bash
composer create-project danidoble/laravel-package-skeleton --prefer-dist --stability=dev
```

## Configuration

Configure composer.json with your package information.

* Change `danidoble/laravel-package-skeleton` with your package name.
* Change `Danidoble\LaravelPackageSkeleton` with your package namespace.
* Change `Danidoble\LaravelPackageSkeleton\LaravelPackageSkeletonServiceProvider` with your package service provider.
* Change `config/package.php` with your package configuration file.
* Change `database/migrations/*.php` with your package migrations.
* Change `resources/views/*.blade.php` with your package views.
* Change `routes/web.php` with your package routes.

## Usage

If you want test the basic route run:

```bash
./vendor/bin/testbench serve
```

then open your browser and go to `http://127.0.0.1:8000/package-route`.

to check the complete list of commands run:

```bash
./vendor/bin/testbench list
```
