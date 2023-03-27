<div class="container mt-5 mb-3">
    <div class="row justify-content-center">
        <div class="col">
            <div class="row faqs-nav-tab">
                <div class="col-md-3">
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        @foreach($categories as $category)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if ($loop->first) active @endif" id="faq-tab-{{ $loop->index }}" data-bs-toggle="tab"
                                    data-bs-target="#faq-content-{{ $loop->index }}" type="button" role="tab"
                                    aria-controls="faq-content-{{ $loop->index }}" aria-selected="true">{!! BaseHelper::clean($category->name) !!}</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="faq-tab-content">
                        @foreach($categories as $category)
                            <div class="tab-pane fade @if ($loop->first) show active @endif" role="tabpanel"
                                aria-labelledby="home-tab" id="faq-content-{{ $loop->index }}">
                                <div class="row row-cols-sm-2 row-cols-1">
                                    @php $prvIndex=0; @endphp
                                    @foreach($category->faqs->chunk(round($category->count() / 2)) as $faqs)
                                        @php $prvIndex = $prvIndex+1 @endphp
                                        <div class="col">
                                            @foreach($faqs as $faq)
                                            <div class="faq-tab-wrapper">
                                                <div class="accordion" id="faq-accordion">
                                                    <div class="card">
                                                        <div class="card-header" id="heading-faq-{{ $prvIndex }}">
                                                            <h2 class="faq-title m-0">
                                                                <button class="btn btn-link btn-block text-start @if ($prvIndex ==1) collapsed @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-faq-{{ $prvIndex }}" aria-expanded="true" aria-controls="collapse-faq-{{ $prvIndex }}">
                                                                    {{ $faq->question }}
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapse-faq-{{ $prvIndex }}" class="collapse @if ($prvIndex ==1) show @endif" aria-labelledby="heading-faq-{{ $prvIndex }}" data-bs-parent="#faq-accordion">
                                                            <div class="faq-desc card-body">
                                                                {!! BaseHelper::clean($faq->answer) !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @php $prvIndex = $prvIndex+1 @endphp
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
