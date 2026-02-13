<x-core::card>
    <x-core::card.header>
        <x-core::card.title>
            {{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.widget.name') }}
        </x-core::card.title>
    </x-core::card.header>
    
    <x-core::card.body>
        <div class="mb-3">
            <label class="form-label">{{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.widget.feed_url') }}</label>
            <div class="input-group">
                <input type="text" class="form-control" id="facebook-catalog-feed-url" value="{{ $feedUrl }}" readonly>
                <button class="btn btn-primary" type="button" onclick="copyFeedUrl()">
                    {{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.widget.copy_url') }}
                </button>
            </div>
        </div>
        
        <script>
            function copyFeedUrl() {
                const input = document.getElementById('facebook-catalog-feed-url');
                input.select();
                document.execCommand('copy');
                
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = '{{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.widget.copied') }}';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            }
        </script>
    </x-core::card.body>
</x-core::card>