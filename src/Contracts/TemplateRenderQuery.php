<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class TemplateRenderQuery
{
    /**
     * @var array{kind: 'inlineDocumentTemplate', inline: string}|array{kind: 'documentTemplate', indexUid: non-empty-string, embedder: non-empty-string}|array{kind: 'documentTemplate', indexUid: non-empty-string, templateUid: non-empty-string}
     */
    private array $template;

    /**
     * @var array{kind: 'inlineDocument', inline: array<string, mixed>}|array{kind: 'indexDocument', indexUid: non-empty-string, id: string|int}|null
     */
    private ?array $input = null;

    private bool $inputSet = false;

    /**
     * @param array{kind: 'inlineDocumentTemplate', inline: string}|array{kind: 'documentTemplate', indexUid: non-empty-string, embedder: non-empty-string}|array{kind: 'documentTemplate', indexUid: non-empty-string, templateUid: non-empty-string} $template
     * @param array{kind: 'inlineDocument', inline: array<string, mixed>}|array{kind: 'indexDocument', indexUid: non-empty-string, id: string|int}|null                                                                                                $input
     */
    public function __construct(array $template, ?array $input = null)
    {
        $this->template = $template;

        if (\func_num_args() >= 2) {
            $this->input = $input;
            $this->inputSet = true;
        }
    }

    /**
     * Set the template to render.
     *
     * Supports two kinds:
     * - inlineDocumentTemplate: ['kind' => 'inlineDocumentTemplate', 'inline' => '{{ doc.name }}']
     * - documentTemplate: ['kind' => 'documentTemplate', 'indexUid' => 'movies', 'embedder' => 'myEmbedder']
     *   or ['kind' => 'documentTemplate', 'indexUid' => 'movies', 'templateUid' => 'myTemplate']
     *
     * @param array{kind: 'inlineDocumentTemplate', inline: string}|array{kind: 'documentTemplate', indexUid: non-empty-string, embedder: non-empty-string}|array{kind: 'documentTemplate', indexUid: non-empty-string, templateUid: non-empty-string} $template
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
     * @param array{kind: 'inlineDocument', inline: array<string, mixed>}|array{kind: 'indexDocument', indexUid: non-empty-string, id: string|int}|null $input
     */
    public function setInput(?array $input): self
    {
        $this->input = $input;
        $this->inputSet = true;

        return $this;
    }

    /**
     * @return array{template: array{kind: 'inlineDocumentTemplate', inline: string}|array{kind: 'documentTemplate', indexUid: non-empty-string, embedder: non-empty-string}|array{kind: 'documentTemplate', indexUid: non-empty-string, templateUid: non-empty-string}, input?: array{kind: 'inlineDocument', inline: array<string, mixed>}|array{kind: 'indexDocument', indexUid: non-empty-string, id: string|int}|null}
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
