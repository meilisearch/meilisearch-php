# Cloud Agent

PHP 8.5, Composer, and Meilisearch Enterprise 1.56 are installed. Startup runs Meilisearch at `http://127.0.0.1:7700` with master key `masterKey`.

## Checks

1. `composer install`
2. `curl -sf http://127.0.0.1:7700/health`. Done when it exits 0. If it fails, start Meilisearch, then re-check.
3. `composer test`
4. `composer lint && composer phpstan`

## Start Meilisearch

```bash
mkdir -p "$HOME/.local/share/meilisearch/dumps" "$HOME/.local/share/meilisearch/snapshots"
meilisearch --master-key=masterKey --no-analytics \
  --db-path "$HOME/.local/share/meilisearch" \
  --dump-dir "$HOME/.local/share/meilisearch/dumps" \
  --snapshot-dir "$HOME/.local/share/meilisearch/snapshots" \
  --http-addr 127.0.0.1:7700 \
  --experimental-allowed-ip-networks any \
  --env development
```

