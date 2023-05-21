<div class="col-xl-2">
    <div class="col mb-2">
        <div class="widget widget-custom-menu">
            <h5 class="fw-bold widget-title mb-2">{!! BaseHelper::clean($config['name']) !!}</h5>
            {!!
                Menu::generateMenu(['slug' => $config['menu_id'], 'options' => ['class' => 'ps-0'], 'view' => 'menu-default'])
            !!}
        </div>
    </div>
</div>

