# Object Store API
![Tests](https://github.com/IonBazan/object-store/workflows/Tests/badge.svg)
[![codecov](https://codecov.io/gh/IonBazan/object-store/branch/master/graph/badge.svg)](https://codecov.io/gh/IonBazan/object-store)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FIonBazan%2Fobject-store%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/IonBazan/object-store/master)

This application provides a simple API for storing your data in your key-value store with history.

# Installation

Prerequisites:
 - PHP 7.4+ with `pdo_mysql` and `redis` extensions
 - Composer
 - MariaDB or MySQL server
 - Redis server

```shell
composer install # Install dependencies
bin/console doctrine:migrations:migrate -n
```

Note that you may need to update your DB and Redis credentials. 
Simply put `DATABASE_URL=mysql://admin:admin@127.0.0.1/object_store` in `.env.local` and put your credentials there.

# Usage

Application offers both Web and CLI interfaces.

## Web
The easiest way to run the application is by installing [Symfony CLI](https://symfony.com/doc/current/setup/symfony_server.html) and run:

```bash
symfony serve
```

Your app should be available at http://localhost:8000.

Use provided Swagger UI documentation to browse the API.

## CLI

Application offers two CLI commands:
```bash
bin/console app:object-store:store <key> <value> # Stores the value in object store
bin/console app:object-store:get <key> [-t TIMESTAMP] # Gets the value from object store at given time
```

Please note that `value` must me JSON-encoded and escaped for your terminal.
For example, if you want to store a string value: `test-value` at key `test-key`, you have to use following command:
```bash
bin/console app:object-store:store test-key \"test-value\"
```

This is to enable you to store complex data as a value.

# Storage adapters

Application allows you to easily swap between different implementations of storage drivers.
You can easily create one by implementing `App\Infrastructure\ObjectStorage\ObjectStorageAdapter` interface.

Currently implemented drivers are:
 - `DoctrineObjectStorageAdapter` - stores data in Database
 - `RedisObjectStorageAdapter` - stores data in Redis
 
## Choosing the adapter

To change the adapter, simply switch `App\Infrastructure\ObjectStorage\ObjectStorageAdapter` service definition in `config/services.yaml`:
```yaml
services:
# ...
    App\Infrastructure\ObjectStorage\ObjectStorageAdapter:
        alias: App\Infrastructure\ObjectStorage\DoctrineObjectStorageAdapter
#        alias: App\Infrastructure\ObjectStorage\RedisObjectStorageAdapter # Use this to switch to Redis driver
```
 
## Doctrine
 
`DoctrineObjectStorageAdapter` uses [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/index.html) to store data as `ObjectEntry` entities.

The `ObjectEntry` entity is using `VARCHAR(255)` column type.

 - `utf8mb4` character set for full Unicode support so you can use keys like 🧅 or 😃. 
 - `utf8mb4_bin` collation ensures binary comparison of keys so `KEY` !== `key` and `key` !== `kęy`.
 - Timestamp is stored as `TIMESTAMP` in UTC

### Limitations

Because of MySQL index length limitations, an error may occur when storing bigger keys. 

## Redis

`RedisObjectStorageAdapter` uses Redis as storage using sorted sets.
Internally, the sorted set keeps only the change reference as:

| KEY | VALUE                 | SCORE     |
|-----|-----------------------|-----------|
| key | SHA512(key):timestamp | timestamp |

Then, the actual value is stored in a hashmap as:

| KEY                   | VALUE |
|-----------------------|-------|
| SHA512(key):timestamp | value |

Because sorted sets require values to be unique, this approach allows `value1 -> value2 -> value1` transitions to be properly stored.

### Limitations

Since the implementation uses SHA512 as part of the key, some conflicts may occur.

# Tests

All components are covered by either unit or feature tests.

Running tests is as simple as configuring `.env.test.local` variables to match your DB credentials and running:
```bash
bin/phpunit
```

## Mutation tests

To make sure your tests actually have necessary assertions, CI is running mutation tests using [Infection](https://infection.github.io/).

Make sure you have Xdebug or PCOV extension enabled as it is required to run:
```bash
vendor/bin/infection
```

# Deployment

This application is being automatically tested using GitHub Actions and deployed on Heroku.
