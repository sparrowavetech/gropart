<?php

namespace Botble\EcommerceWholesale\Forms\Fronts;

use Botble\Base\Forms\FieldOptions\ButtonFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\PhoneNumberFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\PhoneNumberField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\EcommerceWholesale\Http\Requests\WholesaleRegisterRequest;

class WholesaleRegisterForm extends FormAbstract
{
    public function setup(): void
    {
        $customer = auth('customer')->user();

        $this
            ->contentOnly()
            ->setValidatorClass(WholesaleRegisterRequest::class)
            ->formClass('wholesale-register-form')
            ->setUrl(route('public.wholesale.register.store'))
            ->columns()
            ->when(! $customer, function (FormAbstract $form): void {
                $form
                    ->add(
                        'name',
                        TextField::class,
                        TextFieldOption::make()
                            ->label(trans('plugins/ecommerce-wholesale::wholesale.register.full_name'))
                            ->placeholder(trans('plugins/ecommerce-wholesale::wholesale.frontend.full_name_placeholder'))
                            ->helperText(trans('plugins/ecommerce-wholesale::wholesale.frontend.full_name_help'))
                            ->colspan(1)
                            ->required(),
                    )
                    ->add(
                        'email',
                        EmailField::class,
                        TextFieldOption::make()
                            ->label(trans('plugins/ecommerce-wholesale::wholesale.application.email'))
                            ->placeholder(trans('plugins/ecommerce-wholesale::wholesale.frontend.email_placeholder'))
                            ->helperText(trans('plugins/ecommerce-wholesale::wholesale.frontend.email_help'))
                            ->colspan(1)
                            ->required(),
                    );
            })
            ->when($customer, function (FormAbstract $form) use ($customer): void {
                $form->add(
                    'logged_in_info',
                    HtmlField::class,
                    HtmlFieldOption::make()
                        ->content(
                            '<div class="alert alert-info mb-3">'
                            . trans('plugins/ecommerce-wholesale::wholesale.frontend.logged_in_as', [
                                'name' => $customer->name,
                                'email' => $customer->email,
                            ])
                            . '</div>'
                        )
                        ->colspan(2),
                );
            })
            ->add(
                'company_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.application.company_name'))
                    ->placeholder(trans('plugins/ecommerce-wholesale::wholesale.frontend.company_name_placeholder'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.frontend.company_name_help'))
                    ->colspan(1)
                    ->required(),
            )
            ->add(
                'tax_id',
                TextField::class,
                TextFieldOption::make()
                    ->maxLength(100)
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.application.tax_id'))
                    ->placeholder(trans('plugins/ecommerce-wholesale::wholesale.frontend.tax_id_placeholder'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.frontend.tax_id_help'))
                    ->colspan(1),
            )
            ->add(
                'phone',
                PhoneNumberField::class,
                PhoneNumberFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.application.phone'))
                    ->placeholder(trans('plugins/ecommerce-wholesale::wholesale.frontend.phone_placeholder'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.frontend.phone_help'))
                    ->colspan(1)
                    ->withCountryCodeSelection(),
            )
            ->add(
                'business_type',
                TextField::class,
                TextFieldOption::make()
                    ->maxLength(100)
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.application.business_type'))
                    ->placeholder(trans('plugins/ecommerce-wholesale::wholesale.frontend.business_type_placeholder'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.frontend.business_type_help'))
                    ->colspan(1),
            )
            ->add(
                'expected_volume',
                TextField::class,
                TextFieldOption::make()
                    ->maxLength(100)
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.application.expected_volume'))
                    ->placeholder(trans('plugins/ecommerce-wholesale::wholesale.frontend.expected_volume_placeholder'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.frontend.expected_volume_help'))
                    ->colspan(2),
            )
            ->add(
                'notes',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->maxLength(2000)
                    ->rows(4)
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.application.notes'))
                    ->placeholder(trans('plugins/ecommerce-wholesale::wholesale.frontend.tell_us_about_business'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.frontend.notes_help'))
                    ->colspan(2),
            )
            ->add(
                'submit',
                'submit',
                ButtonFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.register.submit'))
                    ->cssClass('btn btn-primary'),
            );
    }
}
