<?php

namespace Botble\Base\Forms;

use Botble\Base\Supports\Builders\HasAttributes;
use Botble\Base\Supports\Builders\HasLabel;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

class FormFieldOptions implements Arrayable
{
    use Conditionable;
    use HasAttributes;
    use HasLabel;
    use Tappable;

    protected bool $required = false;

    protected int $colspan = 0;

    protected array $helperText = [];

    protected array $labelAttributes = [];

    protected array $wrapperAttributes = [];

    protected bool $metadata = false;

    protected Closure|bool $disabled = false;

    public static function make(): static
    {
        return app(static::class);
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function colspan(int $colspan): static
    {
        $this->colspan = $colspan;

        return $this;
    }

    public function getColspan(): int
    {
        return $this->colspan;
    }

    public function labelAttributes(array $attributes): static
    {
        $this->labelAttributes = $attributes;

        return $this;
    }

    public function getLabelAttributes(): array
    {
        return $this->labelAttributes;
    }

    public function wrapperAttributes(array $attributes): static
    {
        $this->wrapperAttributes = $attributes;

        return $this;
    }

    public function getWrapperAttributes(): array
    {
        return $this->wrapperAttributes;
    }

    public function helperText(string $helperText, array $attributes = []): static
    {
        $attributes['class'] = (isset($attributes['class']) ? ' ' : '') . 'form-hint';

        $this->helperText = [
            'text' => $helperText,
            'tag' => 'small',
            'attr' => $attributes,
        ];

        return $this;
    }

    public function getHelperText(): array
    {
        return $this->helperText;
    }

    public function isMetadata(): bool
    {
        return $this->metadata;
    }

    public function metadata(bool $metadata = true): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function disabled(Closure|bool $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function isDisabled(): bool
    {
        return is_callable($this->disabled) ? call_user_func($this->disabled) : $this->disabled;
    }

    public function toArray(): array
    {
        $data = [
            'label' => $this->getLabel(),
            'required' => $this->isRequired(),
            'attr' => $this->getAttributes(),
        ];

        if ($this->colspan) {
            $data['colspan'] = $this->getColspan();
        }

        if ($this->helperText) {
            $data['help_block'] = $this->getHelperText();
        }

        if ($this->labelAttributes) {
            $data['label_attr'] = $this->getLabelAttributes();
        }

        if ($this->wrapperAttributes) {
            $data['wrapper'] = $this->getWrapperAttributes();
        }

        if ($this->isMetadata()) {
            $data['metadata'] = true;
        }

        if ($this->isDisabled()) {
            $data['attr']['disabled'] = true;
        }

        return $data;
    }
}
