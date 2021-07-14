@extends('layouts.auth')

@section('content')
    <a class="home" href="{{URL::to('/')}}">
        <div class="img-wrapper">
            <img src="/images/logo.svg" />
        </div>
        <h1>OpenDialog</h1>
    </a>
    @if (session('status'))
        <p class="subheader">{{ session('status') }}</p>
    @elseif ($errors->has('email'))
        <p class="subheader">{{ $errors->first('email') }}</p>
    @else
        <p class="subheader smaller"> Forgot your password? Let’s get you a new one!</p>
        <p class="subheader smaller"> What e-mail did you sign up with?</p>
    @endif
    <div class="container">
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email" >{{ __('E-Mail') }}</label>
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <button type="submit">
                    {{ __('Confirm') }}
                </button>
            </div>
        </form>
    </div>
@endsection
