

@section('sub-content')

    <div class="row">
        <div class="col-md-8">

            <div class="box box-warning">
                <div class="box-header with-border">
                    <h2 class="box-title">Добавление брэнда</h2>
                </div>
                <div class="box-body">
                    {!! Form::open(['route' => 'brands.store', 'method' => 'post']) !!}

                    @include('admin.brands.form', ['submitButtonText' => 'Добавить брэнд'])

                    {!! Form::close() !!}

                    @include('errors.list')
                </div>
            </div>


        </div>
        <div class="col-md-4">


        </div>
    </div>


@endsection