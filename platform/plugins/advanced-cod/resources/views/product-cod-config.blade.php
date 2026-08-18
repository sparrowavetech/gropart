<style>
    #advanced_cod_meta_box {
        border: 1px solid #b7ead6;
        box-shadow: 0 10px 28px rgba(28, 118, 85, 0.12);
        overflow: hidden;
    }

    #advanced_cod_meta_box .card-header {
        background: linear-gradient(135deg, #eefdf6 0%, #ecf7ff 100%);
        border-bottom-color: #cdeee1;
    }

    #advanced_cod_meta_box .card-title {
        color: #16614a;
        font-weight: 700;
    }

    #advanced_cod_meta_box .card-body {
        background: linear-gradient(135deg, #f5fff9 0%, #f2fbff 52%, #fffdf4 100%);
        padding: 20px;
    }

    .advanced-cod-config {
        align-items: center;
        display: flex;
        gap: 18px;
        justify-content: space-between;
    }

    .advanced-cod-config__content {
        min-width: 0;
    }

    .advanced-cod-config__label {
        color: #1f2937;
        display: block;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 6px;
    }

    .advanced-cod-config__help {
        color: #4b6259;
        font-size: 13px;
        line-height: 1.55;
        margin: 0;
        max-width: 760px;
    }

    .advanced-cod-switch {
        flex: 0 0 auto;
        position: relative;
    }

    .advanced-cod-switch__input {
        height: 1px;
        opacity: 0;
        position: absolute;
        width: 1px;
    }

    .advanced-cod-switch__label {
        align-items: center;
        background: #dce8e3;
        border: 1px solid rgba(22, 97, 74, 0.18);
        border-radius: 999px;
        cursor: pointer;
        display: flex;
        height: 36px;
        padding: 3px;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
        width: 68px;
    }

    .advanced-cod-switch__handle {
        background: #ffffff;
        border-radius: 50%;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.18);
        height: 28px;
        transform: translateX(0);
        transition: transform 0.2s ease;
        width: 28px;
    }

    .advanced-cod-switch__input:checked + .advanced-cod-switch__label {
        background: linear-gradient(135deg, #21b36b 0%, #16a3a8 100%);
        box-shadow: 0 0 0 4px rgba(33, 179, 107, 0.14);
    }

    .advanced-cod-switch__input:checked + .advanced-cod-switch__label .advanced-cod-switch__handle {
        transform: translateX(31px);
    }

    .advanced-cod-status {
        color: #16704f;
        display: inline-block;
        font-size: 12px;
        font-weight: 700;
        margin-top: 10px;
        text-transform: uppercase;
    }

    .advanced-cod-switch__input:not(:checked) ~ .advanced-cod-status--enabled,
    .advanced-cod-switch__input:checked ~ .advanced-cod-status--disabled {
        display: none;
    }

    .advanced-cod-status--disabled {
        color: #8a5a16;
    }

    @media (max-width: 767.98px) {
        .advanced-cod-config {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="advanced-cod-config">
    <div class="advanced-cod-config__content">
        <label for="is_cod_eligible" class="advanced-cod-config__label">Eligible for Cash on Delivery (COD)?</label>
        <p class="advanced-cod-config__help">
            If enabled, this product will be available for Cash on Delivery. If the cart contains any product where this is disabled, COD will be hidden at checkout.
        </p>
    </div>

    <div class="advanced-cod-switch">
        <input type="hidden" name="is_cod_eligible" value="0">
        <input type="checkbox" name="is_cod_eligible" class="advanced-cod-switch__input" id="is_cod_eligible" value="1" {{ $model->is_cod_eligible ? 'checked' : '' }}>
        <label class="advanced-cod-switch__label" for="is_cod_eligible" aria-label="Toggle COD eligibility">
            <span class="advanced-cod-switch__handle"></span>
        </label>
        <span class="advanced-cod-status advanced-cod-status--enabled">COD enabled</span>
        <span class="advanced-cod-status advanced-cod-status--disabled">COD disabled</span>
    </div>
</div>
