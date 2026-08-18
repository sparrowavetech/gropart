<div id="whatsapp-floating-button"></div>

@php
    $position = setting('whatsapp-floating-button.position', 'right');
    $offsetX = (int) setting('whatsapp-floating-button.offset_x', 20);
    $offsetY = (int) setting('whatsapp-floating-button.offset_y', 20);
    $showPopup = (bool) setting('whatsapp-floating-button.show_popup', false);
@endphp

<style>
    #whatsapp-floating-button {
        left: {{ $position === 'left' ? $offsetX . 'px' : 'auto' }} !important;
        right: {{ $position === 'right' ? $offsetX . 'px' : 'auto' }} !important;
        bottom: {{ $offsetY }}px !important;
    }
</style>

<script>
    window.addEventListener('load', function() {
        const whatsappFloatingButton = document.getElementById('whatsapp-floating-button');

        if (whatsappFloatingButton) {
            $(whatsappFloatingButton).floatingWhatsApp({
                phone: "{{ setting('whatsapp-floating-button.phone_number') }}",
                popupMessage: @json(Str::limit((string) setting('whatsapp-floating-button.popup_message'), 220)),
                showPopup: {{ $showPopup ? 'true' : 'false' }},
                headerTitle: @json((string) setting('whatsapp-floating-button.popup_title')),
                position: "{{ $position }}",
                size: "{{ setting('whatsapp-floating-button.size', 60) }}px",
                backgroundColor: '#25D366',
                showOnIE: true,
                autoOpenTimeout: 0,
                headerColor: '#128C7E',
                zIndex: {{ (int) setting('whatsapp-floating-button.z_index', 999) }},
            });
        }
    });
</script>
