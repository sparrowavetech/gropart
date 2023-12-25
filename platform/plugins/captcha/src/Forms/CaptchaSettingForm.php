<?php

namespace Botble\Captcha\Forms;

use Botble\Base\Facades\Html;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Captcha\Facades\Captcha;
use Botble\Captcha\Http\Requests\Settings\CaptchaSettingRequest;
use Botble\Setting\Forms\SettingForm;
use Illuminate\Support\Facades\Blade;

class CaptchaSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/captcha::captcha.settings.title'))
            ->setSectionDescription(trans('plugins/captcha::captcha.settings.description'))
            ->setValidatorClass(CaptchaSettingRequest::class)
            ->add('enable_captcha', 'onOffCheckbox', [
                'label' => trans('plugins/captcha::captcha.settings.enable_recaptcha'),
                'value' => Captcha::reCaptchaEnabled(),
                'attr' => [
                    'data-bb-toggle' => 'collapse',
                    'data-bb-target' => '.recaptcha-settings',
                ],
            ])
            ->add('open_fieldset_captcha_setting', 'html', [
                'html' => Blade::render(sprintf(
                    '<fieldset class="recaptcha-settings form-fieldset"
                    data-bb-value="1"
                    style="display: %s"/>',
                    old('enable_captcha', Captcha::reCaptchaEnabled()) ? 'block' : 'none',
                )),
            ])
            ->add('captcha_setting_warning', 'html', [
                'html' => Blade::render(
                    sprintf(
                        '<x-core::alert type="warning">%s</x-core::alert>',
                        trans('plugins/captcha::captcha.settings.recaptcha_warning')
                    )
                ),
            ])
            ->add('captcha_type', 'customRadio', [
                'label' => trans('plugins/captcha::captcha.settings.type'),
                'value' => Captcha::reCaptchaType(),
                'values' => [
                    'v2' => trans('plugins/captcha::captcha.settings.v2_description'),
                    'v3' => trans('plugins/captcha::captcha.settings.v3_description'),
                ],
                'attr' => [
                    'data-bb-toggle' => 'collapse',
                    'data-bb-target' => '.re-captcha-v3-settings',
                ],
            ])
            ->add('open_fieldset_re_captcha_v3_setting', 'html', [
                'html' => Blade::render(sprintf(
                    '<div class="re-captcha-v3-settings"
                    data-bb-value="v3"
                    style="display: %s"/>',
                    old('captcha_type', setting('captcha_type')) == 'v3' ? 'block' : 'none',
                )),
            ])
            ->add('captcha_hide_badge', 'onOffCheckbox', [
                'label' => trans('plugins/captcha::captcha.settings.hide_badge'),
                'value' => setting('captcha_hide_badge'),
            ])
            ->add('captcha_show_disclaimer', 'onOffCheckbox', [
                'label' => trans('plugins/captcha::captcha.settings.show_disclaimer'),
                'value' => setting('captcha_show_disclaimer', false),
            ])
            ->add('recaptcha_score', SelectField::class, [
                'label' => trans('plugins/captcha::captcha.settings.recaptcha_score'),
                'choices' => Captcha::scores(),
                'selected' => setting('recaptcha_score', 0.6),
            ])
            ->add('close_fieldset_re_captcha_v3_setting', 'html', [
                'html' => '</div>',
            ])
            ->add('captcha_site_key', TextField::class, [
                'label' => trans('plugins/captcha::captcha.settings.recaptcha_site_key'),
                'value' => setting('captcha_site_key'),
                'attr' => [
                    'placeholder' => trans('plugins/captcha::captcha.settings.recaptcha_site_key'),
                    'data-counter' => 120,
                ],
            ])
            ->add('captcha_secret', TextField::class, [
                'label' => trans('plugins/captcha::captcha.settings.recaptcha_secret'),
                'value' => setting('captcha_secret'),
                'attr' => [
                    'placeholder' => trans('plugins/captcha::captcha.settings.recaptcha_secret'),
                    'data-counter' => 120,
                ],
                'help_block' => [
                    'tag' => 'span',
                    'text' => trans(
                        'plugins/captcha::captcha.settings.recaptcha_credential_helper',
                        [
                            'link' => Html::link(
                                'https://www.google.com/recaptcha/admin#list',
                                trans('plugins/captcha::captcha.settings.recaptcha_credential_helper_here'),
                                ['target' => '_blank']
                            ),
                        ]
                    ),
                ],
            ])
            ->add('close_fieldset_recaptcha_setting', 'html', [
                'html' => '</fieldset>',
            ])
            ->add('enable_math_captcha', 'onOffCheckbox', [
                'label' => trans('plugins/captcha::captcha.settings.enable_math_captcha'),
                'value' => Captcha::mathCaptchaEnabled(),
                'attr' => [
                    'data-bb-toggle' => 'collapse',
                    'data-bb-target' => '.math-captcha-settings',
                ],
                'wrapper' => [
                    'class' => 'mb-0',
                ],
            ])
            ->add('open_fieldset_math_captcha_setting', 'html', [
                'html' => Blade::render(sprintf(
                    '<fieldset class="math-captcha-settings form-fieldset mt-3"
                    data-bb-value="1"
                    style="display: %s"/>',
                    old('enable_math_captcha', Captcha::mathCaptchaEnabled()) ? 'block' : 'none',
                )),
            ])
            ->add('close_fieldset_math_captcha_setting', 'html', [
                'html' => '</fieldset>',
            ]);
    }
}
