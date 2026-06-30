# Render Template API — Implementation Plan

> **For Hermes:** Use `subagent-driven-development` skill to implement this plan task-by-task.

**Goal:** Add render template API support to `meilisearch-php` SDK (Issue [#921](https://github.com/meilisearch/meilisearch-php/issues/921))

**Architecture:** Follow existing `DynamicSearchRules` pattern — create a Contract DTO for request/response, an Endpoint class for HTTP calls, a Delegate trait for Client convenience methods, and register everything in `Client.php`. The Meilisearch render template API is a simple `POST /render-template` endpoint.

**Tech Stack:** PHP 8.5, PHPUnit, Meilisearch >= 1.48.0

---

## Context

- **Original repo:** `meilisearch/meilisearch-php` (main branch)
- **Fork:** `wayosu/meilisearch-php` 
- **Local:** `~/workspace/contributor/meilisearch-php`
- **Branch:** `feat/render-template`
- **Meilisearch API:** `POST /render-template` with body `{ template: { kind, inline }, input: { kind, inline } }`, responds `{ template, rendered }`
- **Experimental feature:** Enabled via `PATCH /experimental-features` with `{ renderTemplate: true }` (Meilisearch >= v1.48.0)

---

## Files to Create (5)

| # | File | Purpose |
|---|------|---------|
| 1 | `src/Contracts/TemplateRenderQuery.php` | Builder-pattern request DTO (template + input) |
| 2 | `src/Contracts/TemplateRenderResult.php` | Response DTO (template + rendered) |
| 3 | `src/Endpoints/Templates.php` | Endpoint class extends `Contracts\Endpoint`, PATH = `/render-template` |
| 4 | `src/Endpoints/Delegates/HandlesTemplates.php` | Trait delegates to `$this->templates->render()` |
| 5 | `tests/Endpoints/TemplatesTest.php` | Integration tests |

## Files to Modify (2)

| # | File | Change |
|---|------|--------|
| 6 | `src/Client.php` | Add `use HandlesTemplates` + init `$this->templates` |
| 7 | `.code-samples.meilisearch.yaml` | Add `render_template_1:` code sample |

---

## Task Breakdown (TDD — RED → GREEN → REFACTOR)

### Task 1: Write failing test for `renderTemplate()`

**Objective:** Create a test that proves the feature doesn't exist yet

**Files:**
- Create: `tests/Endpoints/TemplatesTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\TemplateRenderQuery;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class TemplatesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $http->patch('/experimental-features', ['renderTemplate' => true]);
    }

    public function testCanRenderInlineTemplate(): void
    {
        $query = (new TemplateRenderQuery())
            ->setTemplate('inlineDocumentTemplate', '{{ doc.breed }} called {{ doc.name }}')
            ->setInput('inlineDocument', ['breed' => 'Jack Russell', 'name' => 'Iko']);

        $response = $this->client->renderTemplate($query);

        self::assertSame('{{ doc.breed }} called {{ doc.name }}', $response->getTemplate());
        self::assertSame('Jack Russell called Iko', $response->getRendered());
    }
}
```

**Step 2: Run test — expect FAIL**
```bash
cd ~/workspace/contributor/meilisearch-php
php vendor/bin/phpunit tests/Endpoints/TemplatesTest.php
```
Expected: Class not found / method not found error

**Step 3: Commit**
```bash
git add tests/Endpoints/TemplatesTest.php
git commit -m "test: add failing test for renderTemplate"
```

---

### Task 2: Create `TemplateRenderQuery` Request DTO

**Objective:** Builder-pattern DTO for the render template request

**Files:**
- Create: `src/Contracts/TemplateRenderQuery.php`

**Complete code:**

```php
<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class TemplateRenderQuery
{
    /**
     * @var array{kind: string, inline: string}
     */
    private array $template = ['kind' => '', 'inline' => ''];

    /**
     * @var array{kind: string, inline: mixed}
     */
    private array $input = ['kind' => '', 'inline' => null];

    /**
     * Set the template to render.
     *
     * @param string $kind  e.g. 'inlineDocumentTemplate'
     * @param string $inline the template string
     */
    public function setTemplate(string $kind, string $inline): self
    {
        $this->template = ['kind' => $kind, 'inline' => $inline];

        return $this;
    }

    /**
     * Set the input document for template rendering.
     *
     * @param string          $kind   e.g. 'inlineDocument'
     * @param array|object|null $inline the document data
     */
    public function setInput(string $kind, mixed $inline): self
    {
        $this->input = ['kind' => $kind, 'inline' => $inline];

        return $this;
    }

    /**
     * @return array{template: array{kind: string, inline: string}, input: array{kind: string, inline: mixed}}
     */
    public function toArray(): array
    {
        return [
            'template' => $this->template,
            'input' => $this->input,
        ];
    }
}
```

**Step 1: Commit**
```bash
git add src/Contracts/TemplateRenderQuery.php
git commit -m "feat: add TemplateRenderQuery request DTO"
```

---

### Task 3: Create `TemplateRenderResult` Response DTO

**Objective:** DTO to parse the render template API response

**Files:**
- Create: `src/Contracts/TemplateRenderResult.php`

**Complete code:**

```php
<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class TemplateRenderResult
{
    private string $template;
    private ?string $rendered;

    /**
     * @param array{template: string, rendered: string|null} $data
     */
    public function __construct(array $data)
    {
        $this->template = $data['template'];
        $this->rendered = $data['rendered'];
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getRendered(): ?string
    {
        return $this->rendered;
    }
}
```

**Step 1: Commit**
```bash
git add src/Contracts/TemplateRenderResult.php
git commit -m "feat: add TemplateRenderResult response DTO"
```

---

### Task 4: Create `Templates` Endpoint Class

**Objective:** Endpoint class for POST /render-template

**Files:**
- Create: `src/Endpoints/Templates.php`

**Complete code:**

```php
<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\TemplateRenderQuery;
use Meilisearch\Contracts\TemplateRenderResult;

final class Templates extends Endpoint
{
    protected const PATH = '/render-template';

    public function render(TemplateRenderQuery $query): TemplateRenderResult
    {
        $response = $this->http->post(self::PATH, $query->toArray());

        return new TemplateRenderResult($response);
    }
}
```

**Step 1: Commit**
```bash
git add src/Endpoints/Templates.php
git commit -m "feat: add Templates endpoint class"
```

---

### Task 5: Create `HandlesTemplates` Delegate Trait

**Objective:** Trait that adds `renderTemplate()` convenience method to Client

**Files:**
- Create: `src/Endpoints/Delegates/HandlesTemplates.php`

**Complete code:**

```php
<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\TemplateRenderQuery;
use Meilisearch\Contracts\TemplateRenderResult;
use Meilisearch\Endpoints\Templates;

trait HandlesTemplates
{
    protected Templates $templates;

    public function renderTemplate(TemplateRenderQuery $query): TemplateRenderResult
    {
        return $this->templates->render($query);
    }
}
```

**Step 1: Commit**
```bash
git add src/Endpoints/Delegates/HandlesTemplates.php
git commit -m "feat: add HandlesTemplates delegate trait"
```

---

### Task 6: Register in `Client.php`

**Objective:** Wire up the Templates endpoint in Client

**Files:**
- Modify: `src/Client.php`

**Changes:**

1. Add import at top (after `HandlesSystem`):
```php
use Meilisearch\Endpoints\Delegates\HandlesTemplates;
```

2. Add import (after `Tasks`):
```php
use Meilisearch\Endpoints\Templates;
```

3. Add `use` trait inside class (after `use HandlesDynamicSearchRules;`):
```php
    use HandlesTemplates;
```

4. Add initialization in constructor (after `$this->dynamicSearchRules`):
```php
        $this->templates = new Templates($this->http);
```

**Step 1: Run test — expect GREEN**
```bash
# Ensure Meilisearch is running with:
# curl -L https://install.meilisearch.com | sh
# ./meilisearch --master-key=masterKey --no-analytics
# export MEILISEARCH_API_KEY=***
php vendor/bin/phpunit tests/Endpoints/TemplatesTest.php
```
Expected: 1 test passed ✓

**Step 2: Commit**
```bash
git add src/Client.php
git commit -m "feat: register Templates endpoint in Client"
```

---

### Task 7: Add code sample

**Objective:** Add code sample for Meilisearch documentation

**Files:**
- Modify: `.code-samples.meilisearch.yaml`

**Add at the end of the file:**

```yaml
render_template_1: |-
  $client->renderTemplate(
    (new TemplateRenderQuery())
      ->setTemplate('inlineDocumentTemplate', '{{ doc.breed }} called {{ doc.name }}')
      ->setInput('inlineDocument', ['breed' => 'Jack Russell', 'name' => 'Iko'])
  );
```

**Step 1: Commit**
```bash
git add .code-samples.meilisearch.yaml
git commit -m "docs: add render template code sample"
```

---

### Task 8: Run full test suite + lint + PHPStan

**Objective:** Verify nothing is broken

```bash
# Full test suite
php vendor/bin/phpunit tests/ -q

# Lint
php vendor/bin/php-cs-fixer fix --dry-run --diff

# PHPStan
php vendor/bin/phpstan analyse
```

**Step 1: Fix any issues found**

**Step 2: Commit fixes if any**
```bash
git add -A
git commit -m "fix: resolve lint/phpstan issues"
```

---

## Risks & Tradeoffs

| Risk | Mitigation |
|------|------------|
| Meilisearch < 1.48.0 doesn't support render template | Ensure test environment has Meilisearch >= 1.48.0 |
| Experimental feature may change API | Mark as experimental in PHPDoc, follow existing convention |
| PHPStan may complain about new files | Follow existing type patterns from DynamicSearchRules |

## Verification Checklist

- [ ] `php vendor/bin/phpunit tests/Endpoints/TemplatesTest.php` — GREEN
- [ ] `php vendor/bin/phpunit tests/ -q` — full suite GREEN
- [ ] `php vendor/bin/php-cs-fixer fix --dry-run` — no issues
- [ ] `php vendor/bin/phpstan analyse` — no new errors
