{{-- require: $user, App\Social  --}}
@php
    $userSocial = App\Social::find($user->id);
dump($userSocial);
@endphp
<div class="panel panel-success">
    <div class="panel-heading">Social Accounts Connection</div>

    <div class="panel-body pt-3">


        <div class="form-horizontal">
            @foreach(App\Social::getProviders() as $provider)
                @if ($provider=='instagram') @continue @endif
                <div class="form-group has-feedback">
                    <label class="col-sm-4 control-label">
                        @if ($provider=='youtube')
                            <i class="fa fa-google mr-1" aria-hidden="true"></i>
                            Google:
                        @else
                            <i class="fa fa-{{$provider}} mr-1" aria-hidden="true"></i>
                            {{$provider}}:
                        @endif


                    </label>

                    @if ($userSocial->hasSocial($provider))
                        <div class="col-sm-2">
                            <p class="form-control-static">
                                <span>connected</span>
                            </p>
                        </div>
                        <div class="col-sm-4">
                            <a class="btn" href="{{ route('social.remove', $provider) }}">Disconnect and wipe data</a>
                        </div>

                    @else
                        <div class="col-sm-6">
                            <a class="btn" href="{{ route('social.add', $provider) }}">Connect</a>
                        </div>
                    @endif


                </div>

            @endforeach
        </div>
    </div>
</div>