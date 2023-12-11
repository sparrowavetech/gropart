<?php

namespace Botble\Marketplace\Forms;

use Botble\Marketplace\Forms\Fields\CustomEditorField;
use Botble\Marketplace\Http\Requests\Fronts\VendorStoreRequest;

class VendorStoreForm extends StoreForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(VendorStoreRequest::class)
            ->addCustomField('customEditor', CustomEditorField::class)
            ->modify('content', 'customEditor')
            ->remove(['status', 'customer_id']);
    }
}
