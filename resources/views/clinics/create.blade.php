@extends('layouts.app')

@section('title', 'إضافة عيادة تخصصية جديدة')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('clinics.index') }}" class="hover:text-teal-700">العيادات والأقسام</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">إضافة عيادة</span>
            </nav>
            <h1 class="text-2xl font-bold text-[#0b1c30]">إضافة عيادة تخصصية جديدة</h1>
        </div>
        <a href="{{ route('clinics.index') }}"
            class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition">
            ← العودة للقائمة
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
        <form action="{{ route('clinics.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        اسم العيادة <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="مثال: عيادة العيون والليزك"
                        value="{{ old('name') }}"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    الوصف والتجهيزات الطبية للعيادة
                </label>
                <textarea name="description" rows="3"
                    placeholder="وصف الخدمات التشخيصية المتوفرة، أجهزة الفحص التخصصية..."
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-600">{{ old('description') }}</textarea>
            </div>



            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('clinics.index') }}"
                    class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    إلغاء
                </a>
                <button type="submit"
                    class="px-5 py-2 text-xs font-bold text-white bg-[#00685f] hover:bg-[#005049] rounded-lg shadow-sm transition">
                    حفظ وتأكيد إنشاء العيادة
                </button>
            </div>
        </form>
    </div>
</div>
@endsection