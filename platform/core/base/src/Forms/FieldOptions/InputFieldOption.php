<?php

namespace Botble\Base\Forms\FieldOptions;

use Botble\Base\Forms\FormFieldOptions;

class InputFieldOption extends FormFieldOptions
{
    protected array|string|bool|null $value;

    protected array|string|bool|null $defaultValue;

    public function value(array|string|bool|null $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): array|string|bool|null
    {
        return $this->value;
    }

    public function getDefaultValue(): bool|array|string|null
    {
        return $this->defaultValue;
    }

    public function defaultValue(bool|array|string|null $defaultValue): static
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->addAttribute('placeholder', $placeholder);

        return $this;
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        if (isset($this->value)) {
            $data['value'] = $this->getValue();
        }

        if (isset($this->defaultValue)) {
            $data['default_value'] = $this->getDefaultValue();
        }

        return $data;
    }
}
