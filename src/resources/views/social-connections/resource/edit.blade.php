@extends('admin.brands.layout')


@section('sub-content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h1>Редактирование: {{ $item->title }}</h1>

                {!! Form::model($item, ['route' => ['brands.update', $item->id], 'method' => 'PATCH', 'files' => true]) !!}
                    @include('admin.brands.form', ['submitButtonText' => 'Сохранить изменения'])
                {!! Form::close() !!}
            </div>
        </div>
    </div>

@stop