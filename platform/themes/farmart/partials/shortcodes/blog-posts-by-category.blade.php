@php
$posts = get_posts_by_category(['category_id'=>$shortcode->category_id]);
$categories = get_all_categories(['id'=>$shortcode->category_id, 'status'=>'published']);
@endphp

<div class="row">
    <div class="col-sm-12 text-center">
        <h2 class="mb-20 blogByCategorySectionTitle">{!! BaseHelper::clean($shortcode->title) !!}</h2>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 blog-page-content">
        <div class="blog-page-content-wrapper">
        @if ($posts->count() > 0)
            @foreach ($posts as $post)
            <article class="post-item-wrapper">
                <div class="card mb-4 pb-4 post-item__inner">
                    <div class="row g-0">
                        <div class="col-md-4 post-item__image">
                            <a class="img-fluid-eq" href="{{ $post->url }}">
                                <div class="img-fluid-eq__dummy"></div>
                                <div class="img-fluid-eq__wrap">
                                    <img class="lazyload img-cover" src="{{ image_placeholder($post->image) }}" data-src="{{ RvMedia::getImageUrl($post->image, null, false, RvMedia::getDefaultImage()) }}">
                                </div>
                            </a>
                        </div>
                        <div class="col-md-8 post-item__content ps-md-4">
                            <div>
                                <div class="entry-title">
                                    <h4><a href="{{ $post->url }}">{{ $post->name }}</a></h4>
                                </div>

                                <div class="entry-meta mb-2">
                                    @if ($post->author)
                                        <div class="entry-meta-author">
                                            <span class="d-inline-block">{{ __('By') }}</span> <span class="d-inline-block author-name">{{ $post->author->name }}</span>
                                        </div>
                                    @endif
                                    @if ($post->categories->count())
                                        <div class="entry-meta-categories">
                                            <span class="d-inline-block">{{ __('in') }}</span>
                                            @foreach($post->categories as $category)
                                                <a href="{{ $category->url }}">{{ $category->name }}</a>@if (!$loop->last), @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="entry-meta-date">
                                        <span class="d-inline-block">{{ __('on') }}</span>
                                        <time>{{ $post->created_at->translatedFormat('M d, Y') }}</time>
                                    </div>
                                </div>
                                <div class="entry-description">
                                    <p>{{ Str::limit($post->description, 120) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
            <div>
                {!! $posts->withQueryString()->links() !!}
            </div>
        @endif
        </div>
    </div>
</div>
