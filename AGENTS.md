# AGENTS.md

## Workflow

Lint when the task is finished.

## Commands

Run checks with Docker Compose:

- Tests: `docker compose run --rm package bash -c "composer install && composer test"`
- Lint: `docker compose run --rm package bash -c "composer lint && composer phpstan"`

Multimodal search tests skip unless `VOYAGE_API_KEY` is set.

## Cursor Cloud specific instructions

On a Cloud Agent, install, test, and lint with `.cursor/CLOUD.md`.
