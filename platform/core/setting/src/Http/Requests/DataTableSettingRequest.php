<?php

namespace Botble\Setting\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class DataTableSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'datatables_default_show_column_visibility' => $onOffRule = new OnOffRule(),
            'datatables_default_show_export_button' => $onOffRule,
        ];
    }
}
