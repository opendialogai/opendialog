@unless($actions->readonly())
    <div class="btn-group pull-right mt5">
        @if (get_class($item) == 'OpenDialogAi\ResponseEngine\OutgoingIntent')
            <a href="{{ route('scaffold.create', ['module' => 'message_templates', 'outgoing_intent' => $item->getKey()]) }}"
               class="btn btn-success btn-quirk mr20">
                <i class="fa fa-plus"></i>
                Create message template
            </a>
        @endif
        @if ($actions->authorize('update', $item))
            <a href="{{ route('scaffold.edit', ['module' => $module, 'id' => $item->getKey()]) }}"
               class="btn btn-info btn-quirk">
                <i class="fa fa-pencil"></i>
                {{ trans('administrator::buttons.edit') }}
            </a>
        @endif
        @if ($actions->authorize('delete', $item))
            <a href="{{ route('scaffold.delete', ['module' => $module, 'id' => $item->getKey()]) }}"
               class="btn btn-danger btn-quirk"
               onclick="return confirm('Are you sure?');">
                <i class="fa fa-trash"></i>
                {{ trans('administrator::buttons.delete') }}
            </a>
        @endif
    </div>
@endunless
