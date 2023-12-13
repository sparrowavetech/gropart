<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Ecommerce\Forms\ProductAttributeSetForm;
use Botble\Ecommerce\Http\Requests\ProductAttributeSetsRequest;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Services\ProductAttributes\StoreAttributeSetService;
use Botble\Ecommerce\Tables\ProductAttributeSetsTable;
use Exception;
use Illuminate\Http\Request;

class ProductAttributeSetsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this
            ->breadcrumb()
            ->add(trans('plugins/ecommerce::product-attributes.name'), route('product-attribute-sets.index'));
    }

    public function index(ProductAttributeSetsTable $dataTable)
    {
        $this->pageTitle(trans('plugins/ecommerce::product-attributes.name'));

        return $dataTable->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/ecommerce::product-attributes.create'));

        Assets::addScripts(['spectrum', 'jquery-ui'])
            ->addStyles(['spectrum'])
            ->addStylesDirectly([
                asset('vendor/core/plugins/ecommerce/css/ecommerce-product-attributes.css'),
            ])
            ->addScriptsDirectly([
                asset('vendor/core/plugins/ecommerce/js/ecommerce-product-attributes.js'),
            ]);

        return ProductAttributeSetForm::create()->renderForm();
    }

    public function store(ProductAttributeSetsRequest $request, StoreAttributeSetService $service)
    {
        $productAttributeSet = $service->execute($request, new ProductAttributeSet());

        if ($request->has('categories')) {
            $productAttributeSet->categories()->sync((array) $request->input('categories', []));
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('product-attribute-sets.index'))
            ->setNextUrl(route('product-attribute-sets.edit', $productAttributeSet->getKey()))
            ->withCreatedSuccessMessage();
    }

    public function edit(ProductAttributeSet $productAttributeSet)
    {
        $this->pageTitle(trans('plugins/ecommerce::product-attributes.edit'));

        Assets::addScripts(['spectrum', 'jquery-ui'])
            ->addStyles(['spectrum'])
            ->addStylesDirectly([
                'vendor/core/plugins/ecommerce/css/ecommerce-product-attributes.css',
            ])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/js/ecommerce-product-attributes.js',
            ]);

        return ProductAttributeSetForm::createFromModel($productAttributeSet)
            ->renderForm();
    }

    public function update(
        ProductAttributeSet $productAttributeSet,
        ProductAttributeSetsRequest $request,
        StoreAttributeSetService $service,
    ) {
        $service->execute($request, $productAttributeSet);

        $productAttributeSet->categories()->sync((array) $request->input('categories', []));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('product-attribute-sets.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(ProductAttributeSet $productAttributeSet, Request $request)
    {
        try {
            $productAttributeSet->delete();

            event(new DeletedContentEvent(PRODUCT_ATTRIBUTE_SETS_MODULE_SCREEN_NAME, $request, $productAttributeSet));

            return $this
                ->httpResponse()
                ->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
