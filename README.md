# Entity repositories for humans

An example to demonstrate a simple (yet flexible) way of creating entity repositories.
We use (a slightly more complicated version of) this pattern at [Slope](https://slope.it), in production code.

Ref: <todo insert article url>

## Requirements

- Docker for desktop
- PHP 8.1 or above installed on your local machine

## Setup

```sh
./$ composer install
./$ source .env && docker compose up -d
./$ vendor/bin/doctrine orm:schema-tool:update --force
```

## Run tests

```
./$ vendor/bin/phpunit
```
