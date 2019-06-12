@push('scaffold.css')
    <style>
        .message .form-message,
        .message .list-message,
        .message .rich-message,
        .message .text-message,
        .message .button-message,
        .message .image-message {
            border-radius: 6px;
            padding: 7px 10px;
            background: #eaeaea;
            max-width: 300px;
        }

        .message .list-message .slider.horizontal {
            padding-bottom: 30px;
        }
        .message .list-message .slider.vertical {
            padding-right: 30px;
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

            @if ($message['type'] == 'rich-message')
                <div class="rich-message">
                    @if (!empty($message['data']['title']))
                        <div class="rich-message--title mb-1">{{ $message['data']['title'] }}</div>
                    @endif
                    @if (!empty($message['data']['subtitle']))
                        <div class="rich-message--subtitle mb-2">{{ $message['data']['subtitle'] }}</div>
                    @endif
                    <div class="rich-message--text">{!! $message['data']['text'] !!}</div>

                    @if (!empty($message['data']['image']['src']))
                        <div class="rich-message--image mt-2 mb-1">
                            @if (!empty($message['data']['image']['url']))
                                <a href="{{ $message['data']['image']['url'] }}">
                                    <img src="{{ $message['data']['image']['src'] }}" />
                                </a>
                            @else
                                <img src="{{ $message['data']['image']['src'] }}" />
                            @endif
                        </div>
                    @endif

                    @if (!empty($message['data']['button']['text']))
                        <div class="buttons">
                            <button class="btn btn-default btn-primary mt-1 mr-2">{{ $message['data']['button']['text'] }}</button>
                        </div>
                    @endif
                </div>
            @endif

            @if ($message['type'] == 'form-message')
                <div class="form-message">
                    <div class="sc-message--form--text">{!! $message['data']['text'] !!}</div>

                    @foreach ($message['data']['elements'] as $element)
                        <div class="sc-message--form--element">
                            @if ($element['display'])
                                <span class="sc-message--form--element-label">{{ $element['display'] }}:</span>
                            @endif

                            @if ($element['element_type'] == 'text')
                                <input class="sc-message--form--element-input" />
                            @endif
                            @if ($element['element_type'] == 'textarea')
                                <textarea class="sc-message--form--element-textarea" />
                            @endif
                            @if ($element['element_type'] == 'select')
                                <select class="sc-message--form--element-select">
                                    @foreach ($element['options'] as $option_value => $option_text)
                                        <option value="{{ $option_value }}">
                                            {{ $option_text }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    @endforeach

                    @if (!$message['data']['auto_submit'])
                        <button>{{ $message['data']['submit_text'] }}</button>
                    @endif
                </div>
            @endif

            @if ($message['type'] == 'list-message')
                <div class="list-message">
                    <slider
                        direction="{{ $message['data']['view_type'] }}"
                        :pagination-visible="true"
                        :pagination-clickable="true"
                    >
                        @foreach ($message['data']['items'] as $item)
                            @if ($item['type'] == 'text-message')
                                <div class="text-message">{!! $item['data'] !!}</div>
                            @endif
                            @if ($item['type'] == 'button-message')
                                <div class="button-message">
                                    <div>{!! $item['data']['text'] !!}</div>
                                    <div class="buttons">
                                        @foreach ($item['data']['buttons'] as $button)
                                            <button class="btn btn-default btn-primary mt-1 mr-2">{{ $button['text'] }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if ($item['type'] == 'image-message')
                                <div class="image-message">
                                    @if (!empty($item['data']['link']))
                                        <a href="{{ $item['data']['link'] }}">
                                            <img src="{{ $item['data']['src'] }}" />
                                        </a>
                                    @else
                                        <img src="{{ $item['data']['src'] }}" />
                                    @endif
                                </div>
                            @endif
                            @if ($item['type'] == 'rich-message')
                                <div class="rich-message">
                                    @if (!empty($item['data']['title']))
                                        <div class="rich-message--title mb-1">{{ $item['data']['title'] }}</div>
                                    @endif
                                    @if (!empty($item['data']['subtitle']))
                                        <div class="rich-message--subtitle mb-2">{{ $item['data']['subtitle'] }}</div>
                                    @endif
                                    <div class="rich-message--text">{!! $item['data']['text'] !!}</div>

                                    @if (!empty($item['data']['image']['src']))
                                        <div class="rich-message--image mt-2 mb-1">
                                            @if (!empty($item['data']['image']['url']))
                                                <a href="{{ $item['data']['image']['url'] }}">
                                                    <img src="{{ $item['data']['image']['src'] }}" />
                                                </a>
                                            @else
                                                <img src="{{ $item['data']['image']['src'] }}" />
                                            @endif
                                        </div>
                                    @endif

                                    @if (!empty($item['data']['button']['text']))
                                        <div class="buttons">
                                            <button class="btn btn-default btn-primary mt-1 mr-2">{{ $item['data']['button']['text'] }}</button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </slider>
                </div>
            @endif
        </div>
    @endforeach
</div>
