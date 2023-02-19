                            
<div class="container mb-3">
    <div class="row">
        <div class="col-sm-12 text-center">
            <h2 class="my-4 mt-2 faqByCategorySectionTitle">{!! BaseHelper::clean($shortcode->title) !!}</h2>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col">
            <div class="row faqs-nav-tab">
                <div class="col-md-3">
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($shortcode->{'category_id_' . $i})
                                @php
                                    $categoryData = get_faq_category(['id'=>$shortcode->{'category_id_' . $i}, 'status'=>'published']);
                                @endphp

                                @foreach($categoryData as $category)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link @if ($i == 1) active @endif" id="faq-tab-{{ $i }}" data-bs-toggle="tab"
                                        data-bs-target="#faq-content-{{ $i }}" type="button" role="tab"
                                        aria-controls="faq-content-{{ $i }}" aria-selected="true">{{ $category->name }}</button>
                                </li>
                                @endforeach
                            @endif
                        @endfor
                    </ul>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="faq-tab-content">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($shortcode->{'category_id_' . $i})
                                @php
                                    $faqs = get_faqs_by_category(['category_id'=>$shortcode->{'category_id_' . $i}, 'status'=>'published']);
                                @endphp
                                <div class="tab-pane fade @if ($i == 1) show active @endif" role="tabpanel"
                                    aria-labelledby="home-tab" id="faq-content-{{ $i }}">
                                    <div class="row row-cols-1">
                                        <div class="col">
                                            @foreach($faqs as $faq)
                                            <div class="faq-tab-wrapper">
                                                <div class="accordion" id="faq-accordion">
                                                    <div class="card">
                                                        <div class="card-header" id="heading-faq-{{ $loop->index }}">
                                                            <h2 class="faq-title m-0">
                                                                <button class="btn btn-link btn-block text-start @if ($loop->first) collapsed @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-faq-{{ $loop->index }}" aria-expanded="true" aria-controls="collapse-faq-{{ $loop->index }}">
                                                                    {{ $faq->question }}
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapse-faq-{{ $loop->index }}" class="collapse @if ($loop->first) show @endif" aria-labelledby="heading-faq-{{ $loop->index }}" data-bs-parent="#faq-accordion">
                                                            <div class="faq-desc card-body">
                                                                {!! BaseHelper::clean($faq->answer) !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
