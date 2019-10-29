<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <script>window.Laravel = {!! json_encode(['apiToken' => auth()->user()->api_token ?? null, 'userId' => auth()->user()->id]) !!};</script>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <script>window.DashboardCards = {!! json_encode(config('admin-stats.cards')) !!};</script>

    <script>window.NavigationItems = {!! json_encode(config('admin-navigation.items')) !!};</script>
  </head>

  <body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden">
    <div id="app">
      <app></app>
    </div>

    @if (request()->route()->getName() == 'webchat-demo')
      <script>
        window.openDialogSettings = {
          url: "{{ env("APP_URL") }}",
          validPath: 'admin/demo',
          user: {
            first_name: '{!! auth()->user()->name !!}',
            last_name: '',
            email: '{!! auth()->user()->email !!}',
            external_id: '{!! auth()->user()->id !!}',
          },
        };
      </script>

      <script src="{{ env('APP_URL') }}/vendor/webchat/js/opendialog-bot.js"></script>
    @endif
  </body>
</html>
