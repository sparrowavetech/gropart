@if (session('success_msg'))
    <div class="alert alert-success alert-dismissible" role="alert">{{ session('success_msg') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if (session('error_msg'))
    <div class="alert alert-danger alert-dismissible" role="alert">{{ session('error_msg') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if ($errors->any())
    <div class="alert alert-danger"><strong>Please fix the following:</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
