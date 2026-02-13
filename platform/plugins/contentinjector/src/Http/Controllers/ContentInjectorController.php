<?php

namespace Botble\ContentInjector\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\ContentInjector\Http\Requests\ContentInjectorRequest;
use Botble\ContentInjector\Models\ContentInjector;
use Botble\Base\Http\Controllers\BaseController;
use Botble\ContentInjector\Tables\ContentInjectorTable;
use Botble\ContentInjector\Forms\ContentInjectorForm;

class ContentInjectorController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans(trans('plugins/contentinjector::contentinjector.name')), route('contentinjector.index'));
    }

    public function index(ContentInjectorTable $table)
    {
        $this->pageTitle(trans('plugins/contentinjector::contentinjector.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/contentinjector::contentinjector.create'));

        return ContentInjectorForm::create()->renderForm();
    }

    public function store(ContentInjectorRequest $request)
    {
        $form = ContentInjectorForm::create()->setRequest($request);

        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('contentinjector.index'))
            ->setNextUrl(route('contentinjector.edit', $form->getModel()->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit()
    {
        $contentInjector = ContentInjector::findOrFail(request()->route('contentinjector'));
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $contentInjector->name]));

        return ContentInjectorForm::createFromModel($contentInjector)->renderForm();
    }

    public function update(ContentInjectorRequest $request)
    {
        
        $contentInjector = ContentInjector::findOrFail(request()->route('contentinjector'));
        
        ContentInjectorForm::createFromModel($contentInjector)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('contentinjector.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ContentInjector $contentInjector)
    {
        return DeleteResourceAction::make($contentInjector);
    }
}
