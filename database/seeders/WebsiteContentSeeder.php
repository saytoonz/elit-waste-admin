<?php

namespace Database\Seeders;

use App\Models\Website\WebsiteMedia;
use App\Models\Website\WebsiteMenuItem;
use App\Models\Website\WebsitePage;
use App\Models\Website\WebsitePost;
use App\Models\Website\WebsiteSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Starter website content. Safe to run repeatedly: existing pages (by slug),
 * media (by path), menus and posts are left alone.
 */
class WebsiteContentSeeder extends Seeder
{
    protected function img(string $name): string
    {
        return "images/site/{$name}.jpg";
    }

    public function run(): void
    {
        DB::transaction(function () {
            $this->seedMedia();
            $pages = $this->seedPages();
            $this->seedMenus($pages);
            $this->seedPosts();
        });
    }

    protected function seedMedia(): void
    {
        $alts = [
            'hero-collection' => 'Collection crew loading a waste truck',
            'bins-colour'     => 'Colour-coded wheelie bins',
            'bins-sorted'     => 'Compost, waste and recycling bins',
            'recycle-bin'     => 'Blue recycling bin',
            'household-bin'   => 'Household waste bin',
            'cardboard-bales' => 'Baled cardboard ready for recycling',
            'cardboard'       => 'Sorted cardboard',
            'plastic-bottles' => 'Plastic bottles collected for recycling',
            'recycling-yard'  => 'Recycling yard with sorted material',
            'hands-plant'     => 'Hands holding a young plant',
            'forest-aerial'   => 'Aerial view of forest and clean water',
            'forest-river'    => 'Clean river through a forest',
            'wind-energy'     => 'Wind turbines at sunset',
            'ocean-plastic'   => 'Plastic pollution in the ocean',
            'plastic-litter'  => 'Plastic litter on the ground',
            'cleanup-beach'   => 'Volunteers cleaning up a beach',
            'cleanup-team'    => 'Clean-up team collecting litter',
            'volunteer'       => 'Community volunteer',
            'sorting'         => 'Team sorting waste materials',
            'business'        => 'Business clients in a meeting',
            'market'          => 'Busy local market',
            'pollution'       => 'Industrial air pollution',
        ];

        foreach ($alts as $name => $alt) {
            $path = $this->img($name);
            $file = public_path($path);
            $size = @getimagesize($file) ?: [null, null];
            WebsiteMedia::firstOrCreate(['path' => $path], [
                'original_name' => "{$name}.jpg",
                'mime'          => 'image/jpeg',
                'size'          => is_file($file) ? filesize($file) : 0,
                'width'         => $size[0],
                'height'        => $size[1],
                'alt'           => $alt,
            ]);
        }
    }

    /** @return array<string, WebsitePage> keyed by slug */
    protected function seedPages(): array
    {
        $created = [];
        foreach ($this->pages() as $slug => $page) {
            $existing = WebsitePage::where('slug', $slug)->first();
            if ($existing) {
                $created[$slug] = $existing;
                continue;
            }

            $model = WebsitePage::create([
                'title'            => $page['title'],
                'slug'             => $slug,
                'status'           => 'Published',
                'is_home'          => $slug === 'home' && !WebsitePage::where('is_home', true)->exists(),
                'meta_title'       => $page['meta_title'] ?? null,
                'meta_description' => $page['meta_description'] ?? null,
            ]);

            foreach ($page['sections'] as $i => [$type, $data]) {
                $visible = !($data['_hidden'] ?? false);
                unset($data['_hidden']);
                WebsiteSection::create([
                    'website_page_id' => $model->id,
                    'type'            => $type,
                    'data'            => $data,
                    'is_visible'      => $visible,
                    'sort_order'      => $i + 1,
                ]);
            }

            $created[$slug] = $model;
        }
        return $created;
    }

    protected function seedMenus(array $pages): void
    {
        if (WebsiteMenuItem::exists()) return;

        $menus = [
            'header'   => ['home' => 'Home', 'about' => 'About', 'services' => 'Services', 'how-it-works' => 'How It Works', 'impact' => 'Impact', 'pricing' => 'Pricing', 'gallery' => 'Gallery', 'news' => 'News', 'contact' => 'Contact'],
            'footer_1' => ['about' => 'About us', 'impact' => 'Our impact', 'gallery' => 'Gallery', 'news' => 'News'],
            'footer_2' => ['services' => 'All services', 'how-it-works' => 'How it works', 'pricing' => 'Pricing', 'request-a-bin' => 'Request a bin'],
            'footer_3' => ['contact' => 'Contact us', 'privacy-policy' => 'Privacy policy', 'terms' => 'Terms of service'],
        ];

        foreach ($menus as $location => $items) {
            $order = 1;
            foreach ($items as $slug => $label) {
                if (!isset($pages[$slug])) continue;
                WebsiteMenuItem::create([
                    'location'        => $location,
                    'label'           => $label,
                    'website_page_id' => $pages[$slug]->id,
                    'sort_order'      => $order++,
                ]);
            }
        }
    }

    protected function seedPosts(): void
    {
        if (WebsitePost::exists()) return;

        $posts = [
            [
                'title'    => '5 simple ways to cut household waste this month',
                'category' => 'Tips',
                'cover'    => 'bins-sorted',
                'excerpt'  => 'Small habits at home add up to fewer full bins, cleaner neighbourhoods and lower costs. Here is where to start.',
                'body'     => "Most household waste can be reduced with a few simple habits. Here are five that make a real difference.\n\n## 1. Separate as you go\nKeep two containers in the kitchen — one for food scraps and one for dry recyclables like plastic bottles, cans and cardboard. Sorting at the source keeps recyclables clean and valuable.\n\n## 2. Carry a reusable bag\nSingle-use plastic bags are one of the biggest sources of litter in our gutters and drains. A sturdy reusable bag pays for itself quickly.\n\n## 3. Buy in bulk where you can\nLarger packs of rice, oil and detergents mean less packaging per use.\n\n## 4. Compost food scraps\nPeels, leaves and food leftovers can be composted into rich soil for your garden instead of filling your bin.\n\n## 5. Keep your bin lid closed\nA closed lid keeps out rain, animals and flies — and keeps your compound clean between collections.\n\nNeed a proper bin? **Request one from us** and we will deliver it to your door.",
            ],
            [
                'title'    => 'Weekly or monthly collection: which plan is right for you?',
                'category' => 'Guides',
                'cover'    => 'hero-collection',
                'excerpt'  => 'Choosing the right collection frequency keeps your compound clean without paying for more than you need.',
                'body'     => "We offer flexible collection schedules because no two households or businesses produce the same amount of waste.\n\n## Weekly collection\nBest for families, compound houses, shops, restaurants and offices. If your bin is full within a week, weekly collection keeps things hygienic and odour-free.\n\n## Monthly collection\nIdeal for small households, single occupants or homes that already compost and recycle. You pay less while still getting a dependable service.\n\n## Not sure?\nStart with the plan that fits today and switch any time — just call or message us. Our team can also recommend a bin size based on your needs.",
            ],
            [
                'title'    => 'Why proper waste disposal protects your family’s health',
                'category' => 'Community',
                'cover'    => 'cleanup-team',
                'excerpt'  => 'Uncollected waste attracts pests, blocks drains and spreads disease. Regular collection is one of the simplest ways to protect your household.',
                'body'     => "Waste left in open spaces or burnt in compounds does more harm than many people realise.\n\n- **Blocked drains** cause flooding during the rainy season.\n- **Flies, rats and mosquitoes** breed in uncollected rubbish and spread illnesses like cholera, typhoid and malaria.\n- **Burning waste** releases toxic smoke that harms the lungs — especially for children and the elderly.\n\nRegular, scheduled collection removes waste before it becomes a problem. Every household that signs up helps keep the whole street cleaner and safer.",
            ],
        ];

        foreach ($posts as $i => $p) {
            WebsitePost::create([
                'title'        => $p['title'],
                'slug'         => \Illuminate\Support\Str::slug($p['title']),
                'category'     => $p['category'],
                'excerpt'      => $p['excerpt'],
                'body'         => $p['body'],
                'cover_image'  => $this->img($p['cover']),
                'status'       => 'Published',
                'published_at' => now()->subDays(($i + 1) * 6),
            ]);
        }
    }

    protected function pages(): array
    {
        $ctaRequest = ['cta', [
            'heading'         => 'Ready for a cleaner compound?',
            'text'            => 'Get a bin delivered and your first collection scheduled in days. No stress, no mess.',
            'image'           => $this->img('bins-colour'),
            'cta_label'       => 'Request a Bin',
            'cta_url'         => '/request-a-bin',
            'secondary_label' => 'Talk to us',
            'secondary_url'   => '/contact',
        ]];

        $scheduleRows = [
            ['title' => 'Household — Standard', 'frequency' => 'Weekly', 'days' => 'Same day every week in your zone', 'note' => 'Families, compound houses'],
            ['title' => 'Household — Lite', 'frequency' => 'Monthly', 'days' => 'One scheduled pickup each month', 'note' => 'Small homes, single occupants'],
            ['title' => 'Commercial', 'frequency' => 'Weekly or more', 'days' => 'Agreed with your business', 'note' => 'Shops, offices, restaurants, schools'],
            ['title' => 'Special / bulk pickup', 'frequency' => 'On request', 'days' => 'Booked when you need it', 'note' => 'Renovations, clear-outs, events'],
        ];

        $faq = [
            ['question' => 'How do I sign up for waste collection?', 'answer' => 'Fill in the Request a Bin form or call us. We confirm your location and zone, deliver your bin and add you to the collection schedule.'],
            ['question' => 'Which areas do you cover?', 'answer' => 'We serve a growing number of zones. Send us your location and we will confirm coverage and your collection day.'],
            ['question' => 'Can I choose weekly or monthly collection?', 'answer' => 'Yes. Weekly suits most families and businesses; monthly suits smaller households. You can switch plans at any time.'],
            ['question' => 'How do I pay?', 'answer' => 'You can pay with mobile money or card through the secure payment link on your invoice, or pay our collector in cash and receive a receipt.'],
            ['question' => 'What if my collection is missed?', 'answer' => 'Call or WhatsApp us and we will arrange a pickup as quickly as possible.'],
            ['question' => 'Do you collect bulky items?', 'answer' => 'Yes — book a special pickup for furniture, renovation debris, garden waste or event clean-ups.'],
        ];

        return [
            'home' => [
                'title' => 'Home',
                'meta_title' => 'Waste Collection & Bin Distribution in Ghana',
                'sections' => [
                    ['hero', [
                        'eyebrow'         => 'Reliable waste collection',
                        'heading'         => 'Cleaner homes, cleaner streets, cleaner Ghana.',
                        'subheading'      => 'Scheduled weekly and monthly rubbish collection, bins delivered to your door and responsible recycling — for homes and businesses.',
                        'image'           => $this->img('hero-collection'),
                        'cta_label'       => 'Request a Bin',
                        'cta_url'         => '/request-a-bin',
                        'secondary_label' => 'See our plans',
                        'secondary_url'   => '/pricing',
                        'badges'          => [['text' => 'Weekly & monthly plans'], ['text' => 'Mobile money payments'], ['text' => 'Bins delivered']],
                    ]],
                    ['stats', [
                        'background' => 'white',
                        'items' => [
                            ['value' => '{customers}+', 'label' => 'Homes & businesses served', 'icon' => 'home'],
                            ['value' => '{zones}', 'label' => 'Collection zones', 'icon' => 'map-pin'],
                            ['value' => '{weight}', 'label' => 'Waste weighed & disposed', 'icon' => 'scale'],
                            ['value' => '7 days', 'label' => 'Customer support', 'icon' => 'chat'],
                        ],
                    ]],
                    ['features', [
                        'eyebrow' => 'Why Elite Waste',
                        'heading' => 'Waste collection you never have to think about',
                        'subheading' => 'We turn rubbish from a daily headache into a simple, dependable service.',
                        'background' => 'light',
                        'columns' => '3',
                        'items' => [
                            ['icon' => 'calendar', 'title' => 'On-schedule pickups', 'text' => 'Your zone gets a fixed collection day, so you always know when we are coming.'],
                            ['icon' => 'trash', 'title' => 'Bins delivered', 'text' => 'Durable, lidded bins brought to your door — sized for your household or business.'],
                            ['icon' => 'recycle', 'title' => 'Responsible recycling', 'text' => 'Plastics, cardboard and metals are separated and sent for recycling where possible.'],
                            ['icon' => 'device-phone', 'title' => 'Easy payments', 'text' => 'Pay by mobile money or card from a secure link, or in cash with an instant receipt.'],
                            ['icon' => 'shield-check', 'title' => 'Trained crews', 'text' => 'Uniformed, courteous collectors who handle your bins with care.'],
                            ['icon' => 'chat', 'title' => 'SMS reminders', 'text' => 'Friendly reminders before collection days and when bills are due.'],
                        ],
                    ]],
                    ['services', [
                        'eyebrow' => 'What we do',
                        'heading' => 'Services for every home and business',
                        'subheading' => 'From a single household bin to full commercial contracts.',
                        'items' => [
                            ['image' => $this->img('household-bin'), 'title' => 'Household collection', 'text' => 'Weekly or monthly kerbside pickup for homes and compound houses.', 'link_label' => 'Learn more', 'link_url' => '/services'],
                            ['image' => $this->img('market'), 'title' => 'Commercial & markets', 'text' => 'Reliable collection for shops, offices, restaurants, schools and markets.', 'link_label' => 'Get a quote', 'link_url' => '/contact'],
                            ['image' => $this->img('bins-colour'), 'title' => 'Bin distribution', 'text' => 'Quality bins in several sizes, delivered and replaced when needed.', 'link_label' => 'Request a bin', 'link_url' => '/request-a-bin'],
                        ],
                    ]],
                    ['steps', [
                        'eyebrow' => 'How it works',
                        'heading' => 'Up and running in three simple steps',
                        'background' => 'light',
                        'items' => [
                            ['icon' => 'chat', 'title' => 'Request', 'text' => 'Tell us where you are and choose a weekly or monthly plan.'],
                            ['icon' => 'truck', 'title' => 'Receive your bin', 'text' => 'We deliver your bin and confirm your collection day by SMS.'],
                            ['icon' => 'sparkles', 'title' => 'Enjoy a clean space', 'text' => 'We collect on schedule — you just put the bin out.'],
                        ],
                    ]],
                    ['image_text', [
                        'eyebrow' => 'For households',
                        'heading' => 'A clean compound, every single week',
                        'body' => "No more piles of rubbish, burning waste or chasing tricycle collectors. Our crews collect on a fixed day in your zone and keep a record of every pickup.\n\nYou get SMS reminders, simple invoices and many ways to pay.",
                        'image' => $this->img('bins-sorted'),
                        'image_position' => 'right',
                        'bullets' => [['text' => 'Fixed collection day per zone'], ['text' => 'Bins in 120L and 240L sizes'], ['text' => 'Pay by MoMo, card or cash']],
                        'cta_label' => 'Start today',
                        'cta_url' => '/request-a-bin',
                    ]],
                    ['schedule', [
                        'eyebrow' => 'Collection plans',
                        'heading' => 'Weekly or monthly — you choose',
                        'subheading' => 'Pick the frequency that fits how much waste you produce. Switch at any time.',
                        'background' => 'light',
                        'items' => $scheduleRows,
                        'footnote' => 'Exact collection days depend on your zone and are confirmed when you sign up.',
                    ]],
                    ['impact', [
                        'eyebrow' => 'Our impact',
                        'heading' => 'Every pickup keeps waste out of drains, gutters and the sea',
                        'body' => 'Uncollected rubbish blocks drains, causes flooding and spreads disease. Scheduled collection and recycling protect our communities and environment.',
                        'image' => $this->img('forest-aerial'),
                        'items' => [
                            ['value' => '{weight}', 'label' => 'Waste weighed & disposed properly'],
                            ['value' => '{customers}+', 'label' => 'Households & businesses'],
                            ['value' => '{zones}', 'label' => 'Zones served'],
                        ],
                        'cta_label' => 'See our impact',
                        'cta_url' => '/impact',
                    ]],
                    ['plans', [
                        'eyebrow' => 'Pricing',
                        'heading' => 'Simple, honest pricing',
                        'subheading' => 'No hidden charges. Pay monthly by mobile money, card or cash.',
                        'source' => 'service_plans',
                        'currency' => 'GH₵',
                        'plan_cta_label' => 'Get started',
                        'plan_cta_url' => '/request-a-bin',
                        'items' => [
                            ['name' => 'Monthly Lite', 'price' => '—', 'period' => '/ month', 'description' => 'For small households.', 'features' => "1 collection per month\nStandard bin\nSMS reminders", 'highlighted' => 'no'],
                            ['name' => 'Weekly Standard', 'price' => '—', 'period' => '/ month', 'description' => 'Our most popular plan.', 'features' => "Weekly collection\nStandard bin\nSMS reminders\nPriority support", 'highlighted' => 'yes'],
                            ['name' => 'Commercial', 'price' => 'Custom', 'period' => '', 'description' => 'For businesses and institutions.', 'features' => "Flexible frequency\nMultiple bins\nDedicated account manager", 'highlighted' => 'no'],
                        ],
                    ]],
                    ['gallery', [
                        'eyebrow' => 'In the field',
                        'heading' => 'Our work in pictures',
                        'items' => [
                            ['image' => $this->img('hero-collection'), 'caption' => 'Daily collection rounds'],
                            ['image' => $this->img('cleanup-beach'), 'caption' => 'Community clean-up'],
                            ['image' => $this->img('cardboard-bales'), 'caption' => 'Cardboard baled for recycling'],
                            ['image' => $this->img('bins-colour'), 'caption' => 'Colour-coded bins'],
                            ['image' => $this->img('sorting'), 'caption' => 'Sorting materials'],
                            ['image' => $this->img('plastic-bottles'), 'caption' => 'Plastic recovered'],
                        ],
                    ]],
                    ['testimonials', [
                        '_hidden' => true,
                        'eyebrow' => 'Testimonials',
                        'heading' => 'What our customers say',
                        'background' => 'light',
                        'items' => [
                            ['quote' => 'Replace this with a real quote from one of your customers before showing this section.', 'name' => 'Customer name', 'role' => 'Area / neighbourhood', 'image' => null],
                            ['quote' => 'Replace this with a real quote from one of your customers before showing this section.', 'name' => 'Customer name', 'role' => 'Business name', 'image' => null],
                            ['quote' => 'Replace this with a real quote from one of your customers before showing this section.', 'name' => 'Customer name', 'role' => 'Area / neighbourhood', 'image' => null],
                        ],
                    ]],
                    ['faq', [
                        'eyebrow' => 'FAQ',
                        'heading' => 'Questions, answered',
                        'items' => array_slice($faq, 0, 4),
                    ]],
                    ['posts', [
                        'eyebrow' => 'News & tips',
                        'heading' => 'From the Elite Waste blog',
                        'background' => 'light',
                        'count' => 3,
                        'paginate' => 'no',
                    ]],
                    $ctaRequest,
                    ['newsletter', [
                        'heading' => 'Get tips and service updates',
                        'text' => 'Collection schedule changes, recycling tips and offers — straight to your inbox. No spam.',
                        'button' => 'Subscribe',
                        'background' => 'white',
                    ]],
                ],
            ],

            'about' => [
                'title' => 'About Us',
                'meta_description' => 'Elite Waste Management provides dependable waste collection, bin distribution and recycling for homes and businesses in Ghana.',
                'sections' => [
                    ['page_header', ['eyebrow' => 'About us', 'heading' => 'Keeping our communities clean, one pickup at a time', 'subheading' => 'We are a Ghanaian waste management company on a mission to make proper waste disposal simple, affordable and dependable.', 'image' => $this->img('cleanup-team')]],
                    ['image_text', [
                        'eyebrow' => 'Our story',
                        'heading' => 'Built to solve a problem we saw every day',
                        'body' => "Overflowing bins, burning rubbish and blocked drains are part of daily life in too many neighbourhoods. We started Elite Waste Management to change that — with organised zones, fixed collection days and proper records for every customer.\n\nToday our crews serve households, businesses and institutions, and every pickup is tracked from the bin to the disposal site.",
                        'image' => $this->img('sorting'),
                        'image_position' => 'left',
                    ]],
                    ['stats', ['background' => 'brand', 'items' => [
                        ['value' => '{customers}+', 'label' => 'Customers', 'icon' => 'users'],
                        ['value' => '{zones}', 'label' => 'Zones', 'icon' => 'map-pin'],
                        ['value' => '{weight}', 'label' => 'Waste weighed', 'icon' => 'scale'],
                    ]]],
                    ['features', [
                        'eyebrow' => 'Our values',
                        'heading' => 'What we stand for',
                        'columns' => '4',
                        'items' => [
                            ['icon' => 'clock', 'title' => 'Reliability', 'text' => 'We show up when we say we will.'],
                            ['icon' => 'heart', 'title' => 'Respect', 'text' => 'For our customers, our crews and our environment.'],
                            ['icon' => 'leaf', 'title' => 'Sustainability', 'text' => 'Recover, recycle and reduce wherever we can.'],
                            ['icon' => 'check-circle', 'title' => 'Accountability', 'text' => 'Every pickup and every payment is recorded.'],
                        ],
                    ]],
                    ['image_text', [
                        'eyebrow' => 'Our mission',
                        'heading' => 'A cleaner, healthier Ghana',
                        'body' => 'We believe every home and business deserves dependable waste collection. By combining trained crews, organised routes and simple digital billing, we make it easy to do the right thing with waste.',
                        'image' => $this->img('hands-plant'),
                        'image_position' => 'right',
                        'background' => 'light',
                        'bullets' => [['text' => 'Organised collection zones'], ['text' => 'Digital records and receipts'], ['text' => 'Community clean-up support']],
                    ]],
                    $ctaRequest,
                ],
            ],

            'services' => [
                'title' => 'Services',
                'meta_description' => 'Household and commercial waste collection, bin distribution, recycling and special pickups.',
                'sections' => [
                    ['page_header', ['eyebrow' => 'Our services', 'heading' => 'Complete waste solutions', 'subheading' => 'Whatever you need to get rid of, we have a dependable way to collect it.', 'image' => $this->img('recycling-yard')]],
                    ['services', [
                        'heading' => 'What we offer',
                        'items' => [
                            ['image' => $this->img('household-bin'), 'title' => 'Household collection', 'text' => 'Weekly or monthly scheduled pickups for homes, flats and compound houses.', 'link_label' => 'Request a bin', 'link_url' => '/request-a-bin'],
                            ['image' => $this->img('market'), 'title' => 'Commercial collection', 'text' => 'Shops, offices, restaurants, hotels, schools, churches and markets.', 'link_label' => 'Get a quote', 'link_url' => '/contact'],
                            ['image' => $this->img('bins-colour'), 'title' => 'Bin distribution', 'text' => 'Durable lidded bins in different sizes, delivered and replaced as needed.', 'link_label' => 'Request a bin', 'link_url' => '/request-a-bin'],
                            ['image' => $this->img('cardboard-bales'), 'title' => 'Recycling', 'text' => 'Separation and recovery of plastics, paper, cardboard and metals.', 'link_label' => 'Our impact', 'link_url' => '/impact'],
                            ['image' => $this->img('recycling-yard'), 'title' => 'Special & bulk pickup', 'text' => 'Renovation debris, furniture, garden waste and one-off clear-outs.', 'link_label' => 'Book a pickup', 'link_url' => '/contact'],
                            ['image' => $this->img('cleanup-beach'), 'title' => 'Event & community clean-ups', 'text' => 'Before-and-after clean-up for events, funerals and community days.', 'link_label' => 'Talk to us', 'link_url' => '/contact'],
                        ],
                    ]],
                    ['image_text', [
                        'eyebrow' => 'For businesses',
                        'heading' => 'Commercial waste handled professionally',
                        'body' => 'A clean business is good business. We agree a collection frequency that fits your volume, supply the right bins and give you clear monthly invoices.',
                        'image' => $this->img('business'),
                        'image_position' => 'left',
                        'background' => 'light',
                        'bullets' => [['text' => 'Flexible frequency — daily, weekly or custom'], ['text' => 'Multiple bins and sites on one account'], ['text' => 'Monthly invoices and payment records']],
                        'cta_label' => 'Request a quote',
                        'cta_url' => '/contact',
                    ]],
                    ['image_text', [
                        'eyebrow' => 'Bin distribution',
                        'heading' => 'The right bin for every space',
                        'body' => 'Our sturdy bins have tight-fitting lids to keep out rain, flies and animals. We deliver them to your door and replace damaged bins quickly.',
                        'image' => $this->img('bins-sorted'),
                        'image_position' => 'right',
                        'bullets' => [['text' => 'Household and commercial sizes'], ['text' => 'Colour-coded bins for recycling'], ['text' => 'Delivery included']],
                        'cta_label' => 'Request a bin',
                        'cta_url' => '/request-a-bin',
                    ]],
                    $ctaRequest,
                ],
            ],

            'how-it-works' => [
                'title' => 'How It Works',
                'sections' => [
                    ['page_header', ['eyebrow' => 'How it works', 'heading' => 'Simple from day one', 'subheading' => 'Signing up takes minutes. Here is what happens next.', 'image' => $this->img('bins-colour')]],
                    ['steps', [
                        'heading' => 'From request to regular collection',
                        'items' => [
                            ['icon' => 'chat', 'title' => 'Send a request', 'text' => 'Use our online form, call or WhatsApp us with your location.'],
                            ['icon' => 'map-pin', 'title' => 'We confirm your zone', 'text' => 'We confirm coverage, your collection day and the right plan.'],
                            ['icon' => 'truck', 'title' => 'Bin delivery', 'text' => 'Your bin is delivered and you are added to the schedule.'],
                            ['icon' => 'calendar', 'title' => 'Regular pickups', 'text' => 'Put your bin out on collection day — we take care of the rest.'],
                        ],
                    ]],
                    ['schedule', [
                        'eyebrow' => 'Collection frequency',
                        'heading' => 'Choose how often we collect',
                        'background' => 'light',
                        'items' => $scheduleRows,
                        'footnote' => 'Exact collection days depend on your zone.',
                    ]],
                    ['features', [
                        'eyebrow' => 'Billing',
                        'heading' => 'Paying is just as easy',
                        'columns' => '3',
                        'items' => [
                            ['icon' => 'chat', 'title' => 'SMS invoice', 'text' => 'You receive an SMS with your bill and a secure payment link.'],
                            ['icon' => 'device-phone', 'title' => 'Pay your way', 'text' => 'Mobile money, card, or cash to our collector.'],
                            ['icon' => 'check-circle', 'title' => 'Instant receipt', 'text' => 'Every payment is recorded and receipted.'],
                        ],
                    ]],
                    ['faq', ['heading' => 'Frequently asked questions', 'background' => 'light', 'items' => $faq]],
                    $ctaRequest,
                ],
            ],

            'impact' => [
                'title' => 'Our Impact',
                'meta_description' => 'How scheduled waste collection and recycling protect health and the environment.',
                'sections' => [
                    ['page_header', ['eyebrow' => 'Impact', 'heading' => 'Cleaner communities, healthier lives', 'subheading' => 'Proper waste management is one of the most powerful ways to protect public health and the environment.', 'image' => $this->img('forest-river')]],
                    ['stats', [
                        'heading' => 'Our impact so far',
                        'subheading' => 'These numbers update automatically from our operations.',
                        'items' => [
                            ['value' => '{weight}', 'label' => 'Waste weighed & disposed', 'icon' => 'scale'],
                            ['value' => '{customers}+', 'label' => 'Homes & businesses', 'icon' => 'home'],
                            ['value' => '{zones}', 'label' => 'Zones served', 'icon' => 'map-pin'],
                        ],
                    ]],
                    ['image_text', [
                        'eyebrow' => 'The problem',
                        'heading' => 'Waste that is not collected does not disappear',
                        'body' => "It blocks gutters and causes flooding. It ends up in rivers and the sea. Burnt waste fills the air with toxic smoke.\n\nEvery household on a collection schedule is one less source of this pollution.",
                        'image' => $this->img('ocean-plastic'),
                        'image_position' => 'left',
                        'background' => 'light',
                    ]],
                    ['features', [
                        'eyebrow' => 'What we do about it',
                        'heading' => 'Our environmental commitments',
                        'columns' => '3',
                        'items' => [
                            ['icon' => 'recycle', 'title' => 'Recover recyclables', 'text' => 'Plastics, cardboard and metals are separated for recycling partners.'],
                            ['icon' => 'scale', 'title' => 'Measure everything', 'text' => 'Every truckload is weighed so we can track and reduce what goes to landfill.'],
                            ['icon' => 'users', 'title' => 'Support communities', 'text' => 'We take part in clean-up exercises and teach good waste habits.'],
                        ],
                    ]],
                    ['impact', [
                        'heading' => 'Small actions, big change',
                        'body' => 'When neighbourhoods sign up together, streets stay clean, drains stay clear and families stay healthy.',
                        'image' => $this->img('wind-energy'),
                        'items' => [],
                        'cta_label' => 'Join us',
                        'cta_url' => '/request-a-bin',
                    ]],
                    ['gallery', [
                        'heading' => 'Impact in action',
                        'items' => [
                            ['image' => $this->img('cleanup-beach'), 'caption' => 'Beach clean-up'],
                            ['image' => $this->img('cleanup-team'), 'caption' => 'Community volunteers'],
                            ['image' => $this->img('cardboard'), 'caption' => 'Recovered cardboard'],
                            ['image' => $this->img('plastic-bottles'), 'caption' => 'Plastic for recycling'],
                        ],
                    ]],
                    $ctaRequest,
                ],
            ],

            'pricing' => [
                'title' => 'Pricing',
                'sections' => [
                    ['page_header', ['eyebrow' => 'Pricing', 'heading' => 'Affordable plans for every need', 'subheading' => 'Choose weekly or monthly collection. Pay by mobile money, card or cash.', 'image' => $this->img('household-bin')]],
                    ['plans', [
                        'heading' => 'Our plans',
                        'source' => 'service_plans',
                        'currency' => 'GH₵',
                        'plan_cta_label' => 'Get started',
                        'plan_cta_url' => '/request-a-bin',
                        'items' => [],
                    ]],
                    ['faq', ['heading' => 'Pricing questions', 'background' => 'light', 'items' => [
                        $faq[3],
                        ['question' => 'Are there any hidden charges?', 'answer' => 'No. You pay the plan price shown on your invoice. Special pickups are quoted upfront.'],
                        ['question' => 'Do businesses pay differently?', 'answer' => 'Commercial pricing depends on your volume and frequency. Contact us for a quote.'],
                        $faq[2],
                    ]]],
                    $ctaRequest,
                ],
            ],

            'gallery' => [
                'title' => 'Gallery',
                'sections' => [
                    ['page_header', ['eyebrow' => 'Gallery', 'heading' => 'See us at work', 'subheading' => 'Our crews, our bins and the communities we serve.', 'image' => $this->img('cleanup-beach')]],
                    ['gallery', [
                        'items' => array_map(fn ($n) => ['image' => $this->img($n), 'caption' => ''], [
                            'hero-collection', 'bins-colour', 'cleanup-beach', 'cardboard-bales', 'sorting', 'bins-sorted',
                            'cleanup-team', 'plastic-bottles', 'recycling-yard', 'recycle-bin', 'cardboard', 'volunteer',
                        ]),
                    ]],
                    $ctaRequest,
                ],
            ],

            'news' => [
                'title' => 'News',
                'sections' => [
                    ['page_header', ['eyebrow' => 'News & tips', 'heading' => 'Stories, tips and updates', 'subheading' => 'Practical advice on waste, recycling and keeping your community clean.', 'image' => $this->img('hands-plant')]],
                    ['posts', ['count' => 9, 'paginate' => 'yes']],
                    ['newsletter', ['heading' => 'Never miss an update', 'text' => 'Get new posts and service updates by email.', 'button' => 'Subscribe', 'background' => 'light']],
                ],
            ],

            'contact' => [
                'title' => 'Contact',
                'meta_description' => 'Call, WhatsApp or message Elite Waste Management.',
                'sections' => [
                    ['page_header', ['eyebrow' => 'Contact', 'heading' => 'We are here to help', 'subheading' => 'Questions, quotes, missed pickups or special collections — get in touch.', 'image' => $this->img('business')]],
                    ['form', [
                        'heading' => 'Send us a message',
                        'subheading' => 'We usually reply within one working day.',
                        'form_type' => 'contact',
                        'submit_label' => 'Send message',
                        'show_contact' => 'yes',
                    ]],
                ],
            ],

            'request-a-bin' => [
                'title' => 'Request a Bin',
                'meta_description' => 'Sign up for waste collection and get a bin delivered to your door.',
                'sections' => [
                    ['page_header', ['eyebrow' => 'Get started', 'heading' => 'Request a bin & start collection', 'subheading' => 'Tell us where you are and how often you need collection. We will call to confirm.', 'image' => $this->img('bins-colour')]],
                    ['form', [
                        'heading' => 'Your details',
                        'subheading' => 'Fields marked * are required.',
                        'form_type' => 'bin_request',
                        'submit_label' => 'Request my bin',
                        'show_contact' => 'no',
                        'image' => $this->img('bins-sorted'),
                    ]],
                    ['steps', [
                        'heading' => 'What happens next',
                        'background' => 'light',
                        'items' => [
                            ['icon' => 'phone', 'title' => 'We call you', 'text' => 'We confirm your location, plan and collection day.'],
                            ['icon' => 'truck', 'title' => 'Bin delivered', 'text' => 'Your bin arrives within a few days.'],
                            ['icon' => 'calendar', 'title' => 'Collection starts', 'text' => 'Put your bin out on your collection day.'],
                        ],
                    ]],
                ],
            ],

            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'sections' => [
                    ['page_header', ['heading' => 'Privacy Policy', 'subheading' => 'How we collect and use your information.']],
                    ['rich_text', ['width' => 'narrow', 'body' => "*Replace this text with your own privacy policy.*\n\n## Information we collect\nWhen you sign up or contact us we collect your name, phone number, email address and location so we can provide waste collection services and send you bills and reminders.\n\n## How we use it\nWe use your information to schedule collections, send invoices and service messages, process payments and respond to your enquiries.\n\n## Sharing\nWe do not sell your information. Payment details are processed securely by our payment provider.\n\n## Contact\nFor any privacy questions, please contact us using the details on our Contact page."]],
                ],
            ],

            'terms' => [
                'title' => 'Terms of Service',
                'sections' => [
                    ['page_header', ['heading' => 'Terms of Service', 'subheading' => 'The terms that apply to our services.']],
                    ['rich_text', ['width' => 'narrow', 'body' => "*Replace this text with your own terms of service.*\n\n## Service\nWe collect waste from your premises according to your chosen plan and your zone's collection schedule.\n\n## Payment\nBills are payable by the due date shown on your invoice. Services may be paused for unpaid bills.\n\n## Bins\nBins supplied by us remain our property unless purchased. Please report damaged or missing bins promptly.\n\n## Changes\nWe may update these terms from time to time. Continued use of the service means you accept the updated terms."]],
                ],
            ],
        ];
    }
}
