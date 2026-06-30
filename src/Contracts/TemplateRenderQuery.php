<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class TemplateRenderQuery
{
    /**
     * @var array{kind: string, ...}
     */
    private array $template = ['kind' => '', 'inline' => ''];

    /**
     * @var array{kind: string, ...}|null
     */
    private ?array $input = null;

    private bool $inputSet = false;

    /**
     * Set the template to render.
     *
     * Supports two kinds:
     * - inlineDocumentTemplate: ['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}']
     * - documentTemplate: ['kind' => 'documentTemplate', 'indexUid' => 'movies', 'embedder' => 'myEmbedder']
     *   or ['kind' => 'documentTemplate', 'indexUid' => 'movies', 'templateUid' => 'myTemplate']
     *
     * @param array{kind: string, ...} $template
     */
    public function setTemplate(array $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Set the input document for template rendering.
     *
     * Supports two kinds:
     * - inlineDocument: ['kind' => 'inlineDocument', 'inline' => ['name' => 'John']]
     * - indexDocument: ['kind' => 'indexDocument', 'indexUid' => 'movies', 'id' => '2']
     *
     * Pass null to explicitly send null input (API returns rendered: null).
     * Omit this call entirely to not include input in the request.
     *
     * @param array{kind: string, ...}|null $input
     */
    public function setInput(?array $input): self
    {
        $this->input = $input;
        $this->inputSet = true;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [
            'template' => $this->template,
        ];

        if ($this->inputSet) {
            $result['input'] = $this->input;
        }

        return $result;
    }
}
