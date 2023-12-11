<?php

namespace Botble\Base\Forms\FieldOptions;

use Botble\Base\Forms\FormFieldOptions;

class CheckboxFieldOption extends FormFieldOptions
{
    protected array|bool|string|int|null $value;

    protected array|bool|string|int|null $defaultValue;

    public function value(array|bool|string|int|null $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): array|bool|string|int|null
    {
        return $this->value;
    }

    public function getDefaultValue(): array|bool|string|int|null
    {
        return $this->defaultValue;
    }

    public function defaultValue(array|bool|string|int|null $defaultValue): static
    {
        $this->defaultValue = $defaultValue;

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
