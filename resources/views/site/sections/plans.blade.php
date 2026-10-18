@php
    use App\Support\Website;
    $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg);
    $currency = $s['currency'] ?? 'GH₵';
    $plans = [];
    if (($s['source'] ?? 'service_plans') === 'service_plans') {
        $rows = \App\Models\ServicePlan::where('is_active', true)->orderBy('amount')->get();
        $mid = intdiv(max($rows->count() - 1, 0), 2);
        foreach ($rows as $i => $p) {
            $plans[] = [
                'name' => $p->name,
                'price' => $currency . ' ' . rtrim(rtrim(number_format((float) $p->amount, 2), '0'), '.'),
                'period' => '/ ' . (['Daily' => 'day', 'Weekly' => 'week', 'Monthly' => 'month', 'Quarterly' => 'quarter', 'Yearly' => 'year', 'Annually' => 'year'][$p->billing_cycle] ?? strtolower((string) $p->billing_cycle)),
                'description' => $p->billing_cycle ? "{$p->billing_cycle} billing" : '',
                'features' => array_filter(preg_split('/\R|;/', (string) $p->description)),
                'highlighted' => $rows->count() > 2 && $i === $mid,
            ];
        }
    }
    if (!$plans) {
        foreach ($s['items'] ?? [] as $p) {
            $plans[] = [
                'name' => $p['name'] ?? '',
                'price' => $p['price'] ?? '',
                'period' => $p['period'] ?? '',
                'description' => $p['description'] ?? '',
                'features' => array_filter(array_map('trim', preg_split('/\R/', (string) ($p['features'] ?? '')))),
                'highlighted' => ($p['highlighted'] ?? 'no') === 'yes',
            ];
        }
    }
@endphp
@if($plans)
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <div class="mx-auto grid max-w-6xl items-stretch gap-8 {{ count($plans) === 1 ? 'max-w-md' : (count($plans) === 2 ? 'md:grid-cols-2 max-w-4xl' : 'md:grid-cols-2 lg:grid-cols-3') }}">
            @foreach($plans as $plan)
                <div class="reveal relative flex flex-col rounded-3xl p-8 {{ $plan['highlighted'] ? 'bg-brand text-white shadow-2xl shadow-brand/30 lg:-my-4 lg:py-12' : 'bg-white ring-1 ring-gray-200' }}">
                    @if($plan['highlighted'])
                        <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-brand-accent px-4 py-1 text-xs font-bold uppercase tracking-wider text-white">Most popular</span>
                    @endif
                    <h3 class="font-display text-xl font-bold {{ $plan['highlighted'] ? 'text-white' : 'text-gray-900' }}">{{ $plan['name'] }}</h3>
                    @if($plan['description'])
                        <p class="mt-2 text-sm {{ $plan['highlighted'] ? 'text-white/75' : 'text-gray-500' }}">{{ $plan['description'] }}</p>
                    @endif
                    <p class="mt-6 flex items-baseline gap-1">
                        <span class="font-display text-4xl font-extrabold tracking-tight">{{ $plan['price'] }}</span>
                        <span class="text-sm font-semibold {{ $plan['highlighted'] ? 'text-white/70' : 'text-gray-500' }}">{{ $plan['period'] }}</span>
                    </p>
                    @if($plan['features'])
                        <ul class="mt-8 flex-1 space-y-3 text-sm">
                            @foreach($plan['features'] as $f)
                                <li class="flex gap-3"><x-site.icon name="check" class="h-5 w-5 shrink-0 {{ $plan['highlighted'] ? 'text-brand-accent' : 'text-brand' }}" /><span>{{ trim($f) }}</span></li>
                            @endforeach
                        </ul>
                    @else
                        <div class="flex-1"></div>
                    @endif
                    @if(!empty($s['plan_cta_label']))
                        <a href="{{ Website::href($s['plan_cta_url'] ?? '') }}" class="mt-8 block rounded-full px-6 py-3 text-center font-bold transition {{ $plan['highlighted'] ? 'bg-white text-brand hover:bg-white/90' : 'bg-brand/10 text-brand hover:bg-brand hover:text-white' }}">{{ $s['plan_cta_label'] }}</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
