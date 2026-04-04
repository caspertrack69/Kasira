@props(['label' => '', 'name', 'options' => [], 'selected' => null])

<label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
<select name="{{ $name }}" {{ $attributes->merge(['class' => 'mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500']) }}>
    <option value="">Select</option>
    @foreach($options as $key => $text)
        <option value="{{ $key }}" @selected((string) old($name, $selected) === (string) $key)>{{ $text }}</option>
    @endforeach
</select>
@error($name)
<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror
