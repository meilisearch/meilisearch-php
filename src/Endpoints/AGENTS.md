# Endpoint Typing

- Map raw API arrays into contract objects (`src/Contracts`) at the endpoint boundary
- Do not weaken known payloads to `array<string, mixed>` unless the API data is intentionally arbitrary
