# Contributing

Thank you for considering contributing to Meilisearch! This document provides everything you need to know in order to contribute to Meilisearch PHP.

<!-- MarkdownTOC autolink="true" style="ordered" indent="   " -->

- [Coding with AI](#coding-with-ai)
- [Assumptions](#assumptions)
- [How to Contribute](#how-to-contribute)
- [Development Workflow](#development-workflow)
- [Git Guidelines](#git-guidelines)
- [Release Process (for internal team only)](#release-process-for-internal-team-only)

<!-- /MarkdownTOC -->

## Coding with AI

We accept the use of AI-powered tools (GitHub Copilot, ChatGPT, Claude, Cursor, etc.) for contributions, whether for code, tests, or documentation.

⚠️ However, transparency is required: if you use AI assistance, please mention it in your PR description. This helps maintainers during code review and ensure the quality of contributions.

What we expect:
- **Disclose AI usage**: A simple note like "Used GitHub Copilot for autocompletion" or "Generated initial test structure with ChatGPT" is sufficient.
- **Specify the scope**: Indicate which parts of your contribution involved AI assistance.
- **Review AI-generated content**: Ensure you understand and have verified any AI-generated code before submitting.

## Assumptions

1. **You're familiar with [GitHub](https://github.com) and [Pull Requests](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/about-pull-requests).**
2. **You've read the Meilisearch [documentation](https://docs.meilisearch.com) and this SDK's [README](/README.md).**
3. **You know about the [Meilisearch community](https://dub.sh/meili-discord?utm_campaign=oss&utm_source=github). Please use this for help.**

## How to Contribute

1. Make sure that the contribution you want to make is explained or detailed in a GitHub issue! Find an [existing issue](https://github.com/meilisearch/meilisearch-php/issues/) or [open a new one](https://github.com/meilisearch/meilisearch-php/issues/new).
2. Once done, [fork the meilisearch-php repository](https://help.github.com/en/github/getting-started-with-github/fork-a-repo) in your own GitHub account. Ask a maintainer if you want your issue to be checked before making a PR.
3. [Create a new Git branch](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-and-deleting-branches-within-your-repository).
4. Review the [Development Workflow](#development-workflow) section that describes the steps to maintain the repository.
5. Make your changes on your branch. If you use AI tools during your work, remember to disclose it in your PR description (see [Coding with AI](#coding-with-ai)).
6. [Submit the branch as a PR](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request-from-a-fork) pointing to the `main` branch of the main meilisearch-php repository.

Read more about [Git guidelines](#git-guidelines) below.

## Development Workflow

### Setup

You can set up your local environment natively or using `docker`, check out the [`docker-compose.yml`](/docker-compose.yml).

Example of running all the checks with docker:
```bash
docker-compose run --rm package bash -c "composer install && composer test && composer lint && composer phpstan"
```

To install dependencies:
```bash
composer install
```

### Tests and Linter

Each PR should pass the tests and the linter to be accepted.

```bash
# Tests
curl -L https://install.meilisearch.com | sh # download Meilisearch
./meilisearch --master-key=masterKey --no-analytics # run Meilisearch
composer test
# Linter (with auto-fix)
composer lint:fix
# Linter (without auto-fix)
composer lint
```

## Git Guidelines

### Git Branches

All changes must be made in a branch and submitted as PR.
We do not enforce any branch naming style, but please use something descriptive of your changes.

### Git Commits

As minimal requirements, your commit message should:
- be capitalized
- not finish by a dot or any other punctuation character (!,?)
- start with a verb so that we can read your commit message this way: "This commit will ...", where "..." is the commit message.
  e.g.: "Fix the home page button" or "Add more tests for create_index method"

We don't follow any other convention, but if you want to use one, we recommend [this one](https://chris.beams.io/posts/git-commit/).

### GitHub Pull Requests

Some notes on GitHub PRs:

- [Convert your PR as a draft](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/changing-the-stage-of-a-pull-request) if your changes are a work in progress: no one will review it until you pass your PR as ready for review.<br>
  The draft PR can be very useful if you want to show that you are working on something and make your work visible.
- The branch related to the PR must be **up-to-date with `main`** before merging. This project uses [Merge Queues](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/configuring-pull-request-merges/managing-a-merge-queue) to automatically enforce this requirement.
- All PRs must be reviewed and approved by at least one maintainer.
- The PR title should be accurate and descriptive of the changes. The title of the PR will be indeed automatically added to the next [release changelogs](https://github.com/meilisearch/meilisearch-php/releases/).

## Release Process (for the internal team only)

Meilisearch tools follow the [Semantic Versioning Convention](https://semver.org/).

### Automated Changelogs

This project uses [Release Drafter](https://github.com/release-drafter/release-drafter) to automate changelog creation.

### How to Publish the Release

⚠️ Before doing anything, make sure you got through the guide about [Releasing an Integration](https://github.com/meilisearch/integration-guides/blob/main/resources/integration-release.md).

Use [our automation](https://github.com/meilisearch/meilisearch-php/actions/workflows/update-version.yml) to update the version: click on `Run workflow`, and fill the appropriate version before validating. A PR updating the version in the [`src/Meilisearch.php`](/src/Meilisearch.php) file will be created.

Or do it manually:

Make a PR modifying the file [`src/Meilisearch.php`](/src/Meilisearch.php) with the right version.

```php
const VERSION = 'X.X.X';
```

Once the changes are merged on `main`, you can publish the current draft release via the [GitHub interface](https://github.com/meilisearch/meilisearch-php/releases): on this page, click on `Edit` (related to the draft release) > update the description (be sure you apply [these recommendations](https://github.com/meilisearch/integration-guides/blob/main/resources/integration-release.md#writting-the-release-description)) > when you are ready, click on `Publish release`.

A WebHook will be triggered and push the package to [Packagist](https://packagist.org/packages/meilisearch/meilisearch-php).

<hr>

Thank you for reading this through ❤️ We're excited to collaborate with you!
