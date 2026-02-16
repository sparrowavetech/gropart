<div class="alert alert-warning mt-3 mb-3">
    <p><strong><i class="fa fa-exclamation-triangle"></i> COD Availability Conflict</strong></p>
    <p>Some items in your cart are not eligible for Cash on Delivery (COD). To use COD, please resolve the conflict below:</p>
    <ul class="mb-2">
        @foreach($ineligibleProducts as $name)
            <li>{{ $name }}</li>
        @endforeach
    </ul>
    <div class="d-flex gap-2">
        <a href="{{ route('public.cart') }}" class="btn btn-sm btn-danger">
            Manage Cart
        </a>
        <span class="text-muted mt-1">Or choose a prepaid method (Razorpay/Instamojo) to proceed with all items.</span>
    </div>
</div>
