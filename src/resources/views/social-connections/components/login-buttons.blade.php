

@foreach(config('social-connections.primary') as $provider)
    @php
        switch($provider) {
            case 'vkontakte': $icon = 'vk'; $title = (App::isLocale('ru')) ? 'ВКонтакте' : $provider; break;
            default: $icon = $title = $provider;
        }
    @endphp
    <p><a href="{{ route('social.redirect', $provider) }}" class="btn btn-block btn-social btn-{{$icon}}"><span class="fa fa-{{$icon}}"></span>{{ucfirst($title)}}</a></p>
@endforeach

<p class="text-center text-muted small">
    {{ trans('social-connections::messages.aware') }}
</p>

<div class="text-right small"><a data-toggle="collapse" href="#collapseOther">{{ trans('social-connections::title.other-connections') }}</a></div>

<div class="collapse pt-1" id="collapseOther">
    @foreach(config('social-connections.providers') as $provider)
        @if (in_array($provider, config('social-connections.primary'))) @continue @endif
        @php
            switch($provider) {
                case 'vkontakte': $icon = 'vk'; $title = (App::isLocale('ru')) ? 'ВКонтакте' : $provider; break;
                default: $icon = $title = $provider;
            }
        @endphp
        <p><a href="{{ route('social.redirect', $provider) }}" class="btn btn-block btn-social btn-{{$icon}}"><span class="fa fa-{{$icon}}"></span>{{ucfirst($title)}}</a></p>
    @endforeach
</div>