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
