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
