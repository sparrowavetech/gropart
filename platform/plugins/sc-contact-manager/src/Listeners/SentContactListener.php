<?php

namespace Skillcraft\ContactManager\Listeners;

use Exception;
use Botble\Base\Facades\BaseHelper;
use Botble\Contact\Events\SentContactEvent;
use Illuminate\Support\Arr;
use Skillcraft\ContactManager\Enums\ContactTypeEnum;
use Skillcraft\ContactManager\Models\ContactManager;

class SentContactListener
{
    public function handle(SentContactEvent $event): void
    {
        try {
            if (setting('sc-cm-contact-form', config('plugins.contact-manager.general.contact_sources.contact_form', false))) {
                $name = explode(' ', $event->data->name);

                $contactManager = (new ContactManager())->query()->create([
                    'first_name' => Arr::first(array: $name, default: 'N/A'),
                    'last_name' => Arr::last(array: $name, default: 'N/A'),
                    'type' => ContactTypeEnum::LEAD,
                    'source' => 'contact-form'
                ]);

                if ($event->data->phone) {
                    $contactManager->phones()->create([
                        'phone' => $event->data->phone
                    ]);
                }

                if ($event->data->email) {
                    $contactManager->emails()->create([
                        'email' => $event->data->email
                    ]);
                }

                if ($event->data->address) {
                    $contactManager->addresses()->create([
                        'address' => $event->data->address
                    ]);
                }
            }
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }
}
