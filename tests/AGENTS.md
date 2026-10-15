# Tests

- `Tests\TestCase` (`tests/TestCase.php`) is for integration tests against a running Meilisearch instance (`tearDown()` deletes all indexes over HTTP). Pure unit tests in `tests/Contracts/` extend `PHPUnit\Framework\TestCase`.
- PHPStan (`bleedingEdge.neon`) rejects calls from `Tests\` to `@internal` symbols in `Meilisearch\`. Share test helpers via `public static` methods on `Tests\TestCase` rather than duplicating `private static` helpers across unit test classes.
