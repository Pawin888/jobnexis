<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn bg-blue-600 mt-4 text-base-100']) }}>
    {{ $slot }}
</button>
