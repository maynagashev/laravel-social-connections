<div class="form-group">
    {!! Form::label('title', 'Provider: ') !!}
    {!! Form::text('title', null, ['class' => 'form-control']) !!}
</div>

<!-- Добавить Form Submit -->
<div class="form-group">
    {!! Form::submit($submitButtonText, ['class' => 'btn btn-primary form-control']) !!}
</div>


