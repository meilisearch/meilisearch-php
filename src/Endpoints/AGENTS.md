# Endpoint Typing

- Map raw API arrays into contract objects (`src/Contracts`) at the endpoint boundary
- Do not weaken known payloads to `array<string, mixed>` unless the API data is intentionally arbitrary
- Pass `\JsonSerializable` contracts (`Settings`, `Synonyms`, `Faceting`, `TypoTolerance`, `Embedders`) directly to `$this->http` rather than unwrapping with `toArray()`, so empty maps serialize as JSON `{}` instead of `[]`
