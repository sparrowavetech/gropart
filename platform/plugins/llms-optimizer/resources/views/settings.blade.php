@extends('core/setting::layouts.master')

@section('content')
    <div class="max-width-1200">
        {!! $form->renderForm() !!}

        <div class="mt-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Actions</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-info w-100" id="btn-preview-llms">
                                <i class="ti ti-eye me-1"></i> Preview llms.txt
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-warning w-100" id="btn-regenerate-llms">
                                <i class="ti ti-refresh me-1"></i> Regenerate Static File
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-secondary w-100" id="btn-clear-cache">
                                <i class="ti ti-trash me-1"></i> Clear Cache
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="preview-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">LLMS.txt Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre id="preview-content" style="max-height: 500px; overflow-y: auto; background: #f5f5f5; padding: 15px; border-radius: 4px;"></pre>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('public.llms-txt') }}" target="_blank" class="btn btn-primary">
                        <i class="ti ti-external-link me-1"></i> Open in New Tab
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
    <script>
        $(document).ready(function() {
            $('#btn-preview-llms').on('click', function() {
                const $button = $(this);
                const originalText = $button.html();
                
                $button.prop('disabled', true).html('<i class="ti ti-loader fa-spin me-1"></i> Loading...');
                
                $.ajax({
                    url: '{{ route('llms-optimizer.settings.preview') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#preview-content').text(response.data.content);
                        $('#preview-modal').modal('show');
                    },
                    error: function(xhr) {
                        Botble.showError(xhr.responseJSON?.message || 'Failed to generate preview');
                    },
                    complete: function() {
                        $button.prop('disabled', false).html(originalText);
                    }
                });
            });

            $('#btn-regenerate-llms').on('click', function() {
                const $button = $(this);
                const originalText = $button.html();
                
                $button.prop('disabled', true).html('<i class="ti ti-loader fa-spin me-1"></i> Regenerating...');
                
                $.ajax({
                    url: '{{ route('llms-optimizer.settings.regenerate') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Botble.showSuccess(response.message);
                    },
                    error: function(xhr) {
                        Botble.showError(xhr.responseJSON?.message || 'Failed to regenerate file');
                    },
                    complete: function() {
                        $button.prop('disabled', false).html(originalText);
                    }
                });
            });

            $('#btn-clear-cache').on('click', function() {
                const $button = $(this);
                const originalText = $button.html();
                
                $button.prop('disabled', true).html('<i class="ti ti-loader fa-spin me-1"></i> Clearing...');
                
                $.ajax({
                    url: '{{ route('llms-optimizer.settings.clear-cache') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Botble.showSuccess(response.message);
                    },
                    error: function(xhr) {
                        Botble.showError(xhr.responseJSON?.message || 'Failed to clear cache');
                    },
                    complete: function() {
                        $button.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
@endpush

