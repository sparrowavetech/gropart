@php
$faqs = get_faqs_by_category(['category_id'=>$shortcode->category_id]);
$categoryData = get_faq_category(['id'=>$shortcode->category_id, 'status'=>'published']);
@endphp

<section class="faqByCategory">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-sm-12 text-center">
                <h2 class="mb-20 faqByCategorySectionTitle">{!! BaseHelper::clean($shortcode->title) !!}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                @foreach($categoryData as $category)
                <h3 class="faqByCategoryTitle mb-3">{{ $category->name }}</h3>
                @endforeach
            </div>
            @foreach($faqs as $faq)
            <div class="col-sm-6">
                <div class="faqByCategoryContent mb-4">
                    <h4 class="faq-category-question mb-3">{{ $faq->question }}</h4>
                    <div class="faq-category-answer text-justified">{!! BaseHelper::clean($faq->answer) !!}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
