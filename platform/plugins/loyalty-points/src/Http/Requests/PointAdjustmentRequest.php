<?php

namespace Botble\LoyaltyPoints\Http\Requests;

use Botble\Support\Http\Requests\Request;

class PointAdjustmentRequest extends Request
{
    public function rules(): array
    {
        return [
            'member_id' => ['required'],
            'adjustment_type' => ['required', 'in:add,deduct'],
            'points' => ['required', 'integer', 'min:1'],
            'note' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => trans('plugins/loyalty-points::loyalty-points.errors.member_required'),
            'member_id.exists' => trans('plugins/loyalty-points::loyalty-points.errors.member_not_found'),
            'adjustment_type.required' => trans('plugins/loyalty-points::loyalty-points.errors.type_required'),
            'adjustment_type.in' => trans('plugins/loyalty-points::loyalty-points.errors.type_invalid'),
            'points.required' => trans('plugins/loyalty-points::loyalty-points.errors.points_required'),
            'points.integer' => trans('plugins/loyalty-points::loyalty-points.errors.points_invalid'),
            'points.min' => trans('plugins/loyalty-points::loyalty-points.errors.points_min'),
            'note.required' => trans('plugins/loyalty-points::loyalty-points.errors.note_required'),
        ];
    }
}
