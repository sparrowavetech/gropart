<?php

namespace Botble\Ecommerce\Forms\Fronts\Auth;

use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\PasswordField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\EmailFieldOption;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\TextFieldOption;
use Botble\Ecommerce\Http\Requests\Fronts\Auth\ResetPasswordRequest;

class ResetPasswordForm extends AuthForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setUrl(route('customer.password.reset.post'))
            ->icon('ti ti-lock')
            ->setValidatorClass(ResetPasswordRequest::class)
            ->heading(__('Reset Password'))
            ->add(
                'token',
                'hidden',
                TextFieldOption::make()
                    ->value($this->request->route('token'))
                    ->toArray()
            )
            ->add(
                'email',
                TextField::class,
                EmailFieldOption::make()
                    ->label(__('Email address'))
                    ->value($this->request->input('email'))
                    ->icon('ti ti-mail')
                    ->toArray()
            )
            ->add(
                'password',
                PasswordField::class,
                TextFieldOption::make()
                    ->label(__('Password'))
                    ->placeholder(__('Password'))
                    ->icon('ti ti-lock')
                    ->toArray()
            )
            ->add(
                'password_confirmation',
                PasswordField::class,
                TextFieldOption::make()
                    ->label(__('Password confirmation'))
                    ->placeholder(__('Password confirmation'))
                    ->icon('ti ti-lock')
                    ->toArray()
            )
            ->submitButton(__('Reset Password'), 'ti ti-arrow-narrow-right')
            ->add('back_to_login', HtmlField::class, [
                'html' => sprintf(
                    '<div class="mt-3 text-center"><a href="%s" class="text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-narrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M5 12l14 0"></path>
                    <path d="M5 13l4 4"></path>
                    <path d="M5 11l4 -4"></path>
                  </svg> %s</a></div>',
                    route('customer.login'),
                    __('Back to login page')
                ),
            ]);
    }
}
