<?php

namespace Skillcraft\ContactManager\Http\Controllers;

use Exception;
use Botble\ACL\Models\User;
use Illuminate\Http\Request;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\Auth;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Skillcraft\ContactManager\Models\ContactTag;
use Skillcraft\ContactManager\Forms\ContactTagForm;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Skillcraft\ContactManager\Tables\ContactTagTable;
use Skillcraft\ContactManager\Http\Requests\ContactTagRequest;

class ContactTagController extends BaseController
{
    public function index(ContactTagTable $table)
    {
        PageTitle::setTitle(trans('plugins/sc-contact-manager::contact-manager.models.tag.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/sc-contact-manager::contact-manager.models.tag.create'));

        return $formBuilder->create(ContactTagForm::class)->renderForm();
    }

    public function store(ContactTagRequest $request, BaseHttpResponse $response)
    {
        $request->merge([
            'author_id'   => Auth::check() ? Auth::id() : 0,
            'author_type' => User::class,
        ]);
        
        $contactTag = ContactTag::query()->create($request->input());

        event(new CreatedContentEvent(CONTACT_MANAGER_TAG_MODULE_SCREEN_NAME, $request, $contactTag));

        return $response
            ->setPreviousUrl(route('contact-tag.index'))
            ->setNextUrl(route('contact-tag.edit', $contactTag->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ContactTag $contactTag, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $contactTag->name]));

        return $formBuilder->create(ContactTagForm::class, ['model' => $contactTag])->renderForm();
    }

    public function update(ContactTag $contactTag, ContactTagRequest $request, BaseHttpResponse $response)
    {
        $contactTag->fill($request->input());

        $contactTag->save();

        event(new UpdatedContentEvent(CONTACT_MANAGER_TAG_MODULE_SCREEN_NAME, $request, $contactTag));

        return $response
            ->setPreviousUrl(route('contact-tag.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ContactTag $contactTag, Request $request, BaseHttpResponse $response)
    {
        try {
            $contactTag->delete();

            event(new DeletedContentEvent(CONTACT_MANAGER_TAG_MODULE_SCREEN_NAME, $request, $contactTag));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getAllTags()
    {
        return (new ContactTag())->query()->pluck('name');
    }
}
