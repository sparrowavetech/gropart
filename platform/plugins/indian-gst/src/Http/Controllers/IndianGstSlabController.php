<?php

namespace SparroWave\IndianGst\Http\Controllers;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Tax;
use Illuminate\Http\Request;
use SparroWave\IndianGst\Tables\TaxSlabProductsTable;

class IndianGstSlabController extends BaseController
{
    public function index()
    {
        PageTitle::setTitle(__('Indian GST Tax Slabs & Mapped Products'));

        $taxes = Tax::query()
            ->orderBy('priority')
            ->get();

        $taxStats = [];
        $totalMappedProducts = 0;
        foreach ($taxes as $tax) {
            $count = \DB::table('ec_tax_products')->where('tax_id', $tax->id)->count();
            $taxStats[$tax->id] = $count;
            $totalMappedProducts += $count;
        }

        return view('plugins/indian-gst::slabs.index', compact('taxes', 'taxStats', 'totalMappedProducts'));
    }

    public function products(int $taxId, TaxSlabProductsTable $table)
    {
        $table->setTaxId($taxId);

        if (request()->wantsJson() || request()->ajax() || request()->isMethod('POST')) {
            return $table->ajax();
        }

        $tax = Tax::query()->findOrFail($taxId);
        $count = \DB::table('ec_tax_products')->where('tax_id', $tax->id)->count();

        PageTitle::setTitle(__('Products in :tax Slab (:rate%) - :count Products', [
            'tax' => $tax->title,
            'rate' => $tax->percentage,
            'count' => number_format($count),
        ]));

        $ajaxUrl = route('indian-gst.slabs.products', $taxId, false);

        return $table
            ->setAjaxUrl($ajaxUrl)
            ->renderTable();
    }
}
