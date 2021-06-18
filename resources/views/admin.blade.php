<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <script>window.Laravel = {!! json_encode(['apiToken' => auth()->user()->api_token ?? null, 'userId' => auth()->user()->id, 'twoFactorAuthEnabled' => auth()->user()->twoFactorAuthEnabled()]) !!};</script>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <script>window.DashboardCards = {!! json_encode(config('admin-stats.cards')) !!};</script>

    <script>window.NavigationItems = {!! json_encode(config('admin-navigation')) !!};</script>

    <script>window.user = {!! json_encode(auth()->user()) !!};</script>

  </head>

  <body class="app">
    <div id="app">
      <app></app>
    </div>



    @if (request()->route()->getName() == 'webchat-demo' && request()->get('selected_scenario'))
      <script>
        window.openDialogSettings = {
          url: "{{ env("APP_URL") }}",
          validPath: 'admin/demo',
          user: {
            custom: {
              selected_scenario: "{{request()->get('selected_scenario')}}"
            }
          },
        };
      </script>

      <script src="{{ env('APP_URL') }}/vendor/webchat/js/opendialog-bot.js"></script>
    @endif
  </body>
</html>
