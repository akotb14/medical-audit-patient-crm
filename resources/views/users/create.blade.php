@extends('layouts.app')

@section('title', 'إضافة مستخدم جديد وتعيين الصلاحيات')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0b1c30]">إضافة مستخدم جديد للنظام</h1>
            <p class="text-xs text-[#64748b] mt-1">إنشاء حساب جديد وتعيين الدور الوظيفي ومصفوفة الصلاحيات السريرية</p>
        </div>
        <a href="{{ route('users.index') }}" class="px-3.5 py-1.5 text-xs text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
            ← العودة للقائمة
        </a>
    </div>

    <!-- Form Container -->
    <form action="{{route('users.store')}}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: User Profile & Account Data -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>البيانات الشخصية وبيانات الدخول</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label for="name" class="font-bold text-slate-700 block mb-1">الاسم الكامل (رباعي):</label>
                    <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    value="{{ old('name') }}"
                    placeholder="مثال: د. عبد العزيز بن فهد الدوسري"
                    class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#00685f]"
                    />
                    @error('name')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                    
                <div>
                    
                    <label for="username" class="font-bold text-slate-700 block mb-1"> اسم المستخدم:</label>
                    <input
                    type="text"
                    id="username"
                    value="{{old('username')}}"
                    name="username"
                    required
                    placeholder="abdelazizdosari13"
                    class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono text-left focus:outline-none focus:ring-1 focus:ring-[#00685f]"
                    />
                    @error('username')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="font-bold text-slate-700 block mb-1">كلمة المرور الأولية:</label>
                    <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="••••••••••••"
                    class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono text-left focus:outline-none focus:ring-1 focus:ring-[#00685f]"
                    />
                    @error('password')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                
            </div>
            {{-- @if ($errors->any())
            <div  class="bg-red-800 w-fit p-2 rounded-lg text-white text-xs">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif --}}
        </div>

        <!-- Section 2: Role Selection -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>تعيين الدور الوظيفي (Role)</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                @foreach ($roles as $role)
                
                <label class="border border-slate-200 rounded-xl p-3.5 flex items-start gap-3 cursor-pointer hover:border-[#00685f] has-checked:border-[#00685f] has-checked:bg-[#eff4ff]">
                    <input type="radio" name="role_id" value="{{$role->id}}" class="mt-0.5 text-[#00685f] focus:ring-[#00685f]" />
                    <div>
                        <div class="font-bold text-[#0b1c30]">{{$role->name}}</div>
                    </div>
                </label>
                
                
                
                @endforeach
                <br/>
                @error('role_id')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
              
            </div>
        </div>

        <!-- Section 3: Granular Permissions Matrix -->
        {{-- <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center justify-between border-b border-slate-100 pb-2">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>الصلاحيات الدقيقة المخصصة (Permissions Matrix)</span>
                </span>
                <span class="text-xs text-slate-500 font-normal">يمكن تخصيص صلاحيات إضافية للمستخدم</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <!-- Appointments Module -->
                <div class="border border-slate-200 rounded-lg p-3 bg-[#f8fafc] space-y-2">
                    <div class="font-bold text-[#00685f] border-b border-slate-200 pb-1">وحدة المواعيد والزيارات</div>
                    <label class="flex items-center gap-2 text-slate-700 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="appointments.view" checked class="rounded text-[#00685f]" />
                        <span>عرض جدول المواعيد (View)</span>
                    </label>
                    <label class="flex items-center gap-2 text-slate-700 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="appointments.create" checked class="rounded text-[#00685f]" />
                        <span>حجز وتسجيل موعد جديد (Create)</span>
                    </label>
                    <label class="flex items-center gap-2 text-slate-700 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="appointments.update_status" checked class="rounded text-[#00685f]" />
                        <span>تأكيد الحضور أو الإلغاء (Status)</span>
                    </label>
                </div>

                <!-- Patients Module -->
                <div class="border border-slate-200 rounded-lg p-3 bg-[#f8fafc] space-y-2">
                    <div class="font-bold text-[#00685f] border-b border-slate-200 pb-1">وحدة المرضى والملفات السريرية</div>
                    <label class="flex items-center gap-2 text-slate-700 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="patients.view" checked class="rounded text-[#00685f]" />
                        <span>معاينة سجلات المرضى (MRN)</span>
                    </label>
                    <label class="flex items-center gap-2 text-slate-700 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="patients.create" checked class="rounded text-[#00685f]" />
                        <span>فتح ملف مريض جديد (Intake)</span>
                    </label>
                    <label class="flex items-center gap-2 text-slate-700 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="patients.edit_clinical" class="rounded text-[#00685f]" />
                        <span>تعديل الحساسية والأمراض المزمنة</span>
                    </label>
                </div>

                <!-- Complaints & Quality -->
                <div class="border border-slate-200 rounded-lg p-3 bg-[#f8fafc] space-y-2">
                    <div class="font-bold text-[#00685f] border-b border-slate-200 pb-1">وحدة الشكاوى والتحقيق (PX)</div>
                    <label class="flex items-center gap-2 text-slate-700 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="complaints.view" checked class="rounded text-[#00685f]" />
                        <span>عرض تذاكر الاعتراضات والشكاوى</span>
                    </label>
                    <label class="flex items-center gap-2 text-slate-700 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="complaints.advance_stepper" class="rounded text-[#00685f]" />
                        <span>نقل مراحل التحقيق في الشكوى</span>
                    </label>
                    <label class="flex items-center gap-2 text-slate-700 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="complaints.close" class="rounded text-[#00685f]" />
                        <span>إغلاق وحل الشكوى نهائياً</span>
                    </label>
                </div>
            </div>
        </div> --}}

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{route('users.index')}}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                إلغاء التغييرات
            </a>

            <button type="submit" class="px-6 py-2.5 bg-[#00685f] hover:bg-[#005049] text-white text-xs font-bold rounded-lg shadow-sm transition">
                حفظ وإنشاء حساب المستخدم
            </button>
        </div>
    </form>
</div>
@endsection
        