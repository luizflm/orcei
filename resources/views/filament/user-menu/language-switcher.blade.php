@php $locale = request()->cookie('locale') ?? config('app.locale'); @endphp

<div x-data="{ open: false }" style="position: relative">
    <x-filament::dropdown.list.item
        x-on:click="open = ! open"
        icon="heroicon-o-language"
    >
        {{ __('profile.locale') }}
    </x-filament::dropdown.list.item>

    <div
        x-show="open"
        x-on:click.outside="open = false"
        x-transition
        x-cloak
        class="fi-dropdown-panel"
        style="position: absolute; left: 100%; top: 0; margin-left: 4px; min-width: 196px; z-index: 50;"
    >
        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item
                tag="a"
                href="{{ route('set-locale', 'en') }}"
                :color="$locale === 'en' ? 'primary' : 'gray'"
                style="justify-content: space-between"
            >
                English
                <x-slot:badge>
                    @if ($locale === 'en')<x-filament::icon icon="heroicon-m-check" class="fi-color" style="width:1rem;height:1rem" />@endif
                </x-slot:badge>
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item
                tag="a"
                href="{{ route('set-locale', 'pt_BR') }}"
                :color="$locale === 'pt_BR' ? 'primary' : 'gray'"
                style="justify-content: space-between"
            >
                Português (Brasil)
                <x-slot:badge>
                    @if ($locale === 'pt_BR')<x-filament::icon icon="heroicon-m-check" class="fi-color" style="width:1rem;height:1rem" />@endif
                </x-slot:badge>
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </div>
</div>
