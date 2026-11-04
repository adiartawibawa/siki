@props(['label', 'name', 'options' => []])

@php $id = $attributes->get('id', $name); @endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-xs font-medium text-slate-600">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}"
        {{ $attributes->except('id')->class([
                'block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-900 transition focus:outline-none focus:ring-2',
                'border-slate-200 focus:border-brand-500 focus:ring-brand-200' => !$errors->has($name),
                'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has($name),
            ]) }}>
        @foreach ($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
