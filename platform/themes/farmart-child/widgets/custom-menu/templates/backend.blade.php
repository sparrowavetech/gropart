<div class="form-group">
    <label for="widget-name">{{ __('Name') }}</label>
    <input
        class="form-control"
        id="widget-name"
        name="name"
        type="text"
        value="{{ $config['name'] }}"
    >
</div>
<div class="form-group">
    <label for="widget_menu">{{ __('Select menu') }}</label>
    {!! Form::customSelect(
        'menu_id',
        app(\Botble\Menu\Repositories\Interfaces\MenuInterface::class)->pluck('name', 'slug'),
        $config['menu_id'],
        ['class' => 'form-control select-full'],
    ) !!}
</div>
