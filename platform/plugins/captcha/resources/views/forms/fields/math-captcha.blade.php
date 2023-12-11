<x-core::form-group>
    <x-core::form.label for="math-group" class="required">{{ app('math-captcha')->label() }}</x-core::form.label>
    {!! app('math-captcha')->input(['class' => 'form-control', 'id' => 'math-group']) !!}
</x-core::form-group>
