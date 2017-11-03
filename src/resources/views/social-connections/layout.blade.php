@extends('layouts.admin')

@section('page-title')
    Подключения к социальным сетям
@endsection

@section('page-description')
    просмотр
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">

            <div class="text-right mb-2">

                @if ($action!='index')
                    <a class="btn btn-warning" href="{{route('reviews.index')}}">Вернуться к списку</a>
                @endif

                @if ($action=='edit' || $action=='show')
                    <a class="btn btn-danger" href="">Удалить</a>
                @endif

            </div>

        </div>
    </div>

    @yield('sub-content')


@endsection
