<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">

    @include("includes.gtm-head")
  </head>

  <body>
    @include("includes.gtm-body")

    <div class="page">
      <div class="header">
        <img class="logo" src="/images/homepage-logo.svg" />
      </div>

      <div class="body">
        <h2>Welcome to OpenDialog.</h2>
        <h3>OpenDialog is a conversation management platform, that helps you build and manage conversational applications.</h3>
      </div>

      <div class="footer">
        <div>
          <a class="button" href="/login">Login</a>
        </div>
        <div>
          <a class="button" target="_blank" href="https://www.opendialog.ai">Find out more</a>
        </div>
      </div>
    </div>
  </body>
</html>
