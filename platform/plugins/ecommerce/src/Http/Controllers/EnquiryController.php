<?php

namespace Botble\Ecommerce\Http\Controllers;

use Assets;
use RvMedia;
use Throwable;
use Botble\Ecommerce\Tables\EnquiryTable;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Repositories\Interfaces\EnquiryInterface;

class EnquiryController extends BaseController
{
    /**
     * @var EnquiryInterface
     */
    protected $inquiryRepository;
    
    /**
     * @param EnquiryInterface $inquiryRepository
     */
    public function __construct(
        EnquiryInterface $inquiryRepository
    ) {
        $this->inquiryRepository = $inquiryRepository;
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
      /**
     * @param int $id
     * @return Factory|View
     */
    public function edit($id)
    {
        $enquiry = $this->inquiryRepository->findOrFail($id, ['product']);
        
        page_title()->setTitle(trans('plugins/ecommerce::order.edit_enquiry', ['code' => $enquiry->code]));

        return view('plugins/ecommerce::enquires.edit', compact('enquiry'));
    }

}
