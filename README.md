# Object Store API
![Tests](https://github.com/IonBazan/object-store/workflows/Tests/badge.svg)
[![codecov](https://codecov.io/gh/IonBazan/object-store/branch/master/graph/badge.svg)](https://codecov.io/gh/IonBazan/object-store)

This application provides a simple API for storing your data in your key-value store with history.

# Installation

Prerequisites:
 - PHP 7.4+ with `pdo_mysql` extension
 - Composer
 - MariaDB or MySQL server

```shell
composer install # Install dependencies
bin/console doctrine:migrations:migrate -n
```

Note that you may need to update your DB credentials. Simply put `DATABASE_URL=mysql://admin:admin@127.0.0.1/object_store` in `.env.local` and put your credentials there.

# Usage
The easiest way to run the application is by installing [Symfony CLI](https://symfony.com/doc/current/setup/symfony_server.html) and run:

```bash
symfony serve
```

Your app should be available at http://localhost:8000.

Use provided Swagger UI documentation to browse the API.

# Tests

Running tests is as simple as configuring `.env.test.local` variables to match your DB credentials and running:
```bash
bin/phpunit
```

# Deployment

This application is being automatically tested using GitHub Actions and deployed on Heroku.


