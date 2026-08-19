<div class="card mb-3" style="background: linear-gradient(135deg, rgba(238, 242, 255, 0.7) 0%, rgba(248, 250, 252, 0.9) 100%); border: 1px solid #cbd5e1; border-radius: 8px;">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <input type="hidden" name="vacation_mode" value="0">
                <label class="form-check form-switch mb-0">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        name="vacation_mode"
                        id="vacation_mode"
                        value="1"
                        {{ $vacationModeValue ? 'checked' : '' }}
                    >
                    <span class="form-check-label fw-bold text-dark fs-5">{{ trans('plugins/marketplace::store.forms.vacation_mode') }}</span>
                </label>
            </div>
            <span class="badge bg-indigo-lt text-indigo px-2 py-1"><i class="ti ti-plane-departure me-1"></i> Vacation Settings</span>
        </div>
        <small class="form-hint text-muted d-block mb-3" style="font-size: 12px; font-weight: normal;">
            {{ trans('plugins/marketplace::store.forms.vacation_mode_helper') }}
        </small>

        <div class="form-group mb-0">
            <label for="vacation_message" class="form-label fw-medium text-muted small">{{ trans('plugins/marketplace::store.forms.vacation_message') }}</label>
            <textarea
                name="vacation_message"
                id="vacation_message"
                class="form-control"
                rows="2"
                placeholder="{{ trans('plugins/marketplace::store.forms.vacation_message_placeholder') }}"
                style="background: #ffffff; border: 1px solid #cbd5e1; font-size: 13px;"
            >{{ $vacationMessageValue }}</textarea>
            <small class="form-hint text-muted mt-1" style="font-size: 11px;">
                {{ trans('plugins/marketplace::store.forms.vacation_message_helper') }}
            </small>
        </div>
    </div>
</div>
