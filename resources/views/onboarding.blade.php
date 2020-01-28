<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/onboarding-app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/onboarding-app.css') }}" rel="stylesheet">
  </head>

  <body class="app">
    <div id="app">
      <app></app>
    </div>
  </body>
</html>
