@extends('layouts.app')

@section('title', 'تعديل بيانات العيادة التخصصية')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <x-alerts />

    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('clinics.index') }}" class="hover:text-teal-700">العيادات والأقسام</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">تعديل بيانات العيادة</span>
            </nav>
            <h1 class="text-2xl font-bold text-[#0b1c30]">تعديل بيانات العيادة</h1>
        </div>
        <a href="{{ route('clinics.index') }}"
            class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition">
            ← العودة للعيادات
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
        <form action="{{ route('clinics.update', $clinic->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        اسم العيادة <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $clinic->name ?? '') }}" required
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600" />
                </div>


            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    الوصف والتجهيزات الطبية
                </label>
                <textarea name="description" rows="3"
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600">{{ old('description', $clinic->description ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        حالة التشغيل <span class="text-red-500">*</span>
                    </label>
                    <select name="status"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600">
                        <option value="active">جاهزة للتشغيل (نشطة)</option>
                        <option value="maintenance">تحت الصيانة</option>
                        <option value="inactive">مغلقة مؤقتاً</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-5">


                    <button type="button"
                        onclick="document.getElementById('deleteClinicModal-{{ $clinic->id }}').classList.remove('hidden')"
                        class="px-3.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 border border-red-200 rounded-lg transition">
                        حذف العيادة
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                        حفظ التعديلات
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete Modal -->
    <x-delete-clinic-modal :clinic="$clinic" />
</div>
@endsection