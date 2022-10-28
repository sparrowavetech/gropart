<nav aria-label="breadcrumb">
    <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
        @foreach ($crumbs = Theme::breadcrumb()->getCrumbs() as $i => $crumb)
            @if ($i != (count($crumbs) - 1))
                <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a href="{{ $crumb['url'] }}">
                        <span itemprop="item">{!! BaseHelper::clean($crumb['label']) !!}</span>
                        <span class="extra-breadcrumb-name"></span>
                    </a>
                    <meta itemprop="name" content="{{ $crumb['label'] }}" />
                    <meta itemprop="position" content="{{ $i + 1}}" />
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <span>{!! BaseHelper::clean($crumb['label']) !!}</span>
                    <meta itemprop="name" content="{{ $crumb['label'] }}" />
                    <meta itemprop="position" content="{{ $i + 1}}" />
                </li>
            @endif
        @endforeach
    </ol>
</nav>
