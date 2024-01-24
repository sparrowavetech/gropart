<?php

namespace Skillcraft\ContactManager\Http\Controllers;

use Skillcraft\ContactManager\Http\Requests\ContactGroupRequest;
use Skillcraft\ContactManager\Models\ContactGroup;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Skillcraft\ContactManager\Tables\ContactGroupTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Skillcraft\ContactManager\Forms\ContactGroupForm;
use Botble\Base\Forms\FormBuilder;

class ContactGroupController extends BaseController
{
    public function index(ContactGroupTable $table)
    {
        PageTitle::setTitle(trans('plugins/sc-contact-manager::contact-manager.models.group.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/sc-contact-manager::contact-manager.models.create'));

        return $formBuilder->create(ContactGroupForm::class)->renderForm();
    }

    public function store(ContactGroupRequest $request, BaseHttpResponse $response)
    {
        $contactGroup = ContactGroup::query()->create($request->input());

        event(new CreatedContentEvent(CONTACT_MANAGER_GROUP_MODULE_SCREEN_NAME, $request, $contactGroup));

        return $response
            ->setPreviousUrl(route('contact-group.index'))
            ->setNextUrl(route('contact-group.edit', $contactGroup->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ContactGroup $contactGroup, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $contactGroup->name]));

        return $formBuilder->create(ContactGroupForm::class, ['model' => $contactGroup])->renderForm();
    }

    public function update(ContactGroup $contactGroup, ContactGroupRequest $request, BaseHttpResponse $response)
    {
        $contactGroup->fill($request->input());

        $contactGroup->save();

        event(new UpdatedContentEvent(CONTACT_MANAGER_GROUP_MODULE_SCREEN_NAME, $request, $contactGroup));

        return $response
            ->setPreviousUrl(route('contact-group.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ContactGroup $contactGroup, Request $request, BaseHttpResponse $response)
    {
        try {
            $contactGroup->delete();

            event(new DeletedContentEvent(CONTACT_MANAGER_GROUP_MODULE_SCREEN_NAME, $request, $contactGroup));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
