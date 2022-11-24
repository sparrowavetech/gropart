<?php

namespace Botble\Ecommerce\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
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
    public function index(EnquiryTable $dataTable)
    {
        page_title()->setTitle(trans('plugins/ecommerce::enquiry.name'));

        return $dataTable->renderTable();
    }
      /**
     * @param int $id
     * @return Factory|View
     */
    public function edit($id)
    {
        $enquiry = $this->enquiryRepository->findOrFail($id, ['product']);
        
        page_title()->setTitle(trans('plugins/ecommerce::enquiry.edit', ['code' => $enquiry->code]));

        return view('plugins/ecommerce::enquires.edit', compact('enquiry'));
    }
    /**
     * @param Request $request
     * @param int $id
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $enquiry = $this->enquiryRepository->findOrFail($id);
            $this->enquiryRepository->delete($enquiry);

            event(new DeletedContentEvent(BRAND_MODULE_SCREEN_NAME, $request, $enquiry));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     * @throws Exception
     */
    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $enquiry = $this->enquiryRepository->findOrFail($id);
            $this->enquiryRepository->delete($enquiry);
            event(new DeletedContentEvent(BRAND_MODULE_SCREEN_NAME, $request, $enquiry));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
