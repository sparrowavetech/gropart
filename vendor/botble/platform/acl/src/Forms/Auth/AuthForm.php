<?php

namespace Botble\ACL\Forms\Auth;

use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Models\BaseModel;
use Illuminate\Support\Facades\Blade;

class AuthForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(BaseModel::class)
            ->template('core/acl::auth.form');
    }

    public function heading(string $heading): self
    {
        $this->add('heading', HtmlField::class, [
            'html' => sprintf(
                '<h2 class="h3 text-center mb-3">%s</h2>',
                $heading
            ),
        ]);

        return $this;
    }

    public function submitButton(string $label, string|null $icon = null): self
    {
        $this
            ->add('open_wrap_button', HtmlField::class, [
                'html' => '<div class="form-footer">',
            ])
            ->add('button_submit', HtmlField::class, [
                'html' => Blade::render(sprintf(
                    '<x-core::button type="submit" color="primary" class="w-full" icon="%s">%s</x-core::button>',
                    $icon ?? '',
                    $label
                )),
            ])
            ->add('close_wrap_button', HtmlField::class, [
                'html' => '</div>',
            ]);

        return $this;
    }
}
