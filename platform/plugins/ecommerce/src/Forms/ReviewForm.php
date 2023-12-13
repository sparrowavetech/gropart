<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\Fields\DatetimeField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Http\Requests\ReviewRequest;
use Botble\Ecommerce\Models\Review;
use Carbon\Carbon;

class ReviewForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->setupModel(new Review())
            ->setValidatorClass(ReviewRequest::class)
            ->add(
                'created_at',
                DatetimeField::class,
                DatePickerFieldOption::make()
                    ->label(trans('core/base::tables.created_at'))
                    ->value(Carbon::now())
                    ->toArray()
            )
            ->add(
                'product_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce::review.product'))
                    ->ajaxSearch()
                    ->ajaxUrl(route('reviews.ajax-search-products'))
                    ->toArray()
            )
            ->add(
                'customer_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce::review.customer'))
                    ->ajaxSearch()
                    ->ajaxUrl(route('reviews.ajax-search-customers'))
                    ->toArray()
            )
            ->add(
                'star',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce::review.star'))
                    ->choices(array_combine(range(1, 5), range(1, 5)))
                    ->selected(5)
                    ->toArray()
            )
            ->add(
                'comment',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/ecommerce::review.comment'))
                    ->toArray()
            );
    }
}
