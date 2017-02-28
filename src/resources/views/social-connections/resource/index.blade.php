@extends('vendor.social-connections.layout')


@section('sub-content')


    <div class="box box-warning">
        <div class="box-header with-border">
            <h2 class="box-title">
                Список всех подключений к социальным сетям
            </h2>
        </div>
        <div class="box-body">

            <div class="col-xs-12 table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Заголовок</th>
                        <th>Оценка</th>
                        <th>Продукт</th>
                        <th></th>

                    </tr>
                    </thead>
                    <tbody>

                    @foreach($items as $item)
                        <tr>
                            <td>{{$loop->iteration}}.</td>
                            <td><a href="{{route('reviews.edit', $item->id)}}">{{$item->title}}</a></td>
                            <td>{{$item->rating}}</td>
                            <td><a href="{{route('products.edit', $item->product->id)}}">{{$item->product->title}}</a></td>
                            <td><a class="btn btn-warning btn-xs" href="{{ route('reviews.edit', $item->id) }}">Редактировать</a></td>
                            <td><a class="btn btn-danger btn-xs" href="{{ route('reviews.delete', $item->id) }}">Удалить</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>



@stop
