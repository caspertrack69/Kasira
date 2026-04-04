@props(['label' => '', 'name', 'value' => '', 'type' => 'text'])

<label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
<input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" {{ $attributes->merge(['class' => 'mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500']) }} />
@error($name)
<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror
