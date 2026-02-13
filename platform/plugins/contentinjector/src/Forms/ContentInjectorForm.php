<?php

namespace Botble\ContentInjector\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\ContentInjector\Http\Requests\ContentInjectorRequest;
use Botble\ContentInjector\Models\ContentInjector;

class ContentInjectorForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(ContentInjector::class)
            ->setValidatorClass(ContentInjectorRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required()->toArray())
            ->add('value', TextareaField::class, TextareaFieldOption::make()->required()->label("Value")->toArray())
            ->add('status', SelectField::class, StatusFieldOption::make()->toArray())
            ->setBreakFieldPoint('status');
    }
}
