@extends('layouts.auth')

@section('content')
            <a class="home" href="{{URL::to('/')}}">
                <div class="img-wrapper">
                    <img src="/images/logo.svg" />
                </div>
                <h1>OpenDialog</h1>
            </a>

            @if ($errors->has('email') || $errors->has('password'))
                <p class="subheader">{{ $errors->first('email') }} Not to worry though, let's <a href="{{ route('password.request') }}">recover your password</a></p>
            @else
                <p class="subheader">👋 &nbsp;Welcome to OpenDialog!</p>
            @endif

            <div class="container">

                    @if (flash()->message)
                        <div class="{{ flash()->class }}">
                            {{ flash()->message }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        <div class="form-group">
                            <label for="email">{{ __('E-Mail') }}</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control" name="password" required>
                        </div>

                        <div class="form-group form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>

                        @if (Route::has('password.request'))
                            <a class="password-request" href="{{ route('password.request') }}">
                                {{ __('I forgot my password') }}
                            </a>
                        @endif
                        </div>

                        <button type="submit" class="btn btn-primary">
                            {{ __('Sign in') }}
                        </button>

                        {{ csrf_field() }}
                    </form>
                </div>
</div>
@endsection
