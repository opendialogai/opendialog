@push('scaffold.css')
    <style>
        .message .text-message,
        .message .button-message,
        .message .image-message {
            border-radius: 6px;
            padding: 7px 10px;
            background: #eaeaea;
            max-width: 300px;
        }

        .message .image-message img {
            max-width: 100%;
        }
    </style>
@endpush

<div>
    @foreach ($messages as $message)
        <div class="message mb-6">
            @if ($message['type'] == 'text-message')
                <div class="text-message">{!! $message['data'] !!}</div>
            @endif

            @if ($message['type'] == 'button-message')
                <div class="button-message">
                    <div>{!! $message['data']['text'] !!}</div>

                    <div class="buttons">
                        @foreach ($message['data']['buttons'] as $button)
                            <button class="btn btn-default btn-primary mt-1 mr-2">{{ $button['text'] }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($message['type'] == 'image-message')
                <div class="image-message">
                    @if (!empty($message['data']['link']))
                        <a href="{{ $message['data']['link'] }}">
                            <img src="{{ $message['data']['src'] }}" />
                        </a>
                    @else
                        <img src="{{ $message['data']['src'] }}" />
                    @endif
                </div>
            @endif
        </div>
    @endforeach
</div>
