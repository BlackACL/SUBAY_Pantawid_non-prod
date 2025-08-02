<div x-data="{ show: @js($show ?? false) }" x-show="show" @keydown.escape.window="show = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-cloak>
    <div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-lg w-full max-w-md p-6']) }} @click.away="show = false">
        {{ $slot }}
    </div>
</div> 