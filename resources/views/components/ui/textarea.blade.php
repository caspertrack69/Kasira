@props(['label' => '', 'name', 'value' => ''])

<label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
<textarea name="{{ $name }}" {{ $attributes->merge(['class' => 'mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500']) }}>{{ old($name, $value) }}</textarea>
@error($name)
<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror
