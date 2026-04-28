# AGENTS.md

## Workflow

- Lint when the task is finished.

## Commands

Use Docker Compose to run commands:
- Tests: `docker compose run --rm package bash -c "composer install && composer test"`
- Lint: `docker compose run --rm package bash -c "composer lint && composer phpstan"`
