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
     * @var array{kind: string, inline: array|object|null}
     */
    private array $input = ['kind' => '', 'inline' => null];

    /**
     * Set the template to render.
     *
     * @param string $kind   e.g. 'inlineDocumentTemplate'
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
     * @param string               $kind   e.g. 'inlineDocument'
     * @param array|object|null    $inline the document data
     */
    public function setInput(string $kind, array|object|null $inline): self
    {
        $this->input = ['kind' => $kind, 'inline' => $inline];

        return $this;
    }

    /**
     * @return array{
     *     template: array{kind: string, inline: string},
     *     input: array{kind: string, inline: array|object|null}
     * }
     */
    public function toArray(): array
    {
        return [
            'template' => $this->template,
            'input' => $this->input,
        ];
    }
}
