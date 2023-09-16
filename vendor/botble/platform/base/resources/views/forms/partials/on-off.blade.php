<div class="onoffswitch">
    <input
        name="{{ $name }}"
        type="hidden"
        value="0"
        {!! Html::attributes($attributes) !!}
    >
    <input
        class="onoffswitch-checkbox"
        id="{{ $name }}"
        name="{{ $name }}"
        type="checkbox"
        value="1"
        @if ($value) checked @endif
        {!! Html::attributes($attributes) !!}
    >
    <label
        class="onoffswitch-label"
        for="{{ $name }}"
    >
        <span class="onoffswitch-inner"></span>
        <span class="onoffswitch-switch"></span>
    </label>
</div>
