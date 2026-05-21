<x-app-layout>
    @section('header') Add Email Account @endsection

    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <form method="POST" action="{{ route('my.services.email.purchase') }}">
                @csrf

                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Order a new email account</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $service->name }} — <strong>{{ $service->currency }} {{ number_format($service->unit_price, 2) }}</strong> / {{ strtolower($service->billing_cycle) }} per account. The user account is created automatically once payment is confirmed.</p>
                </div>

                <div class="px-6 py-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-900">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900">Email Address <span class="text-red-500">*</span></label>
                        <div class="mt-2 flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-primary">
                            <input type="text" name="email_local" required value="{{ old('email_local') }}"
                                placeholder="username"
                                autocomplete="off"
                                autocapitalize="off"
                                spellcheck="false"
                                inputmode="email"
                                pattern="^[A-Za-z0-9](?:[A-Za-z0-9._+\-]*[A-Za-z0-9])?$"
                                title="Letters, digits, dot, underscore, plus or hyphen (no @)"
                                class="block flex-1 border-0 bg-transparent py-1.5 pl-3 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm">
                            <span class="inline-flex items-center bg-gray-50 px-3 text-gray-500 text-sm border-l border-gray-200 rounded-r-md select-none">{{ '@' . $emailDomain }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">All accounts use the <span class="font-mono">{{ '@' . $emailDomain }}</span> domain. The new user logs in with this address.</p>
                        <x-input-error :messages="$errors->get('email_local')" class="mt-2" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                            @foreach($roles as $r)
                                <option value="{{ $r }}" @selected(old('role') === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Determines what the user can access inside the dashboard.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required minlength="8"
                                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                            <p class="text-xs text-gray-500 mt-1">Min. 8 characters.</p>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required minlength="8"
                                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-900">
                        <strong>What happens next?</strong> You'll be redirected to Paystack to pay <strong>{{ $service->currency }} {{ number_format($service->unit_price, 2) }}</strong>. Once payment is confirmed, the account is created instantly — the new user can sign in with the email and password you just set.
                    </div>
                </div>

                <div class="flex items-center justify-end gap-x-4 border-t border-gray-900/10 px-6 py-4">
                    <a href="{{ route('my.services.index') }}" class="text-sm font-semibold text-gray-900">Cancel</a>
                    <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">
                        Continue to Payment →
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
