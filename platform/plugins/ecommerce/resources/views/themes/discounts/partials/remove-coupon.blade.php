<div class="row promo">
    <div class="col-sm-9">
        <div class="alert alert-success coupon-text fw-bold fs-6" style="padding: 9px; 10px">
        <svg fill="#198754" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 300 300" xml:space="preserve"
            style="height: 30px; width: 30px; margin-right:3px"><g id="SVGRepo_bgCarrier" stroke-width="0"></g>
            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
            <g id="SVGRepo_iconCarrier"> <g> <g> <g> <rect x="107.563" y="119.408" width="10.375"
            height="12.281"></rect>
            <path d="M170.944,134.285c0-8.997-7.467-16.376-16.381-16.376c-8.995,0-16.376,7.291-16.376,16.376 c0,9.181,7.381,16.376,16.376,16.376C163.476,150.661,170.944,143.376,170.944,134.285z M147.456,134.285 c0-3.958,3.239-7.109,7.107-7.109c3.87,0,7.107,3.149,7.107,7.109c0,3.96-3.237,7.109-7.107,7.109 C150.695,141.394,147.456,138.246,147.456,134.285z"></path> <rect x="107.563" y="168.531" width="10.375" height="12.278"></rect> <rect x="138.38" y="145.544" transform="matrix(0.4696 -0.8829 0.8829 0.4696 -40.0464 234.8049)" width="74.035" height="10.375"></rect> <rect x="107.563" y="143.967" width="10.375" height="12.278"></rect> <path d="M196.828,150.124c-8.997-0.034-16.407,7.231-16.438,16.319c-0.036,9.179,7.319,16.407,16.322,16.444 c8.912,0.031,16.402-7.234,16.438-16.322C213.181,157.563,205.74,150.156,196.828,150.124z M196.768,173.615 c-3.865,0-7.107-3.151-7.107-7.112c0-3.96,3.242-7.107,7.107-7.107c3.87,0,7.112,3.146,7.112,7.107 S200.638,173.615,196.768,173.615z"></path> <path d="M149.997,0C67.157,0,0,67.157,0,150c0,82.841,67.157,150,149.997,150C232.841,300,300,232.838,300,150 C300,67.157,232.841,0,149.997,0z M238.489,185.004c0,8.045-7.462,14.568-16.661,14.568h-103.89v-6.484h-10.375v6.484H78.175 c-9.202,0-16.664-6.526-16.664-14.568v-69.795c0-8.043,7.462-14.566,16.664-14.566h29.388v6.484h10.375v-6.484h103.89 c9.2,0,16.661,6.523,16.661,14.566V185.004z"></path>
            </g> </g> </g> </g></svg>
            {{ __('Coupon code: :code', ['code' => session('applied_coupon_code')]) }}
        </div>
    </div>
    <div class="col-sm-3 text-end p-0">
        <button class="btn fs-6 btn-md btn-gray btn-warning remove-coupon-code" data-url="{{ route('public.coupon.remove') }}" type="button" style="padding: 12px;"><i class="fa fa-trash"></i> {{ __('Remove') }}</button>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-12">
        <div class="coupon-error-msg">
            <span class="text-danger"></span>
        </div>
    </div>
</div>
