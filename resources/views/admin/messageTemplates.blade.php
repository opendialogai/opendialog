<h4>Message Templates</h4>
<table class="table table-striped-col">
    <thead>
        <tr>
            <th style="text-wrap: none; vertical-align: baseline;">Name</th>
            <th style="text-wrap: none; vertical-align: baseline;">Created At</th>
            <th style="text-wrap: none; vertical-align: baseline;">Updated At</th>
            <th style="text-wrap: none; vertical-align: baseline;">Actions</th>
        </tr>
    </thead>
    <tbody>
         @foreach($items as $item)
          <tr>
              <td> {{$item->name}} </td>
              <td> {{$item->created_at}} </td>
              <td> {{$item->updated_at}} </td>
              <td class="actions">
                  <ul class="list-unstyled">
                      <li>
                          <a data-scaffold-action="message_templates-view" href="{{ url('/cms/message_templates/' . $item->id) }}">
                              <i class="fa fa-eye"> View</i>
                          </a>
                      </li>
                      <li>
                          <a data-scaffold-action="message_templates-edit" href="{{ url('/cms/message_templates/' . $item->id . '/edit') }}">
                              <i class="fa fa-pencil"> Edit</i>
                          </a>
                      <li>
                      </li>
                          <a
                              data-scaffold-action="message_templates-delete"
                              onclick="return confirm('Are you sure you want to delete this item?');"
                              href="{{ url('/cms/message_templates/' . $item->id . '/delete') }}">
                              <i class="fa fa-trash"> Delete</i>
                          </a>
                      </li>
                  </ul>
              </td>
          </tr>
         @endforeach
   </tbody>
</table>
