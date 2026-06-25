<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                   <a href="{{ asset('images/logo.jpeg') }}" target="_blank">
                        <img src="{{ asset('images/logo.jpeg') }}" 
                             alt="{{ config('app.name') }}" 
                             class="block h-9 w-auto rounded-full">
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 no-print">
               
                @if(auth()->user()->hasRole('admin'))
                    {{-- رابط تسعير العمرة --}}
                    <a href="{{ route('pricing') }}"
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                        💰 تسعير العمرة
                    </a>
                @endif

                 <a href="{{ route('trips.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                 🚌 الرحلات</a>

                @if(auth()->user()->hasRole('admin'))
                    {{-- الحسابات --}}
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <span class="ml-1">📊 الحسابات</span>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">

                            @php
                            $pendingCount = \App\Models\Discount::where('status','pending')->count();
                            $pendingCount2 = \App\Models\JournalEntry::where('status','draft')->count();
                            @endphp 
                            <x-dropdown-link :href="route('accounts.index')" class="flex justify-end items-center gap-2">
                                {{ __('شجرة الحسابات 🌳⚖️') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('journal.index')" class="flex justify-end items-center gap-2">
                                {{ __('قائمة القيود 📋') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('discounts.pending')" class="flex justify-end items-center gap-2">
                                @if($pendingCount > 0)
                                    <span style="background:#ef4444; color:white; border-radius:50%;
                                                 padding:1px 6px; font-size:11px; margin-right:5px;">
                                        {{ $pendingCount }}
                                    </span>
                                @endif
                                 خصومات 🏷️
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('journal.pending')" class="flex justify-end items-center gap-2">
                                @if($pendingCount2 > 0)
                                <span style="background:#ef4444;color:white;border-radius:50%;
                                                    padding:1px 7px;font-size:11px;">
                                        {{ $pendingCount2 }}
                                    </span>
                                    @endif
                                 المحاسب ⏳
                            </x-dropdown-link>
                    </x-slot>
                    </x-dropdown>
                @endif
                
                @if(auth()->user()->hasRole('admin'))
                    {{-- التقارير --}}
                    <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <span class="ml-1">📋 التقارير الختامية</span>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('financial-reports.trial-balance')" class="flex justify-end items-center gap-2">
                        <i class="fas fa-balance-scale me-2"></i> ميزان المراجعة
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('financial-reports.income-statement')" class="flex justify-end items-center gap-2">
                        <i class="fas fa-file-invoice me-2"></i> قائمة الدخل
                        </x-dropdown-link>
                         <x-dropdown-link :href="route('financial-reports.balance-sheet')" class="flex justify-end items-center gap-2">
                         <i class="fas fa-landmark me-2"></i> الميزانية العمومية
                        </x-dropdown-link>
                    </x-slot>
                
                    </x-dropdown> 
                @endif


                @if(auth()->user()->hasRole('admin'))
                    {{-- الايصالات --}}
                    <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <span class="ml-1">🧾 الايصالات</span>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('vouchers.receipt')" class="flex justify-end items-center gap-2">
                        📥 ايصال استلام
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('vouchers.payment')" class="flex justify-end items-center gap-2">
                        📤 ايصال صرف
                        </x-dropdown-link>
                    </x-slot>
                
                
                    </x-dropdown> 
                @endif  
                
                
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <span class="ml-1">👤 {{ Auth::user()->name }}</span>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if(auth()->user()->hasRole('admin'))
                            <x-dropdown-link :href="route('employees.index')" class="flex justify-end items-center gap-2">
                                {{ __('الموظفين 👔') }}
                            </x-dropdown-link>
                        @endif
                        <x-dropdown-link :href="route('profile.edit')" class="flex justify-end items-center gap-2">
                        {{ __('الحساب الشخصي 👤') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" class="flex justify-end items-center gap-2">
                            @csrf

                            <x-dropdown-link :href="route('logout')" class="flex justify-end items-center gap-2"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('تسجيل خروج 🚪') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown> 

                {{-- Dropdown نظام إدارة الحجوزات --}}
                <x-dropdown align="left" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <span class="ml-1">📅 نظام إدارة الحجوزات</span>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link href="{{ route('register') }}" class="flex justify-end items-center gap-2">
                            🇪🇬 حجوزات مصر
                        </x-dropdown-link>
                        <x-dropdown-link href="{{ config('app.main_app_url') }}" target="_blank" class="flex justify-end items-center gap-2">
                            🇸🇦 حجوزات السعودية
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>
                
            </div>

           

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>