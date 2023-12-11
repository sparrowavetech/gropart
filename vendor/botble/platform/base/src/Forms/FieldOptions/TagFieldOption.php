<?php

namespace Botble\Base\Forms\FieldOptions;

class TagFieldOption extends TextFieldOption
{
    public function ajaxUrl(string $url): static
    {
        $this->addAttribute('data-url', $url);

        return $this;
    }
}
