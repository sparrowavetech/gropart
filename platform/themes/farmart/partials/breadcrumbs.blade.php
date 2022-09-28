<nav aria-label="breadcrumb">
    <ol class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
        @foreach ($crumbs = Theme::breadcrumb()->getCrumbs() as $i => $crumb)
            @if ($i != (count($crumbs) - 1))
                <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                    <a href="{{ $crumb['url'] }}" itemprop="item">
                        {!! BaseHelper::clean($crumb['label']) !!}
                    </a>
                    <meta itemprop="name" content="{{ $crumb['label'] }}" />
                    <meta itemprop="position" content="{{ $i + 1}}" />
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                    {!! BaseHelper::clean($crumb['label']) !!}
                    <meta itemprop="name" content="{{ $crumb['label'] }}" />
                    <meta itemprop="position" content="{{ $i + 1}}" />
                </li>
            @endif
        @endforeach
    </ol>
</nav>
