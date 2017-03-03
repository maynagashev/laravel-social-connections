@extends('vendor.social-connections.layout')


@section('sub-content')


    <div class="box box-warning">
        <div class="box-header with-border">
            <h2 class="box-title">
                Список всех подключений
            </h2>
        </div>
        <div class="box-body">

            <div class="col-xs-12 table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Провайдер</th>
                        <th>Аккаунт</th>
                        <th>Email</th>
                        <th>Локальный пользователь</th>
                        <th>Опции</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($items as $item)
{{--                        <tr><td colspan="10">{{dump($item->toArray())}}</td></tr>--}}
                        <tr>
                            <td><img src="{{$item->provider_avatar}}" style="max-width: 100px;"></td>
                            <td>{{$item->provider}}</td>
                            @if ($item->provider_url)
                                <td><a href="{{$item->provider_url}}" target="_blank">{{$item->provider_name}}</a></td>
                            @else
                                <td>{{$item->provider_name}}</td>
                            @endif
                            <td>{{$item->provider_email}}</td>
                            <td>{{$item->user->name}}</td>
                            <td><a href="/profile/{{$item->user_id}}.html" class="btn btn-primary" target="_blank">Публичный профиль</a></td>
{{--                            <td><a href="{{route('social-connections.edit', $item->id)}}" class="btn btn-primary">Редактировать</a></td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>



@stop
