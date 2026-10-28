{{--
    Marks a patient file that was archived. The clinical record stays readable,
    so the marker follows the name instead of hiding the row.
--}}
@props(['patient'])

@if ($patient?->trashed())
    <span
        {{ $attributes->merge(['class' => 'inline-flex items-center px-1.5 py-0.5 rounded border border-amber-200 bg-amber-50 text-[10px] font-bold text-amber-800']) }}>(مؤرشف)</span>
@endif