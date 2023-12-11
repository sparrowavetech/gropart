@if ($contact)
    <div
        id="reply-wrapper"
        class="mb-3"
    >
        @if (count($contact->replies) > 0)
            @foreach ($contact->replies as $reply)
                <x-core::form.fieldset>
                    <p>{{ trans('plugins/contact::contact.tables.time') }}: <i>{{ $reply->created_at }}</i></p>
                    <p>{{ trans('plugins/contact::contact.tables.content') }}:</p>
                    {!! BaseHelper::clean($reply->message) !!}
                </x-core::form.fieldset>
            @endforeach
        @else
            <div class="text-muted">{{ trans('plugins/contact::contact.no_reply') }}</div>
        @endif
    </div>

    <x-core::button
        type="button"
        color="info"
        class="answer-trigger-button"
    >
        {{ trans('plugins/contact::contact.reply') }}
    </x-core::button>

    <div class="answer-wrapper mt-3">
        <input
            type="hidden"
            value="{{ $contact->id }}"
            id="input_contact_id"
        >

        <div class="mb-3">
            {!! Form::editor('message', null, ['without-buttons' => true, 'class' => 'form-control']) !!}
        </div>

        <x-core::button
            type="button"
            color="primary"
            icon="ti ti-message-circle-2"
            class="answer-send-button"
        >
            {{ trans('plugins/contact::contact.send') }}
        </x-core::button>
    </div>
@endif
