{{-- require: $user, App\Social  --}}

<div class="form-horizontal">

    <table class="table table-striped">
        <tbody>

        @foreach(App\Social::getProviders() as $provider)
            <tr><td style="padding-top:20px;">
                    <div class="form-group has-feedback">
                        <label class="col-sm-4 control-label">
                            @if ($provider=='youtube')
                                <i class="fa fa-google ion-social-google mr-1" aria-hidden="true"></i>
                                google:
                            @else
                                @php
                                    switch ($provider) {
                                        case 'vkontakte':  $icon = 'vk'; break;
                                        default: $icon = $provider;
                                    }
                                @endphp
                                <i class="fa fa-{{$icon}} mr-1" aria-hidden="true"></i>
                                {{$provider}}:
                            @endif

                        </label>

                        @if ($user->hasProvider($provider))
                            @php $p = $user->getProvider($provider); @endphp


                            <div class="col-sm-4">
                                <p class="form-control-static">
                                    <strong style="color:green;">{{trans('social-connections::title.provider-connected')}}</strong>
                                <p class="text-muted">
                                    <small>
                                        @if($p->provider_url)
                                            <a href="{{$p->provider_url}}" target="_blank">{{$p->provider_name}}</a>
                                        @else
                                            {{$p->provider_name}}
                                        @endif
                                    </small>

                                </p>
                                @if ($p->provider_avatar) <img src="{{$p->provider_avatar}}" style="max-width:200px;"> @endif
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <a class="btn btn-danger btn-sm" href="{{ route('social.remove', $provider) }}">{{trans('social-connections::title.btn-disconnect')}}</a>
                            </div>

                            {{--{{dump($p->toArray())}}--}}

                        @else
                            <div class="col-sm-6">
                                <a class="btn btn-primary" href="{{ route('social.add', $provider) }}">{{trans('social-connections::title.btn-connect')}}</a>
                            </div>
                        @endif




                    </div>
                </td></tr>

        @endforeach
        </tbody>
    </table>
</div>