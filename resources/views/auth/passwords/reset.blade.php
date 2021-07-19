@extends('layouts.auth')

@section('content')

            <a class="home" href="{{URL::to('/')}}">
                <div class="img-wrapper">
                    <img src="/images/logo.svg" />
                </div>
                <h1>OpenDialog</h1>
            </a>

            @if ($errors->has('email'))
                <p class="subheader">{{ $errors->first('email') }}</p>
            @elseif ($errors->has('password'))
                <p class="subheader">{{ $errors->first('password') }}</p>
            @else
                <p class="subheader">Let’s get you back on track.</p>
                <p class="subheader">You can define a brand new password below 👇</p>
            @endif

            <div class="container">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->token }}">

                    <div class="form-group">
                        <label for="email">{{ __('E-Mail') }}</label>
                        <input id="email" type="email" class="form-control" name="email" value="{{ $request->email ?? old('email') }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">{{ __('New Password') }}</label>
                        <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password" data-lpignore="true">
                    </div>

                    <div class="form-group">
                        <label for="password-confirm">{{ __('Confirm new password') }}</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" data-lpignore="true" required autocomplete="new-password">
                    </div>

                    <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Confirm') }}
                            </button>
                    </div>
                </form>
            </div>
@endsection
