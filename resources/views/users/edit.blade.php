@extends('layouts.app')

@section('title', 'تعديل بيانات الحساب والدور الوظيفي ')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0b1c30]">تعديل مستخدم</h1>
            <p class="text-xs text-[#64748b] mt-1">تعديل بيانات الحساب والدور الوظيفي والصلاحيات السريرية</p>
        </div>
        <a href="{{ route('users.index') }}"
            class="px-3.5 py-1.5 text-xs text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
            ← العودة للقائمة
        </a>
    </div>

    <!-- Form Container -->
    <form action="{{route('users.update',$user)}}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1: User Profile & Account Data -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>البيانات الشخصية وبيانات الدخول</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label for="name" class="font-bold text-slate-700 block mb-1">الاسم الكامل (رباعي):</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required

                    class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 focus:outline-none
                    focus:ring-1 focus:ring-[#00685f]" />

                </div>


                <div>

                    <label for="username" class="font-bold text-slate-700 block mb-1"> اسم المستخدم:</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required

                    class="w-full p-2.5 bg-white border border-slate-300 rounded-lg font-mono text-left
                    focus:outline-none focus:ring-1 focus:ring-[#00685f]" />

                </div>




            </div>
         
        </div>

        <!-- Section 2: Role Selection -->
        <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-[#0b1c30] flex items-center gap-2 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-[#00685f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>تعيين الدور الوظيفي (Role)</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                @foreach ($roles as $role)

                <label
                    class="border border-slate-200 rounded-xl p-3.5 flex items-start gap-3 cursor-pointer hover:border-[#00685f] has-checked:border-[#00685f] has-checked:bg-[#eff4ff]">
                    <input type="radio" name="role_id" value="{{$role->id}}" @checked($user->hasRole($role->name))
                    class="mt-0.5 text-[#00685f] focus:ring-[#00685f]" />
                    <div>
                        <div class="font-bold text-[#0b1c30]">{{ trans()->has('roles.' . $role->name) ? __('roles.' . $role->name) : $role->name }}</div>
                    </div>
                </label>



                @endforeach


            </div>

        </div>



        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{route('users.index')}}"
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                إلغاء التغييرات
            </a>

            <button type="submit"
                class="px-6 py-2.5 bg-[#00685f] hover:bg-[#005049] text-white text-xs font-bold rounded-lg shadow-sm transition">
                حفظ التعديلات
            </button>
        </div>
    </form>
</div>
@endsection
