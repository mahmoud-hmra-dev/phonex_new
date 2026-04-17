{{-- My Addresses --}}
@php
    $customer = auth('customer')->user();
    $addresses = $customer->addresses()->get();

    $addressesData = $addresses->map(function ($addr) {
        return [
            'id'        => $addr->id,
            'name'      => $addr->first_name . ' ' . $addr->last_name,
            'first_name' => $addr->first_name ?? '',
            'last_name'  => $addr->last_name ?? '',
            'phone'     => $addr->phone ?? '',
            'email'     => $addr->email ?? '',
            'address'   => is_array($addr->address) ? implode(', ', array_filter($addr->address)) : ($addr->address ?? ''),
            'city'      => $addr->city ?? '',
            'state'     => $addr->state ?? '',
            'postcode'  => $addr->postcode ?? '',
            'country'   => $addr->country ?? '',
            'isDefault' => (bool) $addr->default_address,
        ];
    })->values()->all();
@endphp

<x-phonix::account.layout
    :title="__('phonix::app.account.addresses.title')"
    :breadcrumbs="[['label' => __('phonix::app.account.addresses.title')]]"
>
    <div
        class="space-y-[24px]"
        x-data="{
            showForm: false,
            editingId: null,
            deleteId: null,
            csrfToken: '{{ csrf_token() }}',
            form: { first_name: '', last_name: '', email: '', phone: '', address: '', city: '', state: '', postcode: '', country: '' },
            openNew() {
                this.editingId = null;
                this.form = { first_name: '', last_name: '', email: '', phone: '', address: '', city: '', state: '', postcode: '', country: '' };
                this.showForm = true;
            },
            openEdit(addr) {
                this.editingId = addr.id;
                this.form = {
                    first_name: addr.first_name,
                    last_name: addr.last_name,
                    email: addr.email || '',
                    phone: addr.phone || '',
                    address: addr.address || '',
                    city: addr.city || '',
                    state: addr.state || '',
                    postcode: addr.postcode || '',
                    country: addr.country || '',
                };
                this.showForm = true;
            },
            closeForm() { this.showForm = false; this.editingId = null; },
            confirmDelete(id) { this.deleteId = id; },
            cancelDelete() { this.deleteId = null; },
            saveAddress() {
                const url = this.editingId
                    ? '{{ route('phonix.account.addresses.update', ':id') }}'.replace(':id', this.editingId)
                    : '{{ route('phonix.account.addresses.store') }}';
                const f = document.createElement('form');
                f.method = 'POST'; f.action = url; f.style.display = 'none';
                const fields = {
                    '_token': this.csrfToken,
                    '_method': this.editingId ? 'PUT' : '',
                    'company_name': '',
                    'first_name':   this.form.first_name || '',
                    'last_name':    this.form.last_name  || '',
                    'email':        this.form.email      || '',
                    'address[]':    this.form.address    || '',
                    'country':      this.form.country    || '',
                    'state':        this.form.state      || '',
                    'city':         this.form.city       || '',
                    'postcode':     this.form.postcode   || '',
                    'phone':        this.form.phone      || '',
                };
                Object.entries(fields).forEach(([k, v]) => {
                    if (k === '_method' && !v) return;
                    const i = document.createElement('input');
                    i.type = 'hidden'; i.name = k; i.value = v;
                    f.appendChild(i);
                });
                document.body.appendChild(f);
                f.submit();
            },
        }"
    >
        {{-- Page Title --}}
        <div class="flex items-center justify-between" data-gsap="fade-up">
            <h1 class="text-fluid-xl font-bold text-slate-800 dark:text-slate-100">
                @lang('phonix::app.account.addresses.title')
            </h1>
        </div>

        {{-- Address Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-[16px]" data-gsap="fade-up">
            @foreach ($addressesData as $addr)
                <div class="card-phoenix p-[20px] relative">
                    {{-- Default Badge --}}
                    @if ($addr['isDefault'])
                        <span class="absolute top-[12px] end-[12px] inline-flex items-center px-[8px] py-[2px] rounded-full text-xs font-semibold bg-phoenix-100 text-phoenix-700 dark:bg-phoenix-900/30 dark:text-phoenix-300">
                            @lang('phonix::app.account.addresses.default')
                        </span>
                    @endif

                    <div class="text-sm space-y-[4px] text-slate-600 dark:text-slate-400 pe-[80px]">
                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $addr['name'] }}</p>
                        @if ($addr['phone'])
                            <p>{{ $addr['phone'] }}</p>
                        @endif
                        @if ($addr['address'])
                            <p>{{ $addr['address'] }}</p>
                        @endif
                        <p>{{ $addr['city'] }}@if ($addr['state']), {{ $addr['state'] }}@endif @if ($addr['postcode']) {{ $addr['postcode'] }}@endif</p>
                        @if ($addr['country'])
                            <p>{{ $addr['country'] }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-[12px] mt-[16px] pt-[12px] border-t border-slate-100 dark:border-dark-border">
                        <button
                            @click="openEdit({{ Js::from($addr) }})"
                            class="flex items-center gap-[4px] text-sm font-medium text-phoenix-600 dark:text-phoenix-400 hover:text-phoenix-700 dark:hover:text-phoenix-300 transition-colors"
                            aria-label="@lang('phonix::app.account.addresses.edit')"
                        >
                            <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                            </svg>
                            @lang('phonix::app.general.edit')
                        </button>

                        @if (!$addr['isDefault'])
                            <button
                                @click="confirmDelete({{ $addr['id'] }})"
                                class="flex items-center gap-[4px] text-sm font-medium text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors"
                                aria-label="@lang('phonix::app.account.addresses.delete')"
                            >
                                <svg class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                                @lang('phonix::app.general.delete')
                            </button>
                        @endif

                        @if (!$addr['isDefault'])
                            <form method="POST" action="{{ route('phonix.account.addresses.update', $addr['id']) }}" class="ms-auto">
                                @csrf
                                <input type="hidden" name="_method" value="PATCH">
                                <button type="submit" class="text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                                    @lang('phonix::app.account.addresses.set_default')
                                </button>
                            </form>
                        @endif
                    </div>

                    {{-- Delete Confirmation --}}
                    <div
                        x-show="deleteId === {{ $addr['id'] }}"
                        x-transition
                        x-cloak
                        class="absolute inset-0 bg-white/95 dark:bg-dark-card/95 backdrop-blur-sm rounded-lg flex flex-col items-center justify-center p-[20px] z-10"
                    >
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200 mb-[16px] text-center">
                            @lang('phonix::app.messages.confirm.delete_address')
                        </p>
                        <div class="flex gap-[8px]">
                            <button
                                @click="cancelDelete()"
                                class="btn-phoenix-ghost text-sm py-[8px] px-[16px]"
                            >
                                @lang('phonix::app.general.cancel')
                            </button>
                            <button
                                @click="
                                    const f = document.createElement('form');
                                    f.method = 'POST';
                                    f.action = '{{ route('phonix.account.addresses.delete', $addr['id']) }}';
                                    f.style.display = 'none';
                                    const ti = document.createElement('input');
                                    ti.type='hidden'; ti.name='_token'; ti.value=csrfToken;
                                    f.appendChild(ti);
                                    document.body.appendChild(f); f.submit();
                                "
                                class="inline-flex items-center justify-center px-[16px] py-[8px] text-sm font-semibold text-white bg-red-500 hover:bg-red-600 rounded-md transition-colors"
                            >
                                @lang('phonix::app.general.delete')
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Add New Address Card --}}
            <button
                @click="openNew()"
                class="flex flex-col items-center justify-center gap-[12px] p-[32px] rounded-lg border-2 border-dashed border-slate-300 dark:border-dark-border hover:border-phoenix-400 dark:hover:border-phoenix-600 text-slate-400 dark:text-slate-500 hover:text-phoenix-500 dark:hover:text-phoenix-400 transition-all duration-200 min-h-[200px]"
                aria-label="@lang('phonix::app.account.addresses.add')"
            >
                <svg class="w-[40px] h-[40px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="text-sm font-medium">@lang('phonix::app.account.addresses.add')</span>
            </button>
        </div>

        {{-- Address Form Modal --}}
        <div
            x-show="showForm"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm"
            @click="closeForm()"
            x-cloak
            aria-hidden="true"
        ></div>

        <div
            x-show="showForm"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-[61] flex items-center justify-center p-[16px]"
            role="dialog"
            aria-modal="true"
            :aria-label="editingId ? '{{ __('phonix::app.account.addresses.edit') }}' : '{{ __('phonix::app.account.addresses.add') }}'"
            x-cloak
        >
            <div class="w-full max-w-xl bg-white dark:bg-dark-card rounded-lg shadow-modal overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-thin" @click.stop>
                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-[20px] border-b border-slate-100 dark:border-dark-border">
                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100" x-text="editingId ? '{{ __('phonix::app.account.addresses.edit') }}' : '{{ __('phonix::app.account.addresses.add') }}'"></h3>
                    <button
                        @click="closeForm()"
                        class="p-[8px] text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 rounded-md transition-colors"
                        aria-label="@lang('phonix::app.general.close')"
                    >
                        <svg class="w-[20px] h-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form class="p-[20px] space-y-[16px]" @submit.prevent="saveAddress()">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[16px]">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                @lang('phonix::app.account.addresses.first_name') <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="form.first_name" class="input-phoenix" required aria-required="true" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                @lang('phonix::app.account.addresses.last_name') <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="form.last_name" class="input-phoenix" required aria-required="true" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                            @lang('phonix::app.account.addresses.phone') <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" x-model="form.phone" class="input-phoenix" required aria-required="true" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                            @lang('phonix::app.account.addresses.address_line_1') <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="form.address" class="input-phoenix" required aria-required="true" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[16px]">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                @lang('phonix::app.account.addresses.city') <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="form.city" class="input-phoenix" required aria-required="true" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                @lang('phonix::app.account.addresses.state')
                            </label>
                            <input type="text" x-model="form.state" class="input-phoenix" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[16px]">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                @lang('phonix::app.account.addresses.postcode') <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="form.postcode" class="input-phoenix" required aria-required="true" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-[6px]">
                                @lang('phonix::app.account.addresses.country') <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.country" class="input-phoenix" required aria-required="true">
                                <option value="">--</option>
                                <option value="SA">Saudi Arabia</option>
                                <option value="AE">United Arab Emirates</option>
                                <option value="KW">Kuwait</option>
                                <option value="BH">Bahrain</option>
                                <option value="QA">Qatar</option>
                                <option value="OM">Oman</option>
                                <option value="EG">Egypt</option>
                                <option value="JO">Jordan</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-[12px] pt-[8px]">
                        <button type="submit" class="btn-phoenix flex-1">
                            @lang('phonix::app.account.addresses.save_address')
                        </button>
                        <button type="button" @click="closeForm()" class="btn-phoenix-ghost">
                            @lang('phonix::app.general.cancel')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-phonix::account.layout>
