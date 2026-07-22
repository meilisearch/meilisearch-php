---
name: write-meilisearch-php-phpdoc
description: Write PHPDoc for meilisearch-php public APIs. Use when documenting client, endpoint, or delegate methods, adding @since or @see tags, or updating PHPDoc on SDK public methods.
---

# Write meilisearch-php PHPDoc

## Shape

Match nearby methods in the same file. Prefer compact PHPDoc that adds information the native PHP signature cannot express.

Typical stable method:

```php
/**
 * Get a dynamic search rule.
 *
 * @param non-empty-string $uid Dynamic search rule UID
 *
 * @since Meilisearch v1.41.0
 * @see https://www.meilisearch.com/docs/reference/api/search-rules/get-a-search-rule
 */
public function getDynamicSearchRule(string $uid): DynamicSearchRule
```

Typical experimental method:

```php
/**
 * Get a dynamic search rule.
 *
 * This is an EXPERIMENTAL feature, which may break without a major version.
 *
 * @param non-empty-string $uid Dynamic search rule UID
 *
 * @since Meilisearch v1.41.0
 * @see https://www.meilisearch.com/docs/reference/api/search-rules/get-a-search-rule
 */
public function getDynamicSearchRule(string $uid): DynamicSearchRule
```

If the signature already fully expresses the types, `@param` and `@return` are optional. Keep them when they add phpstan refinements such as `non-empty-string`, array shapes, or literal unions, or when a short description prevents ambiguity.

## Rules

- Link the matching API reference page with `@see https://www.meilisearch.com/docs/...`
- Resolve URLs from `https://www.meilisearch.com/docs/llms.txt`; prefer current canonical paths over aliases
- Use `@since Meilisearch vX.Y.Z` for version-gated APIs instead of putting version requirements in the prose summary
- Mark experimental APIs with the prose notice `This is an EXPERIMENTAL feature, which may break without a major version.`
- Prefer phpstan-friendly refinements in docblocks: `non-empty-string`, array shapes, literal unions, typed lists
- Do not add redundant `@param` or `@return` tags when the native type already says enough
- Match nearby summary punctuation and wording in the same file
- Do not invent docs URLs; if no page exists, link the closest related API reference page

## Check

- `docker compose run --rm package bash -c "composer lint:fix && composer phpstan"`
