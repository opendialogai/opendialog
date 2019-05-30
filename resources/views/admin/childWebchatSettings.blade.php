<h4>Children</h4>
<table class="table table-striped-col">
    <thead>
        <tr>
            <th style="text-wrap: none; vertical-align: baseline;">Name</th>
            <th style="text-wrap: none; vertical-align: baseline;">Value</th>
            <th style="text-wrap: none; vertical-align: baseline;">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($children as $child)
            <tr>
                <td>{{ $child->name }}</td>
                <td>
                    @switch($child->type)
                    @case('colour')
                        @if(!empty($child->value))
                            <span style="width: 16px; height: 16px; margin:auto; display: inline-block; border: 1px solid gray; vertical-align: middle; border-radius: 2px; background: {{ $child->value }}" /> <span style="margin-left: 20px;">{{ $child->value }}</span>
                        @endif
                        @break
                    @default
                        {{ $child->value }}
                        @break
                    @endswitch
                </td>
                <td class="actions">
                    <ul class="list-unstyled">
                        <li>
                            <a data-scaffold-action="webchat_settings-view" href="{{ url('/admin/webchat_settings/' . $child->id) }}">
                                <i class="fa fa-eye"> View</i>
                            </a>
                        </li>
                        <li>
                            <a data-scaffold-action="webchat_settings-edit" href="{{ url('/admin/webchat_settings/' . $child->id . '/edit') }}">
                                <i class="fa fa-pencil"> Edit</i>
                            </a>
                        <li>
                        </li>
                            <a
                                data-scaffold-action="webchat_settings-delete"
                                onclick="return confirm('Are you sure you want to delete this item?');"
                                href="{{ url('/admin/webchat_settings/' . $child->id . '/delete') }}">
                                <i class="fa fa-trash"> Delete</i>
                            </a>
                        </li>
                    </ul>
                </td>
            </tr>
        @endforeach
   </tbody>
</table>
