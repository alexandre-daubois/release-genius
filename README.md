Release Genius
==============

[![PHPUnit](https://github.com/alexandre-daubois/release-genius/actions/workflows/php.yaml/badge.svg)](https://github.com/alexandre-daubois/release-genius/actions/workflows/php.yaml)

![Conventional version screenshot](asset/conv-vers.png)

## Requirements

- PHP 8.2 or higher
- Git

That's it!

## Why this package?

This package is a simple tool to help you manage your changelog and versioning.
It uses the [Conventional Commits](https://www.conventionalcommits.org/) standard
to generate the changelog. This standard is widely used in the open-source
community and is a great way to ensure that your commit messages are
understandable and easy to parse.

Unlike [this package](https://github.com/marcocesarato/php-conventional-changelog), this one is under **the MIT license**. This licence is more permissive than the GPL-3.0 licence. This can be a pain point for some projects, and it is the main motivation for creating Release Genius.

## Installation

```bash
composer require alexandre-daubois/release-genius
```

## Usage

### TL;DR

```bash
# Initialize the package
vendor/bin/release-genius --init

# Upgrade to the next version (major, minor or patch)
# CHANGELOG.md, package.json and composer.json will be updated
vendor/bin/release-genius patch
git push && git push --tags

# Only update the changelog, composer.json and package.json
vendor/bin/release-genius patch --no-commit
```

### Available options

```
Usage:
  ./release-genius [options] [--] [<release type>]

Arguments:
  release type                   The type of release to be generated (major, minor, patch)

Options:
  -f, --path=PATH                The file to write the changelog to [default: "CHANGELOG.md"]
  -m, --mode=MODE                The writing mode to use when writing the changelog to a file, between prepend, append and overwrite [default: "prepend"]
  -i, --init                     Initialize the changelog file and create a new git tag
  -r, --remote=REMOTE            The remote to push the tag to; This is also used to generate URLs in the Changelog (use "none" if you don't use a remote) [default: "origin"]
      --remote-type=REMOTE-TYPE  The type of remote to use; This is used to generate URLs in the Changelog ("github" or "gitlab")
      --skip-vendors             Skip the update of package.json and composer.json
      --no-commit                Do not create a commit and a tag, only update the changelog and vendor files if any
```

### Initialize and upgrade the version

The first time you use the package, you may need to initialize it. This is
required when you don't have any tag in your repository and no changelog file
exists. You can do this by running the following command:

```bash
vendor/bin/release-genius --init
```

This will create a new file called `CHANGELOG.md` in the root of your project.
Also, it will create a new tag. You will be prompted to enter the version number
you want to use. The version number should follow the [Semantic Versioning](https://semver.org/)
specification.

After the initialization, you can start using the package to manage your
changelog and versioning. The package provides a few commands to help you with
that. Imagine your current version is `1.0.0`. You can upgrade the version by
running the following command:

```bash
# Upgrade the version to 1.0.1
vendor/bin/release-genius patch

# Upgrade the version to 1.1.0
vendor/bin/release-genius minor

# Upgrade the version to 2.0.0
vendor/bin/release-genius major
```

This will update the `CHANGELOG.md` file and create a new tag. The tag will **not**
be  pushed to the remote repository. This is something you need to do manually in order
to ensure you're happy with the changes and the new version.

#### Vendors JSON files

If a `package.json` file exists in the root of your project, the version number
will be updated **in this file as well** (if present). The same goes for a `composer.json`
file.

#### Skip the commit and tag creation

If you want to create the tag and commit yourself to be extra careful, you can use the `--no-commit` option. Release Genius will only update the changelog and the vendor JSON files.

```bash
vendor/bin/release-genius minor --no-commit
```

### Generate a changelog

The changelog is generated thanks to your commit messages. All commits from
the last tag to the current state of your repository will be used to generate
it.

You can customize the output path of the changelog file by using the `--path`
option. By default, the changelog file is created in the root of your project
and is called `CHANGELOG.md`.

```bash
vendor/bin/release-genius minor --path=docs/CHANGELOG.md
```

You can also choose the way to changelog file is generated. By default, the
changelog file uses the `prepend` mode. This means that the new content is
added at the beginning of the file. You can change this behavior by using the
`--mode` option. The available modes are `append`, `prepend` and `overwrite`.

```bash
vendor/bin/release-genius minor --mode=append
```

### Manage remotes

This package will do its best to guess the remote repository URL. By default, it tries to find
a remote called `origin`.
If it fails, you
can use the `--remote` option to specify the remote repository URL.

```bash
vendor/bin/release-genius minor --remote=upstream
```

This will allow to generate a changelog with the correct links to the commits and versions comparison.

In case you don't use a guessable remote URL (because your hosting a private instance of Gitlab, for example), you can use the `--remote-type` option to specify the type of your remote repository. The available types are `github`, `gitlab`.

```bash
vendor/bin/release-genius minor --remote-type=gitlab
```

Specifying the type will help generate the good URL format, with your remote URL. The remote type **always** takes precedence over the automatic remote URL guessing.
