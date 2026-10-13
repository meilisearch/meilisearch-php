# [AGENTS.md](http://AGENTS.md)

## Commands

This repository comes with a Docker configuration to run commands in Docker.

Run commands using Docker Compose with `docker compose run --rm package bash -c <command>`.

Commands:

- Install dependencies (required): `composer install`
- Run tests: `composer test`
- Lint: `composer lint && composer phpstan`

## Cursor Cloud specific instructions

Docker is not available in Cursor Cloud Agents. See instructions in `.cursor/CLOUD.md`.
