@php
    use App\Support\Website;
    $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg);
    $type = $s['form_type'] ?? 'contact';
    $showContact = ($s['show_contact'] ?? 'yes') === 'yes';
    $img = Website::image($s['image'] ?? null);
    $map = $s['map_embed'] ?? Website::setting('map_embed');
    $anchor = 'form-' . $section->id;
    $isThisForm = old('form_type') === $type;
    $input = 'block w-full rounded-xl border-0 bg-white px-4 py-3 text-gray-900 ring-1 ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-brand';
    $zones = in_array($type, ['bin_request', 'quote', 'pickup'])
        ? \App\Models\Zone::where('is_active', true)->orderBy('name')->pluck('name') : collect();
@endphp
<section class="{{ Website::bg($bg) }} py-20 sm:py-28" id="{{ $anchor }}">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-5 lg:gap-16 lg:px-8">

        {{-- Side panel --}}
        <div class="reveal lg:col-span-2">
            @if($showContact)
                <div class="h-full rounded-[2rem] bg-brand-dark p-8 text-white sm:p-10">
                    <h3 class="font-display text-2xl font-extrabold">Contact details</h3>
                    <ul class="mt-8 space-y-6">
                        @foreach([['phone', 'phone', 'Call us'], ['envelope', 'email', 'Email'], ['map-pin', 'address', 'Visit us'], ['clock', 'office_hours', 'Office hours']] as [$icon, $key, $label])
                            @if($val = Website::setting($key))
                                <li class="flex gap-4">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-brand-accent"><x-site.icon :name="$icon" /></span>
                                    <div>
                                        <div class="text-sm text-white/60">{{ $label }}</div>
                                        @if($key === 'phone')
                                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $val) }}" class="font-semibold hover:underline">{{ $val }}</a>
                                        @elseif($key === 'email')
                                            <a href="mailto:{{ $val }}" class="font-semibold break-all hover:underline">{{ $val }}</a>
                                        @else
                                            <div class="font-semibold">{!! nl2br(e($val)) !!}</div>
                                        @endif
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                    @if($wa = preg_replace('/\D/', '', (string) Website::setting('whatsapp')))
                        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="mt-10 inline-flex items-center gap-2 rounded-full bg-[#25D366] px-6 py-3 font-bold text-white">
                            <x-site.social name="whatsapp" class="h-5 w-5" /> Chat on WhatsApp
                        </a>
                    @endif
                    @if($map)
                        <iframe src="{{ $map }}" class="mt-10 h-56 w-full rounded-2xl border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map"></iframe>
                    @endif
                </div>
            @elseif($img)
                <img src="{{ $img }}" alt="" loading="lazy" class="h-full max-h-[640px] w-full rounded-[2rem] object-cover shadow-2xl">
            @endif
        </div>

        {{-- Form --}}
        <div class="reveal {{ ($showContact || $img) ? 'lg:col-span-3' : 'lg:col-span-5 mx-auto w-full max-w-3xl' }}">
            @include('site.partials.heading', ['dark' => $dark, 'center' => false])

            @if(session('form_success') && session('form_type') === $type)
                <div class="rounded-3xl bg-green-50 p-8 text-center ring-1 ring-green-200">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-500 text-white"><x-site.icon name="check" class="h-8 w-8" /></div>
                    <p class="mt-5 text-lg font-semibold text-green-800">{{ session('form_success') }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('site.forms.submit') }}#{{ $anchor }}" class="grid gap-5 sm:grid-cols-2">
                    @csrf
                    <input type="hidden" name="form_type" value="{{ $type }}">
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                    @if($errors->any() && $isThisForm)
                        <div class="sm:col-span-2 rounded-2xl bg-red-50 p-4 text-sm text-red-700 ring-1 ring-red-200">
                            @foreach($errors->unique() as $error)<p>{{ $error }}</p>@endforeach
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">Full name *</label>
                        <input type="text" name="name" required maxlength="120" value="{{ $isThisForm ? old('name') : '' }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">Phone {{ $type === 'contact' ? '' : '*' }}</label>
                        <input type="tel" name="phone" maxlength="30" {{ $type === 'contact' ? '' : 'required' }} value="{{ $isThisForm ? old('phone') : '' }}" class="{{ $input }}" placeholder="024 000 0000">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">Email</label>
                        <input type="email" name="email" maxlength="150" value="{{ $isThisForm ? old('email') : '' }}" class="{{ $input }}" placeholder="you@example.com">
                    </div>

                    @if(in_array($type, ['quote', 'bin_request', 'pickup']))
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">Location / address *</label>
                            <input type="text" name="location" required maxlength="255" value="{{ $isThisForm ? old('location') : '' }}" class="{{ $input }}" placeholder="Area, street, landmark or GPS address" list="zones-{{ $section->id }}">
                            @if($zones->isNotEmpty())
                                <datalist id="zones-{{ $section->id }}">@foreach($zones as $z)<option value="{{ $z }}">@endforeach</datalist>
                            @endif
                        </div>
                    @endif

                    @if($type === 'bin_request' || $type === 'quote')
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">Customer type</label>
                            <select name="service_interest" class="{{ $input }}">
                                @foreach(['Household', 'Business / shop', 'Office', 'School / church / institution', 'Market', 'Other'] as $opt)
                                    <option @selected($isThisForm && old('service_interest') === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">Collection frequency</label>
                            <select name="frequency" class="{{ $input }}">
                                @foreach(['Weekly', 'Twice a week', 'Monthly', 'Not sure yet'] as $opt)
                                    <option @selected($isThisForm && old('frequency') === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if($type === 'quote')
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">Business name</label>
                            <input type="text" name="company" maxlength="150" value="{{ $isThisForm ? old('company') : '' }}" class="{{ $input }}">
                        </div>
                    @endif

                    @if($type === 'pickup')
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">What needs collecting?</label>
                            <select name="service_interest" class="{{ $input }}">
                                @foreach(['Renovation / building debris', 'Furniture & bulky items', 'Garden waste', 'Event clean-up', 'Other'] as $opt)
                                    <option @selected($isThisForm && old('service_interest') === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">{{ $type === 'contact' ? 'Message *' : 'Anything else we should know?' }}</label>
                        <textarea name="message" rows="5" maxlength="3000" {{ $type === 'contact' ? 'required' : '' }} class="{{ $input }}">{{ $isThisForm ? old('message') : '' }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand px-8 py-4 text-base font-bold text-white shadow-lg shadow-brand/25 transition hover:-translate-y-0.5 hover:bg-brand/90 sm:w-auto">
                            {{ $s['submit_label'] ?? 'Send' }} <x-site.icon name="arrow-right" class="h-4 w-4" />
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</section>
