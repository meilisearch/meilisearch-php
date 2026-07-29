# Contract Typing

- Use precise PHPStan array shapes over `array<string, mixed>`
- Type `toArray()` with the most accurate known shape
- Name API payload array shapes `Raw*` (e.g. `RawSearchQuery`), not `*Array`
