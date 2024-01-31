<?php

namespace Skillcraft\ContactManager\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Exception;
use Illuminate\Http\Request;
use Skillcraft\ContactManager\Forms\ContactManagerForm;
use Skillcraft\ContactManager\Http\Requests\ContactManagerRequest;
use Skillcraft\ContactManager\Models\ContactManager;
use Skillcraft\ContactManager\Services\ContactManagerService;
use Skillcraft\ContactManager\Services\StoreContactTagService;
use Skillcraft\ContactManager\Tables\ContactManagerTable;

class ContactManagerController extends BaseController
{
    public function index(ContactManagerTable $table)
    {
        PageTitle::setTitle(trans('plugins/sc-contact-manager::contact-manager.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/sc-contact-manager::contact-manager.create'));

        return $formBuilder->create(ContactManagerForm::class)->renderForm();
    }

    public function store(ContactManagerRequest $request, BaseHttpResponse $response, StoreContactTagService $tagService)
    {
        $contactManager = ContactManager::query()->create($request->validated());

        $tagService->execute($request, $contactManager);

        (new ContactManagerService())
            ->executeUpdateContactInfo($contactManager, $request->validated());

        event(new CreatedContentEvent(CONTACT_MANAGER_MODULE_SCREEN_NAME, $request, $contactManager));

        return $response
            ->setPreviousUrl(route('contact-manager.index'))
            ->setNextUrl(route('contact-manager.edit', $contactManager->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ContactManager $contactManager, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $contactManager->name]));

        return $formBuilder->create(ContactManagerForm::class, ['model' => $contactManager])->renderForm();
    }

    public function update(ContactManager $contactManager, ContactManagerRequest $request, BaseHttpResponse $response, StoreContactTagService $tagService)
    {
        $contactManager->fill($request->validated());

        $contactManager->save();

        $tagService->execute($request, $contactManager);

        (new ContactManagerService())
            ->executeUpdateContactInfo($contactManager, $request->validated());

        event(new UpdatedContentEvent(CONTACT_MANAGER_MODULE_SCREEN_NAME, $request, $contactManager));

        return $response
            ->setPreviousUrl(route('contact-manager.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ContactManager $contactManager, Request $request, BaseHttpResponse $response)
    {
        try {
            $contactManager->delete();

            event(new DeletedContentEvent(CONTACT_MANAGER_MODULE_SCREEN_NAME, $request, $contactManager));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
