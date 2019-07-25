@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Two-factor Authentication') }}</div>
                <div class="card-body">

                    <p>Please enter your Opendialog Security Code</p>
                    <form method="POST" action="{{url('auth/token')}}">
                        {!! csrf_field() !!}

                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group has-feedback">
                            <input type="type" name="token" class="form-control" placeholder="Code">
                            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Verify Security Code') }}
                                </button>
                            </div>
                        </div>
                    </form>

                </div><!-- /.card-body -->
            </div><!-- /.card -->
        </div><!-- /.col-md-8 -->
    </div><!-- /.row -->
</div><!-- /.container -->
@endsection
