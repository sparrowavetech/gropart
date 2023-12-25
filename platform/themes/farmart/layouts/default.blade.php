{!! Theme::partial('header') !!}

<div id="main-content">
    {!! Theme::partial('page-header', [
        'size' => Theme::get('containerSize', 'xxxl'),
        'withTitle' => Theme::get('withTitle', true),
    ]) !!}
    <div class="container-{{ Theme::get('containerSize', 'xxxl') }}">
        <div class="mb-5">
            {!! Theme::content() !!}
        </div>
    </div>
</div>

{!! Theme::partial('footer') !!}
