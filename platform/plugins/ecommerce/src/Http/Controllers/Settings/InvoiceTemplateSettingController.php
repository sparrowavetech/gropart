<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Http\Requests\Settings\InvoiceTemplateSettingRequest;
use Botble\Ecommerce\Supports\InvoiceHelper;
use Illuminate\Contracts\View\View;

class InvoiceTemplateSettingController extends BaseController
{
    public function edit(InvoiceHelper $invoiceHelper): View
    {
        Assets::addStylesDirectly([
            'vendor/core/core/base/libraries/codemirror/lib/codemirror.css',
            'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.css',
        ])
        ->addScriptsDirectly([
            'vendor/core/core/base/libraries/codemirror/lib/codemirror.js',
            'vendor/core/core/base/libraries/codemirror/lib/css.js',
            'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.js',
            'vendor/core/core/base/libraries/codemirror/addon/hint/anyword-hint.js',
            'vendor/core/core/base/libraries/codemirror/addon/hint/css-hint.js',
        ]);

        $content = $invoiceHelper->getInvoiceTemplate();
        $variables = $invoiceHelper->getVariables();

        return view('plugins/ecommerce::invoice-template.settings', compact('content', 'variables'));
    }

    public function update(InvoiceTemplateSettingRequest $request)
    {
        BaseHelper::saveFileData(storage_path('app/templates/invoice.tpl'), $request->input('content'), false);

        return $this
            ->httpResponse()
            ->withUpdatedSuccessMessage();
    }
}
