# Contract Typing

- Prefer precise PHPStan array shapes over `array<string, mixed>`; type `toArray()` with the most accurate known shape.
- Name types so it's clear whether they're a wire-format payload or a domain concept:
  - `Raw*` to disambiguate array shapes vs class (e.g. `RawSettings` vs the `Settings` DTO).
  - Avoid suffixing with `*Array`
- On `\JsonSerializable` settings contracts (`src/Contracts/Index/*`), keep `toArray()` and `jsonSerialize()` distinct: `toArray()` unwraps to plain PHP arrays for callers, while `jsonSerialize()` returns `(object)` so empty maps encode as `{}` on the wire.
