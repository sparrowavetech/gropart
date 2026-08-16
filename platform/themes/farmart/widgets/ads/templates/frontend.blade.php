@if (is_plugin_active('ads'))
    @php
        $images = collect(Arr::wrap($config['ads_key'] ?? null))
            ->filter(fn ($key) => is_string($key) || is_numeric($key))
            ->map(fn ($key) => display_ads_advanced((string) $key, ['class' => 'd-flex justify-content-center']))
            ->filter();
    @endphp

    @if ($images->isNotEmpty())
        <div
            class="lazyload"
            @if ($config['background']) data-bg="{{ RvMedia::getImageUrl($config['background']) }}" @endif
        >
            @php
                $size = 'xxxl';
                switch ($config['size']) {
                    case 'large':
                        $size = 'xxl';
                        break;
                    case 'medium':
                        $size = 'lg';
                        break;
                }
            @endphp
            <div class="container-{{ $size }}">
                <div class="row">
                    @foreach ($images as $image)
                        <div class="my-5">
                            {!! $image !!}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endif
