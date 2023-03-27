<div class="page-header">
    @if (!Theme::get('breadcrumbRendered', false))
        <div class="page-breadcrumbs">
            <div class="container-{{ $size ?? 'xxxl' }}">
                {!! Theme::partial('breadcrumbs') !!}
            </div>
        </div>
        @php
            Theme::set('breadcrumbRendered', true);
        @endphp
    @endif
    @if (!empty($withTitle) && !Theme::get('titleRendered', false))
        <div class="page-title text-center">
            <div class="container-fluid pt-5">
                <h1>{{ $title ?? SeoHelper::getTitle() }}</h1>
            </div>
        </div>
        @php
            Theme::set('titleRendered', true);
        @endphp
    @endif
</div>
