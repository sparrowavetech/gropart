<?php

namespace SparroWave\IndianGst\Hooks;

use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Marketplace\Models\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class MarketplaceGstHookListener
{
    public static function addStoreGstFields(FormAbstract $form, ?Model $data = null): FormAbstract
    {
        if (! $data instanceof Store) {
            return $form;
        }

        // 1. Phone & Seller GSTIN (Row: 50% - 50%)
        if ($form->has('phone')) {
            $form->getField('phone')?->setOption('colspan', 3);
        }

        $form->addAfter(
            'phone',
            'gstin',
            TextField::class,
            TextFieldOption::make()
                ->label(__('Seller GSTIN'))
                ->placeholder(__('Enter Seller GSTIN (e.g. 08ADEPA5871A1Z0)'))
                ->value($data->gstin ?: $data->tax_id)
                ->colspan(3)
                ->toArray()
        );

        // 2. Location Fields: State/City (Row 1), Address/Zipcode (Row 2: 50% - 50%)
        if ($form->has('state')) {
            $form->getField('state')?->setOption('colspan', 3);
        }
        if ($form->has('city')) {
            $form->getField('city')?->setOption('colspan', 3);
        }
        if ($form->has('address')) {
            $form->getField('address')?->setOption('colspan', 3);
        }
        if ($form->has('zip_code')) {
            $form->getField('zip_code')?->setOption('colspan', 3);
        }

        // 3. Four Images (2 rows x 2 columns: 50% width each)
        if ($form->has('logo')) {
            $form->getField('logo')?->setOption('colspan', 3);
        }
        if ($form->has('logo_square')) {
            $form->getField('logo_square')?->setOption('colspan', 3);
        }
        if ($form->has('cover_image')) {
            $form->getField('cover_image')?->setOption('colspan', 3);
        }
        if ($form->has('background')) {
            $form->getField('background')?->setOption('colspan', 3);
        }

        // 4. Status & Store Owner (Row: 50% - 50%)
        if ($form->has('status')) {
            $form->getField('status')?->setOption('colspan', 3);
        }
        if ($form->has('customer_id')) {
            $form->getField('customer_id')?->setOption('colspan', 3);
        }

        // 5. Highlighted Vacation Box
        $vacationModeValue = (bool) old('vacation_mode', $data->getMetaData('vacation_mode', true) ?: $data->vacation_mode);
        $vacationMessageValue = (string) old('vacation_message', $data->getMetaData('vacation_message', true) ?: $data->vacation_message);

        if ($form->has('vacation_mode')) {
            $form->remove('vacation_mode');
        }
        if ($form->has('vacation_message')) {
            $form->remove('vacation_message');
        }

        $form->add(
            'vacation_box_section',
            HtmlField::class,
            [
                'colspan' => 6,
                'html' => view('plugins/indian-gst::stores.vacation-box', compact('vacationModeValue', 'vacationMessageValue'))->render(),
            ]
        );

        // 6. Highlighted Vendor Managed Shipping Box
        $vendorShippingValue = (bool) old('vendor_managed_shipping', $data->vendor_managed_shipping);
        $form->add(
            'vendor_shipping_box_section',
            HtmlField::class,
            [
                'colspan' => 6,
                'html' => view('plugins/indian-gst::stores.vendor-shipping-box', compact('vendorShippingValue'))->render(),
            ]
        );

        return $form;
    }

    public static function saveStoreGstData(string $screen, Request $request, $model): void
    {
        if (! $model instanceof Store) {
            return;
        }

        if ($request->has('gstin')) {
            $model->gstin = trim((string) $request->input('gstin'));
        }

        if ($request->has('vendor_managed_shipping')) {
            $model->vendor_managed_shipping = (bool) $request->input('vendor_managed_shipping');
        }

        if ($request->has('vacation_mode')) {
            $model->vacation_mode = (bool) $request->input('vacation_mode');
            \Botble\Base\Facades\MetaBox::saveMetaBoxData($model, 'vacation_mode', (bool) $request->input('vacation_mode'));
        }

        if ($request->has('vacation_message')) {
            $model->vacation_message = (string) $request->input('vacation_message');
            \Botble\Base\Facades\MetaBox::saveMetaBoxData($model, 'vacation_message', (string) $request->input('vacation_message'));
        }

        $model->saveQuietly();
    }
}
