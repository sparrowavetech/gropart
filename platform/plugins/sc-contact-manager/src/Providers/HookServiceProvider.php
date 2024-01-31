<?php

namespace Skillcraft\ContactManager\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\CustomField\Facades\CustomField;
use Illuminate\Support\Facades\DB;
use Skillcraft\ContactManager\Enums\ContactTypeEnum;
use Skillcraft\ContactManager\Models\ContactManager;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerModules();

        $this->filters();
    }

    private function filters(): void
    {
        add_filter(CONTACT_MANAGER_QUERY_PLUCK_FILTER, function (ContactTypeEnum|string $type, array $conditions = []) {
            if (is_string($type)) {
                $type = (new ContactTypeEnum())->make($type);
            }

            $query = (new ContactManager())
                ->query()
                ->hasType($type);

            if (sizeof($conditions) > 0) {
                $query->where($conditions);
            }

            return $query->pluck(
                DB::raw(
                    'CONCAT(
                        COALESCE(NULLIF(contact_manager.business_name, ""), CONCAT(contact_manager.first_name, " ", contact_manager.last_name))
                    ) AS name'
                ),
                'contact_manager.id'
            );
        }, 100, 1);
    }

    private function registerModules(): void
    {
        if (defined('CUSTOM_FIELD_MODULE_SCREEN_NAME')) {
            CustomField::registerModule(ContactManager::class)
                ->registerRule('basic', trans('Contact'), ContactManager::class, function () {
                    return (new ContactManager())
                        ->query()
                        ->pluck(DB::raw("CONCAT(first_name, ' ', last_name)"), 'id')
                        ->toArray();
                })
                ->expandRule('other', trans('plugins/custom-field::rules.model_name'), 'model_name', function () {
                    return [
                        ContactManager::class => trans('Contacts'),
                    ];
                });
        }
    }
}
