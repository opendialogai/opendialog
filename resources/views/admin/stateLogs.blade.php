<h4>State Logs</h4>
<table class="table table-striped-col">
    <thead>
        <tr>
            <th style="text-wrap: none; vertical-align: baseline;">Date</th>
            <th style="text-wrap: none; vertical-align: baseline;">Type</th>
            <th style="text-wrap: none; vertical-align: baseline;">Message</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td> {{$item->created_at}} </td>
                <td> {{$item->type}} </td>
                <td> {{$item->message}} </td>
            </tr>
        @endforeach
   </tbody>
</table>
