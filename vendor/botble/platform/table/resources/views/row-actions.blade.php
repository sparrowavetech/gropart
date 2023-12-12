@php
    /** @var Botble\Table\Abstracts\TableActionAbstract[] $actions */
    /** @var \Illuminate\Database\Eloquent\Model $model */
@endphp

<div class="table-actions">
    @foreach ($actions as $action)
        {{ $action->setItem($model) }}
    @endforeach
</div>
