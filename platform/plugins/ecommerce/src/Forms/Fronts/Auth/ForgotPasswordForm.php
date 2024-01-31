<?php

namespace Botble\Ecommerce\Forms\Fronts\Auth;

use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\EmailFieldOption;
use Botble\Ecommerce\Http\Requests\Fronts\Auth\ForgotPasswordRequest;

class ForgotPasswordForm extends AuthForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setUrl(route('customer.password.request'))
            ->setValidatorClass(ForgotPasswordRequest::class)
            ->icon('ti ti-lock-question')
            ->heading(__('Forgot Password'))
            ->description(__('Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.'))
            ->add(
                'email',
                EmailField::class,
                EmailFieldOption::make()
                    ->label(__('Email'))
                    ->placeholder(__('Email address'))
                    ->icon('ti ti-mail')
                    ->toArray()
            )
            ->submitButton(__('Send Password Reset Link'), 'ti ti-arrow-narrow-right')
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
