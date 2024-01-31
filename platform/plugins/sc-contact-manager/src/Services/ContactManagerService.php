<?php

namespace Skillcraft\ContactManager\Services;

use Botble\Base\Models\BaseModel;
use Illuminate\Support\Arr;
use Skillcraft\ContactManager\Enums\ContactDataTypeEnum;
use Skillcraft\ContactManager\Models\ContactAddress;
use Skillcraft\ContactManager\Models\ContactEmail;
use Skillcraft\ContactManager\Models\ContactManager;
use Skillcraft\ContactManager\Models\ContactPhone;

final class ContactManagerService
{
    public function executeUpdateContactInfo(ContactManager $contact, array $data = []): void
    {
        $condition = ['contact_id' => $contact->id];

        if (Arr::exists($data, 'addresses')) {
            $contact->addresses()->delete();

            foreach (Arr::get($data, 'addresses') as $key => $value) {
                foreach ($value as $key => $value2) {
                    if ($value2['key'] == 'address') {
                        $address_value[$value2['key']] = Arr::get($value2, 'value');
                    }

                    if ($value2['key'] == 'address2') {
                        $address_value[$value2['key']] = Arr::get($value2, 'value');
                    }

                    if ($value2['key'] == 'city') {
                        $address_value[$value2['key']] = Arr::get($value2, 'value');
                    }

                    if ($value2['key'] == 'state') {
                        $address_value[$value2['key']] = Arr::get($value2, 'value');
                    }

                    if ($value2['key'] == 'postalcode') {
                        $address_value[$value2['key']] = Arr::get($value2, 'value');
                    }
                    if ($value2['key'] == 'type') {
                        $address_value[$value2['key']] = Arr::get($value2, 'value', ContactDataTypeEnum::HOME);
                    }
                }

                (new ContactAddress())->query()->create($condition + $address_value);
            }
        }

        if (Arr::exists($data, 'emails')) {
            $contact->emails()->delete();

            foreach (Arr::get($data, 'emails') as $value) {
                foreach ($value as $value2) {
                    if ($value2['key'] == 'email') {
                        $email_value[$value2['key']] = Arr::get($value2, 'value');
                    }

                    if ($value2['key'] == 'type') {
                        $email_value[$value2['key']] = Arr::get($value2, 'value', ContactDataTypeEnum::HOME);
                    }
                }

                (new ContactEmail())->query()->create($condition + $email_value);
            }
        }

        if (Arr::exists($data, 'phones')) {
            $contact->phones()->delete();

            foreach (Arr::get($data, 'phones') as $value) {
                foreach ($value as $value2) {
                    if ($value2['key'] == 'phone') {
                        $phone_value[$value2['key']] = Arr::get($value2, 'value');
                    }

                    if ($value2['key'] == 'type') {
                        $phone_value[$value2['key']] = Arr::get($value2, 'value', ContactDataTypeEnum::HOME);
                    }
                }

                (new ContactPhone())->query()->create($condition + $phone_value);
            }
        }
    }

    public static function getEmailRepeaterData(?BaseModel $baseModel): string|array
    {
        if ($baseModel && $baseModel->id && $baseModel->emails()->count() > 0) {
            foreach ($baseModel->emails()->get() as $email) {
                $repeaterValue[] = [
                    [
                        'key' => 'email',
                        'value' => $email->email,
                    ],
                    [
                        'key' => 'type',
                        'value' => $email->type,
                    ],
                ];
            }
        } else {
            $repeaterValue = json_encode([[]]);
        }

        return $repeaterValue;
    }

    public static function getPhoneRepeaterData(?BaseModel $baseModel): string|array
    {
        if ($baseModel && $baseModel->id && $baseModel->phones()->count() > 0) {
            foreach ($baseModel->phones()->get() as $phone) {
                $repeaterValue[] = [
                    [
                        'key' => 'phone',
                        'value' => $phone->phone,
                    ],
                    [
                        'key' => 'type',
                        'value' => $phone->type,
                    ],
                ];
            }
        } else {
            $repeaterValue = json_encode([[]]);
        }

        return $repeaterValue;
    }

    public static function getAddressRepeaterData(?BaseModel $baseModel): string|array
    {
        if ($baseModel && $baseModel->id && $baseModel->addresses()->count() > 0) {
            foreach ($baseModel->addresses()->get() as $address) {
                $repeaterValue[] = [
                    [
                        'key' => 'address',
                        'value' => $address->address,
                    ],
                    [
                        'key' => 'address2',
                        'value' => $address->address2,
                    ],
                    [
                        'key' => 'state',
                        'value' => $address->state,
                    ],
                    [
                        'key' => 'city',
                        'value' => $address->city,
                    ],
                    [
                        'key' => 'postalcode',
                        'value' => $address->postalcode,
                    ],
                    [
                        'key' => 'type',
                        'value' => $address->type,
                    ],
                ];
            }
        } else {
            $repeaterValue = json_encode([[]]);
        }

        return $repeaterValue;
    }
}
