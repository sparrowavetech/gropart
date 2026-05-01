<?php

namespace Botble\EcommerceWholesale\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Http\Requests\ApproveApplicationRequest;
use Botble\EcommerceWholesale\Http\Requests\RejectApplicationRequest;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Botble\EcommerceWholesale\Services\ApplicationApprovalService;
use Botble\EcommerceWholesale\Tables\ApplicationTable;

class ApplicationController extends BaseController
{
    public function __construct(
        protected ApplicationApprovalService $approvalService
    ) {
        $this->breadcrumb()
            ->add(trans('plugins/ecommerce-wholesale::wholesale.name'), route('wholesale.customer-groups.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.applications'), route('wholesale.applications.index'));
    }

    public function index(ApplicationTable $table)
    {
        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.applications'));

        return $table->renderTable();
    }

    public function edit(int|string $id)
    {
        $application = WholesaleApplication::query()
            ->with(['reviewer', 'assignedGroup', 'customer'])
            ->findOrFail($id);

        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.application.review'));

        $groups = CustomerGroup::query()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->pluck('name', 'id');

        return view('plugins/ecommerce-wholesale::admin.application-review', compact('application', 'groups'));
    }

    public function approve(int|string $id, ApproveApplicationRequest $request)
    {
        $application = WholesaleApplication::query()->findOrFail($id);

        if (! $application->isPending()) {
            return $this->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.messages.application_already_processed'));
        }

        $group = CustomerGroup::query()->findOrFail($request->input('customer_group_id'));

        $this->approvalService->approve($application, $group, auth()->user());

        return $this->httpResponse()
            ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.messages.application_approved'))
            ->setNextUrl(route('wholesale.applications.index'));
    }

    public function reject(int|string $id, RejectApplicationRequest $request)
    {
        $application = WholesaleApplication::query()->findOrFail($id);

        if (! $application->isPending()) {
            return $this->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.messages.application_already_processed'));
        }

        $this->approvalService->reject(
            $application,
            $request->input('rejection_reason'),
            auth()->user()
        );

        return $this->httpResponse()
            ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.messages.application_rejected'))
            ->setNextUrl(route('wholesale.applications.index'));
    }

    public function destroy(int|string $id)
    {
        $application = WholesaleApplication::query()->findOrFail($id);

        if ($application->isApproved()) {
            return $this->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.messages.cannot_delete_approved'));
        }

        return DeleteResourceAction::make($application);
    }
}
