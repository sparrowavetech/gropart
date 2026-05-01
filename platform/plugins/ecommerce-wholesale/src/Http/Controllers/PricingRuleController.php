<?php

namespace Botble\EcommerceWholesale\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\EcommerceWholesale\Forms\PricingRuleForm;
use Botble\EcommerceWholesale\Http\Requests\PricingRuleRequest;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Botble\EcommerceWholesale\Tables\PricingRuleTable;

class PricingRuleController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/ecommerce-wholesale::wholesale.name'), route('wholesale.customer-groups.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.pricing_rules'));
    }

    public function index(PricingRuleTable $table)
    {
        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.pricing_rules'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.create'));

        return PricingRuleForm::create()->renderForm();
    }

    public function store(PricingRuleRequest $request)
    {
        $form = PricingRuleForm::create()->setRequest($request);
        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('wholesale.pricing-rules.index')
            ->setNextRoute('wholesale.pricing-rules.edit', $form->getModel()->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(GroupPricingRule $pricingRule)
    {
        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.edit'));

        return PricingRuleForm::createFromModel($pricingRule)->renderForm();
    }

    public function update(GroupPricingRule $pricingRule, PricingRuleRequest $request)
    {
        PricingRuleForm::createFromModel($pricingRule)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('wholesale.pricing-rules.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(GroupPricingRule $pricingRule)
    {
        return DeleteResourceAction::make($pricingRule);
    }
}
