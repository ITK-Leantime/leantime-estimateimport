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

Run composer install

```shell name=development-install
docker run --interactive --rm --volume ${PWD}:/app itkdev/php8.3-fpm:latest composer install
```

### Composer normalize

```shell name=composer-normalize
docker run --rm --volume ${PWD}:/app itkdev/php8.3-fpm:latest composer normalize
```

### Coding standards

#### Check and apply with phpcs

```shell name=check-coding-standards
docker run --interactive --rm --volume ${PWD}:/app itkdev/php8.3-fpm:latest composer coding-standards-check
```

```shell name=apply-coding-standards
docker run --interactive --rm --volume ${PWD}:/app itkdev/php8.3-fpm:latest composer coding-standards-apply
```

#### Check and apply markdownlint

```shell name=markdown-check
docker run --rm --volume $PWD:/md peterdavehello/markdownlint markdownlint --ignore vendor --ignore LICENSE.md '**/*.md'
```

```shell name=markdown-apply
docker run --rm --volume $PWD:/md peterdavehello/markdownlint markdownlint --ignore vendor --ignore LICENSE.md '**/*.md' --fix
```

#### Check with shellcheck

```shell name=shell-check
docker run --rm --volume "$PWD:/app" --workdir /app peterdavehello/shellcheck shellcheck bin/create-release
docker run --rm --volume "$PWD:/app" --workdir /app peterdavehello/shellcheck shellcheck bin/deploy
docker run --rm --volume "$PWD:/app" --workdir /app peterdavehello/shellcheck shellcheck bin/local.create-release
```

### Code analysis

```shell name=code-analysis
# This analysis takes a bit more than the default allocated ram.
docker run --interactive --rm --volume ${PWD}:/app --env PHP_MEMORY_LIMIT=256M itkdev/php8.3-fpm:latest composer code-analysis
```

## Test release build

```shell name=test-create-release
docker compose build && docker compose run --rm php bin/create-release dev-test
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
