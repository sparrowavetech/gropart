@php
$brands = get_all_brands();
@endphp
<div class="container">
  <div class="row">
  @foreach ($brands as $brand)
    <div class="col-sm">
        <div class="card" style="width: 18rem;">
            <img class="card-img-top lazyload" src="{{ image_placeholder($brand->logo) }}" data-src="{{ RvMedia::getImageUrl($brand->logo, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $brand->name }}">
            <div class="card-body">
                <h5 class="card-title">{{ $brand->name }}</h5>
                <p class="card-text">{{ BaseHelper::clean(Str::limit($brand->description, 150)) }}</p>
                <a href="{{ $brand->url }}" class="btn btn-primary">Go somewhere</a>
            </div>
        </div>
    </div>
    @endforeach
  </div>
</div>