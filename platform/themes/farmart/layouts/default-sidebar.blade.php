{!! Theme::partial('header') !!}

<div id="main-content">
    {!! Theme::partial('page-header', ['size' => Theme::get('containerSize', 'xxxl'), 'withTitle' => Theme::get('withTitle', true)]) !!}
    <div class="container-{{ Theme::get('containerSize', 'xxxl') }}">
        <div class="row mt-5">
            <div class="col-sm-9">
                {!! Theme::content() !!}
            </div>
            <div class="col-sm-3">
                <div class="primary-sidebar">
                    <aside class="widget-area" id="default_page_sidebar">
                        {!! dynamic_sidebar('default_page_sidebar') !!}
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>

{!! Theme::partial('footer') !!}
