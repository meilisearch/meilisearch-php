# Contract Typing

- Use precise PHPStan array shapes over `array<string, mixed>`
- Import shared aliases with `@phpstan-import-type` instead of duplicating shapes
- Type `toArray()` with the most accurate known shape
