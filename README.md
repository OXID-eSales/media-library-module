# Media Library Module for OXID eShop

[![Development](https://github.com/OXID-eSales/media-library-module/actions/workflows/trigger.yaml/badge.svg?branch=b-7.6.x)](https://github.com/OXID-eSales/media-library-module/actions/workflows/trigger.yaml)
[![Latest Version](https://img.shields.io/packagist/v/OXID-eSales/media-library-module?logo=composer&label=latest&include_prereleases&color=orange)](https://packagist.org/packages/oxid-esales/media-library-module)
[![PHP Version](https://img.shields.io/packagist/php-v/oxid-esales/media-library-module)](https://github.com/oxid-esales/media-library-module)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_media-library-module&metric=alert_status)](https://sonarcloud.io/dashboard?id=OXID-eSales_media-library-module)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_media-library-module&metric=coverage)](https://sonarcloud.io/dashboard?id=OXID-eSales_media-library-module)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=OXID-eSales_media-library-module&metric=sqale_index)](https://sonarcloud.io/dashboard?id=OXID-eSales_media-library-module)

Module provides basic media files management.

## Compatibility

### Versions
* v5.x is compatible with eShop compilation 7.5.x and higher
* v4.x is compatible with eShop compilation 7.4.x and higher
* v3.0.x is compatible with eShop compilation 7.3.x and higher
* v2.1.x is compatible with eShop compilation 7.2.x and higher
* v2.0.x is compatible with eShop compilation 7.1.x and higher
* v1.0.x is compatible with eShop compilation 7.1.x and higher

### Branches
* b-7.6.x is compatible with shop b-7.6.x branches
* b-7.5.x is compatible with shop b-7.5.x branches
* b-7.4.x is compatible with shop b-7.4.x branches
* b-7.3.x is compatible with shop b-7.3.x branches
* b-7.2.x is compatible with shop b-7.2.x branches
* b-7.1.x is compatible with shop b-7.1.x branches

### Module installation via composer

In order to install the module via composer run one of the following commands in commandline in your shop base directory
(where the shop's composer.json file resides).
* `composer require oxid-esales/media-library-module:^5.1.0`
  to install the released version compatible with OXID eShop v7.5.x
* `composer require oxid-esales/media-library-module:dev-b-7.6.x`
  to install the specific unreleased branch

# Development installation on OXID eShop SDK

The installation instructions below are shown for the current [SDK](https://github.com/OXID-eSales/docker-eshop-sdk)
for shop 7.6. Make sure your system meets the requirements of the SDK.

0. Ensure all docker containers are down to avoid port conflicts

1. Clone the SDK for the new project
```shell
echo MyProject && git clone https://github.com/OXID-eSales/docker-eshop-sdk.git $_ && cd $_
```

2. Clone the repository to the source directory
```shell
git clone --recurse-submodules https://github.com/OXID-eSales/media-library-module.git --branch=b-7.6.x ./source
```

3. Run the recipe to setup the development environment
```shell
./source/recipes/setup-development.sh
```

You should be able to access the shop via
- Frontend http://localhost.local
- Admin Panel: http://localhost.local/admin
    - (credentials: noreply@oxid-esales.com / admin)

### Running the tests and quality tools

Check the "scripts" section in the `composer.json` file for the available commands. Those commands can be executed
by connecting to the php container and running the command from there, example:

```shell
make php
composer tests-coverage
```

Commands can be also triggered directly on the container with docker compose, example:

```shell
docker compose exec -T php composer tests-coverage
```
## Rebuilding the assets

The build requires Node 22.12 or newer. The odd Node releases 21 and 23 are not supported by
the toolchain, so use either the 22 line from 22.12 upwards, or Node 24 and newer.

To rebuild the assets, latest node docker container can be used. The one is pulled automatically if you are using the
installation method from the previous section. What is left - connect to the container, install the npm dependencies
and run the assets building process

```shell
make node
npm install
npm run build
```

Alternatively, if you're actively developing and want changes to be applied automatically, you can enable watch mode:

```shell
npm run watch
```

## Migration

After updating the Media Library module, ensure you run the database migrations:

```bash
vendor/bin/oe-eshop-doctrine_migration migrations:migrate ddoemedialibrary
```

## License

OXID Module and Component License, see [LICENSE file](LICENSE).

## Bugs and Issues

If you experience any bugs or issues, please report them in the section **module Media Library** of https://bugs.oxid-esales.com.
