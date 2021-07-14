@extends('layouts.auth')

@section('content')
    <a class="home" href="{{URL::to('/')}}">
        <div class="img-wrapper">
            <img src="/images/logo.svg" />
        </div>
        <h1>OpenDialog</h1>
    </a>
    <p class="subheader">Enter your 2FA Code</p>
    <div class="container">

        @if (flash()->message)
            <div class="{{ flash()->class }}">
                {{ flash()->message }}
            </div>
        @endif


        <form method="POST" action="/two-factor-challenge">
            @csrf


            <div class="form-group">
                <label>{{ __('Code') }}</label>
                <input type="text" name="code" />
            </div>

            <button type="submit" class="btn btn-primary">
                {{ __('Sign in') }}
            </button>
        </form>
    </div>
    </div>
@endsection
