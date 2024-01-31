<?php

namespace Skillcraft\ContactManager\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Skillcraft\ContactManager\Http\Requests\ContactGroupRequest;
use Skillcraft\ContactManager\Models\ContactGroup;

class ContactGroupForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(ContactGroup::class)
            ->setValidatorClass(ContactGroupRequest::class)
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
