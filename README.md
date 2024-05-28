# Estimate import

Estimate import plugin for Leantime.

Allows .csv files to be imported into the system and tickets to be created from the imported data, automating this process.

## Installation

Download a release from
<https://github.com/ITK-Leantime/leantime-estimateimport/releases> and extract into
your Leantime plugins folder, e.g.

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
git clone https://github.com/ITK-Leantime/leantime-estimateimport app/Plugins/EstimateImport
```

Install and enable the plugin:

``` shell
bin/leantime plugin:install leantime/estimateimport --no-interaction
bin/leantime plugin:enable leantime/estimateimport --no-interaction
```

### Coding standards

``` shell
docker run --tty --interactive --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer install
docker run --tty --interactive --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer coding-standards-apply
docker run --tty --interactive --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer coding-standards-check
```

```shell
docker run --tty --interactive --rm --volume ${PWD}:/app node:20 yarn --cwd /app install
docker run --tty --interactive --rm --volume ${PWD}:/app node:20 yarn --cwd /app coding-standards-apply
docker run --tty --interactive --rm --volume ${PWD}:/app node:20 yarn --cwd /app coding-standards-check
```

### Code analysis

```shell
docker run --tty --interactive --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer install
docker run --tty --interactive --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer code-analysis
```

## Release

We use GitHub Actions to build releases (cf. `.github/workflows/release.yaml`).
To test building a release, run

```shell
# https://github.com/catthehacker/docker_images/pkgs/container/ubuntu#images-available
# Note: The ghcr.io/catthehacker/ubuntu:full-latest image is HUGE!
docker run --rm --volume ${PWD}:/app --workdir /app ghcr.io/catthehacker/ubuntu:full-latest bin/create-release test
# Show release content
tar tvf leantime-plugin-*-test.tar.gz
```
