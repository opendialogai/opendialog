@push('scaffold.css')
    <style>
        #revision-viewer .collapsed {
          display: none;
        }

        #revision-viewer .label {
          color: black;
          font-size: 16px;
          font-weight: 500;
        }

        #revision-viewer .DifferencesSideBySide {
          width: 100%;
          margin: 7px 0 30px 0;
        }

        #revision-viewer .DifferencesSideBySide thead {
          display: none;
        }

        #revision-viewer .DifferencesSideBySide tr td {
          width: 50%;
          height: auto;
          border: none;
        }

        #revision-viewer .DifferencesSideBySide tr th {
          border: none;
          background: none;
          padding-top: 0;
          padding-bottom: 0;
        }

        #revision-viewer .DifferencesSideBySide .Left del {
          text-decoration: none;
          background: #f98b8b;
        }

        #revision-viewer .DifferencesSideBySide .Right ins {
          text-decoration: none;
          background: #44ee44;
        }
    </style>
@endpush
@push('scaffold.js')
    <script>
        function showDiff(e, idx) {
          e.preventDefault();
          document.getElementById(`revisions-${idx}`).classList.remove('collapsed');
          document.getElementById(`show-link-${idx}`).classList.add('collapsed');
          document.getElementById(`hide-link-${idx}`).classList.remove('collapsed');
        }

        function hideDiff(e, idx) {
          e.preventDefault();
          document.getElementById(`revisions-${idx}`).classList.add('collapsed');
          document.getElementById(`show-link-${idx}`).classList.remove('collapsed');
          document.getElementById(`hide-link-${idx}`).classList.add('collapsed');
        }
    </script>
@endpush

<div id="revision-viewer">
    <table class="table w-full">
        <thead>
            <th class="text-left">Date</th>
            <th class="text-left">User</th>
            <th class="text-left">Updates</th>
            <th class="text-left">Actions</th>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['user'] }}</td>
                    <td>{{ $row['updates'] }}</td>
                    <td>
                        <a class="mr-6" id="show-link-{{ $i }}" href="#" onclick="showDiff(event, {{ $i }})">View changes</a>
                        <a class="mr-6 collapsed" id="hide-link-{{ $i }}" href="#" onclick="hideDiff(event, {{ $i }})">Hide changes</a>
                        <a href="#">Revert</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" id="revisions-{{ $i }}" class="revisions collapsed">{!! $row['format'] !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
