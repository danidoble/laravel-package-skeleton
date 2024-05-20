# Laravel Package Skeleton

Create your own laravel package with this skeleton.

# requirements

For Laravel 10

* PHP 8.1

For Laravel 11 and above

* PHP 8.2 or higher

## Installation

```bash
composer create-project danidoble/laravel-package-skeleton --prefer-dist
```

### Note for Windows

Windows not support laravel prompts, so you need to create project, and when it finishes, you need to run the following
commands:

Enter to the project folder

```bash
cd laravel-package-skeleton
```

Execute the configuration script

```bash
php configure.php
```

update composer

```bash
composer update
```

or in one line

```bash
cd laravel-package-skeleton && php configure.php && composer update
```

## Usage

If you want test the basic route run:

```bash
./vendor/bin/testbench serve
```

then open your browser and go to `http://127.0.0.1:8000/`

### routes

If you added web routes in your package, you can test it by going to

`http://127.0.0.1:8000/package-route`.

Note: package-route is the route pre-defined in the package,
when you configure the package this route change to `your-name-package-route`

Ex. my package is Testing so my route will be `testing-route`, so you need to open `http://127.0.0.1:8000/testing-route`
instead of previous route.

to check the complete list of commands run:

```bash
./vendor/bin/testbench list
```

for more information about testing with orchestra/testbench visit
[Orchestra Testbench](https://packages.tools/testbench)
