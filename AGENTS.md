# AGENTS.md

## Workflow

- Lint when the task is finished.

## Commands

Cloud Agents have PHP 8.5, Composer, and Meilisearch Enterprise 1.54. Meilisearch is started on boot at `http://127.0.0.1:7700` with master key `masterKey`, which matches `phpunit.xml.dist`.

- Install dependencies: `composer install`
- Tests: `composer test`
- Lint: `composer lint && composer phpstan`

`composer install` is idempotent. This library does not commit `composer.lock`.

Multimodal search tests skip unless `VOYAGE_API_KEY` is set. The rest of the suite does not need it.

Docker is not installed in the Cloud Agent environment. On a machine with Docker, the same checks can be run with:

- Tests: `docker compose run --rm package bash -c "composer install && composer test"`
- Lint: `docker compose run --rm package bash -c "composer lint && composer phpstan"`
