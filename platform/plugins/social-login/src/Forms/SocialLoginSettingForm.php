<?php

namespace Botble\SocialLogin\Forms;

use Botble\Setting\Forms\SettingForm;
use Botble\SocialLogin\Http\Requests\Settings\SocialLoginSettingRequest;

class SocialLoginSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/social-login::social-login.settings.title'))
            ->setSectionDescription(trans('plugins/social-login::social-login.settings.description'))
            ->setValidatorClass(SocialLoginSettingRequest::class)
            ->add('social_login', 'html', [
                'html' => view('plugins/social-login::partials.social-login-fields'),
                'wrapper' => [
                    'class' => 'mb-0',
                ],
            ]);
    }
}
