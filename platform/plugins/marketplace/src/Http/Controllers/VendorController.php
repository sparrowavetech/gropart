<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Marketplace\Tables\VendorTable;

class VendorController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->breadcrumb();
    }

    public function index(VendorTable $table)
    {
        $this->pageTitle(trans('plugins/marketplace::marketplace.vendors'));

        return $table->renderTable();
    }
}
