<?php

namespace Skillcraft\ContactManager\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\Fields\SelectField;
use Skillcraft\ContactManager\Models\ContactTag;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Skillcraft\ContactManager\Http\Requests\ContactTagRequest;

class ContactTagForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(ContactTag::class)
            ->setValidatorClass(ContactTagRequest::class)
            ->add(
                'name',
                TextField::class,
                NameFieldOption::make()
                    ->required()
                    ->maxLength(120)
                    ->toArray()
            )
            ->add(
                'status',
                SelectField::class,
                StatusFieldOption::make()
                    ->required()
                    ->toArray()
            )
            ->setBreakFieldPoint('status');
    }
}
