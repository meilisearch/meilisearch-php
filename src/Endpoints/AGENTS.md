# Endpoint Typing

- Define endpoint-only response aliases locally; import contract aliases with `@phpstan-import-type`
- Map raw API arrays into contract objects (`src/Contracts`) at the endpoint boundary
- Do not weaken known payloads to `array<string, mixed>` unless the API data is intentionally arbitrary
