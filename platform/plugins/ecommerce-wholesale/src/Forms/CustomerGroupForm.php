<?php

namespace Botble\EcommerceWholesale\Forms;

use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Http\Requests\CustomerGroupRequest;
use Botble\EcommerceWholesale\Models\CustomerGroup;

class CustomerGroupForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->setupModel(new CustomerGroup())
            ->setValidatorClass(CustomerGroupRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('description', TextareaField::class, DescriptionFieldOption::make())
            ->add(
                'discount_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.customer_group.discount_type'))
                    ->choices(DiscountTypeEnum::labels())
                    ->required()
            )
            ->add(
                'discount_value',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.customer_group.discount_value'))
                    ->required()
            )
            ->add(
                'priority',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.customer_group.priority'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.customer_group.priority_help'))
                    ->defaultValue(0)
            )
            ->add(
                'min_order_quantity',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.moq.min_quantity'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.moq.group_moq_help'))
            )
            ->add(
                'min_order_value',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.moq.min_order_value'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.moq.min_order_value_help'))
            )
            ->add(
                'status',
                SelectField::class,
                StatusFieldOption::make()->choices(CustomerGroupStatusEnum::labels())
            );
    }
}
