# Estimate import

Estimate import plugin for Leantime

## Installation

Download a release from
<https://github.com/ITK-Leantime/leantime-estimateimport/releases> and extract into
your Leantime plugins folder, e.g.

``` shell
# curl --silent --location https://github.com/ITK-Leantime/leantime-dataexport/releases/download/0.0.2/leantime-dataexport-0.0.2.tar.gz | tar xv
```

Install and enable the plugin:

``` shell
bin/leantime plugin:install leantime/estimateimport --no-interaction
bin/leantime plugin:enable leantime/estimateimport --no-interaction
```

## Usage

N/A

## Development

Clone this repository into your Leantime plugins folder:

``` shell
git clone https://github.com/ITK-Leantime/leantime-estimateimport app/Plugins/DataExport
```

Install plugin dependencies:

``` shell
cd app/Plugins/EstimateImport
docker run --tty --interactive --rm --env COMPOSER=composer-plugin.json --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer install --no-dev
```

Install and enable the plugin:

``` shell
bin/leantime plugin:install leantime/dataexport --no-interaction
bin/leantime plugin:enable leantime/dataexport --no-interaction
```

### Coding standards

``` shell
docker run --tty --interactive --rm --env COMPOSER=composer-plugin.json --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer install
docker run --tty --interactive --rm --env COMPOSER=composer-plugin.json --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer coding-standards-check
docker run --tty --interactive --rm --env COMPOSER=composer-plugin.json --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer coding-standards-apply
```

```shell
docker run --tty --interactive --rm --volume ${PWD}:/app node:20 yarn --cwd /app install
docker run --tty --interactive --rm --volume ${PWD}:/app node:20 yarn --cwd /app coding-standards-check
```