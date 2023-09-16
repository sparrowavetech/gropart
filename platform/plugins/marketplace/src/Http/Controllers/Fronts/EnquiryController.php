<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Exception;
use Throwable;
use EmailHandler;
use EcommerceHelper;
use MarketplaceHelper;
use Botble\Marketplace\Tables\EnquiryTable;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Repositories\Interfaces\EnquiryInterface;

class EnquiryController extends BaseController
{
    /**
     * @var EnquiryInterface
     */
    protected $enquiryRepository;
    
    /**
     * @param EnquiryInterface $enquiryRepository
     */
    public function __construct(
        EnquiryInterface $enquiryRepository
    ) {
        $this->enquiryRepository = $enquiryRepository;
    }

    /**
     * @param OrderTable $dataTable
     * @return Factory|View
     * @throws Throwable
     */
    public function index(EnquiryTable $table)
    {
        page_title()->setTitle(__('Enquires'));
        $enquires = auth('customer')->user()->store->enquires()->get();
        return $table->render(MarketplaceHelper::viewPath('dashboard.table.base'), compact('enquires'));
    }
      /**
     * @param int $id
     * @return Factory|View
     */
    public function edit($id)
    {
        $enquiry = $this->enquiryRepository
        ->getModel()
        ->where('id', $id)
        ->with(['product'])
        ->firstOrFail();

        page_title()->setTitle(trans('plugins/ecommerce::enquiry.edit', ['code' => $enquiry->code]));

        return MarketplaceHelper::view('dashboard.enquires.edit', compact('enquiry'));
    }
   
}