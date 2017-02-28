<div class="form-group">
    {!! Form::label('title', 'Название: * ') !!}
    {!! Form::text('title', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('slug', 'Адресный идентификатор на английском:') !!}
    {!! Form::text('slug', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('category_id', 'Категория брэнда:') !!}
    @php
        $cats = \App\BrandCategory::pluck('title', 'id');
        $cats->prepend('--- Категория не выбрана ---');
    @endphp
    {!! Form::select('category_id', $cats, null, ['class' => 'form-control']) !!}
</div>


<div class="form-group">
    {!! Form::label('description', 'Краткое описание: ') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('photo_id', 'Фото брэнда: ') !!}
    {!! Form::file('photo_id', ['class' => 'form-control']) !!}
</div>

<!-- Добавить Form Submit -->
<div class="form-group">
    {!! Form::submit($submitButtonText, ['class' => 'btn btn-primary form-control']) !!}
</div>


