<?php

namespace Botble\Ecommerce\Http\Controllers;

use Assets;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Tables\EnquiryTable;
use RvMedia;
use Throwable;

class EnquiryController extends BaseController
{
   
    public function __construct() {
        
    }

    /**
     * @param OrderTable $dataTable
     * @return Factory|View
     * @throws Throwable
     */
    public function index(EnquiryTable $dataTable)
    {
        page_title()->setTitle(trans('plugins/ecommerce::order.enquiry'));

        return $dataTable->renderTable();
    }
}
