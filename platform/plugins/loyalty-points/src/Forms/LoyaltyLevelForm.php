<?php

namespace Botble\LoyaltyPoints\Forms;

use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\LoyaltyPoints\Http\Requests\LoyaltyLevelRequest;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;

class LoyaltyLevelForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(LoyaltyLevel::class)
            ->setValidatorClass(LoyaltyLevelRequest::class)
            ->add(
                'name',
                TextField::class,
                NameFieldOption::make()
                    ->required()
                    ->toArray()
            )
            ->add(
                'badge',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.levels.badge'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.levels.badge_help'))
                    ->toArray()
            )
            ->add(
                'min_points',
                NumberField::class,
                [
                    'label' => trans('plugins/loyalty-points::loyalty-points.levels.min_points'),
                    'required' => true,
                    'attr' => [
                        'placeholder' => 0,
                    ],
                ]
            )
            ->add(
                'max_points',
                NumberField::class,
                [
                    'label' => trans('plugins/loyalty-points::loyalty-points.levels.max_points'),
                    'help_block' => [
                        'text' => trans('plugins/loyalty-points::loyalty-points.levels.max_points_help'),
                    ],
                    'attr' => [
                        'placeholder' => trans('plugins/loyalty-points::loyalty-points.levels.unlimited'),
                    ],
                ]
            )
            ->add(
                'earning_rate',
                NumberField::class,
                [
                    'label' => trans('plugins/loyalty-points::loyalty-points.levels.earning_rate'),
                    'required' => true,
                    'default_value' => 1,
                    'attr' => [
                        'step' => 0.01,
                        'placeholder' => 1.0,
                    ],
                    'help_block' => [
                        'text' => trans('plugins/loyalty-points::loyalty-points.levels.earning_rate_help'),
                    ],
                ]
            )
            ->add(
                'benefits',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.levels.benefits'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.levels.benefits_help'))
                    ->placeholder(trans('plugins/loyalty-points::loyalty-points.levels.benefits_placeholder'))
                    ->rows(4)
                    ->toArray()
            )
            ->add(
                'order',
                NumberField::class,
                [
                    'label' => trans('core/base::forms.order'),
                    'default_value' => 0,
                ]
            )
            ->add(
                'status',
                SelectField::class,
                StatusFieldOption::make()->toArray()
            )
            ->setBreakFieldPoint('status');
    }
}
