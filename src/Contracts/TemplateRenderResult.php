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
