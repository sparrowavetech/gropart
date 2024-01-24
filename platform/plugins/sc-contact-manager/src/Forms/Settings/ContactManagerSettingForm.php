<?php

namespace Skillcraft\ContactManager\Forms\Settings;

use Botble\Setting\Forms\SettingForm;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Skillcraft\ContactManager\Http\Requests\Settings\ContactManagerSettingRequest;

class ContactManagerSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/sc-contact-manager::contact-manager.settings.title'))
            ->setSectionDescription(trans('plugins/sc-contact-manager::contact-manager.settings.description'))
            ->setValidatorClass(ContactManagerSettingRequest::class)
            ->add(
                'sc-cm-contact-form',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/sc-contact-manager::contact-manager.settings.contact-form'))
                    ->value(setting('sc-cm-contact-form', config('plugins.contact-manager.general.contact_sources.contact_form', false)))
                    ->toArray()
            );
    }
}
