@extends('layouts.admin')

@section('page-title')
    Отзывы
@endsection

@section('page-description')
    редактор
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">


            <div class="text-right mb-2">
                @yield('header-controls')


                @if ($action!='create')
                    <a class="btn btn-primary" href="/write-review.html">
                        Добавить новый отзыв
                    </a>
                @endif


                @if ($action!='index')
                    <a class="btn btn-warning" href="{{route('reviews.index')}}">Вернуться к списку</a>
                @endif

                @if ($action=='edit' || $action=='show')
                    <a class="btn btn-danger" href="{{ route('reviews.delete', $item->id) }}">Удалить</a>
                @endif

            </div>

            @include('errors.list')

        </div>
    </div>


    @yield('sub-content')



@endsection
