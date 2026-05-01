<?php

namespace Botble\EcommerceWholesale\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\EcommerceWholesale\Forms\CustomerGroupForm;
use Botble\EcommerceWholesale\Http\Requests\CustomerGroupRequest;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Tables\CustomerGroupTable;

class CustomerGroupController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/ecommerce-wholesale::wholesale.name'), route('wholesale.customer-groups.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.customer_groups'));
    }

    public function index(CustomerGroupTable $table)
    {
        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.customer_groups'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.customer_group.create'));

        return CustomerGroupForm::create()->renderForm();
    }

    public function store(CustomerGroupRequest $request)
    {
        $form = CustomerGroupForm::create()->setRequest($request);
        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('wholesale.customer-groups.index')
            ->setNextRoute('wholesale.customer-groups.edit', $form->getModel()->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(CustomerGroup $customerGroup)
    {
        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.customer_group.edit', ['name' => $customerGroup->name]));

        return CustomerGroupForm::createFromModel($customerGroup)->renderForm();
    }

    public function update(CustomerGroup $customerGroup, CustomerGroupRequest $request)
    {
        CustomerGroupForm::createFromModel($customerGroup)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('wholesale.customer-groups.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(CustomerGroup $customerGroup)
    {
        return DeleteResourceAction::make($customerGroup);
    }
}
