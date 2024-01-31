<?php

namespace Skillcraft\ContactManager\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EnumColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Skillcraft\ContactManager\Enums\ContactTypeEnum;
use Skillcraft\ContactManager\Models\ContactGroup;
use Skillcraft\ContactManager\Models\ContactManager;
use Skillcraft\ContactManager\Models\ContactTag;

class ContactManagerTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ContactManager::class)
            ->addActions([
                EditAction::make()
                    ->route('contact-manager.edit'),
                DeleteAction::make()
                    ->route('contact-manager.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (ContactManager $item) {
                if (! $this->hasPermission('contact-manager.edit')) {
                    return BaseHelper::clean($item->name);
                }

                return Html::link(route('contact-manager.edit', $item->getKey()), BaseHelper::clean($item->name));
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
               'id',
               'first_name',
               'last_name',
               'business_name',
               'type',
               'source',
               'created_at',
           ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('first_name'),
            Column::make('last_name'),
            Column::make('business_name'),
            EnumColumn::make('type'),
            Column::make('source'),
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('contact-manager.create'), 'contact-manager.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('contact-manager.destroy'),
        ];
    }

    public function getFilters(): array
    {
        return [
            'contact_manager.first_name' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.first_name'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
            'contact_manager.last_name' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.last_name'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
            'contact_manager.group_id' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.group'),
                'type' => 'select',
                'validate' => 'required|integer',
                'choices' => ['0' => trans('plugins/contacts-manager::contacts-manager.no_group')] + (new ContactGroup())->query()->pluck('name', 'id')->toArray(),
            ],

            'contact_addresses.address' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.address'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
            'contact_addresses.address2' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.address2'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
            'contact_addresses.city' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.city'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
            'contact_addresses.state' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.state'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
            'contact_addresses.postalcode' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.postalcode'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
            'contact_phones.phone' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.phone'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
            'contact_emails.email' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.email'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
            'contact_tags' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.tag'),
                'type' => 'select-search',
                'validate' => 'required|integer',
                'choices' => (new ContactTag())
                    ->query()
                    ->pluck('name', 'id')
                    ->toArray(),
            ],
            'contact_manager.type' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.type'),
                'type' => 'select-search',
                'validate' => 'required|in:' . implode(',', ContactTypeEnum::values()),
                'choices' => ContactTypeEnum::labels(),
            ],
            'contact_manager.source' => [
                'title' => trans('plugins/sc-contact-manager::contact-manager.forms.source'),
                'type' => 'text',
                'validate' => 'required|string',
            ],
        ];
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }

    public function applyFilterCondition($query, string $key, string $operator, ?string $value)
    {

        if (! empty($value)) {
            if ($operator === 'like') {
                $value = '%' . $value . '%';
            }

            $query = match ($key) {
                'contact_addresses.state', 'contact_addresses.address', 'contact_addresses.address2', 'contact_addresses.postalcode', 'contact_addresses.city' => $query->HasAddressInfo(
                    $key,
                    $operator,
                    $value
                ),
                'contact_emails.email' => $query->hasEmailInfo($key, $operator, $value),
                'contact_phones.phone' => $query->hasPhoneInfo($key, $operator, $value),
                'contact_tags' => $query->hasContactTag($operator, $value),
                default => parent::applyFilterCondition($query, $key, $operator, $value),
            };
        }

        return $query;
    }
}
