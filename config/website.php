<?php

/*
|--------------------------------------------------------------------------
| Public website (CMS) definitions
|--------------------------------------------------------------------------
|
| Every section type the page builder offers, and every site-wide setting,
| is described here. The admin editor forms are generated from these field
| lists and the public templates live in resources/views/site/sections/{type}.
|
| Field types: text, textarea (plain paragraphs), markdown, image, url,
| number, select (needs 'options'), icon, color, repeater (needs 'fields').
|
*/

$cta = [
    'cta_label' => ['type' => 'text', 'label' => 'Button text'],
    'cta_url'   => ['type' => 'url', 'label' => 'Button link'],
];

$intro = [
    'eyebrow'    => ['type' => 'text', 'label' => 'Small label above heading'],
    'heading'    => ['type' => 'text', 'label' => 'Heading'],
    'subheading' => ['type' => 'textarea', 'label' => 'Intro text'],
];

$background = [
    'background' => ['type' => 'select', 'label' => 'Background', 'options' => [
        'white' => 'White', 'light' => 'Light tint', 'dark' => 'Dark', 'brand' => 'Brand colour',
    ], 'default' => 'white'],
];

return [

    'reserved_slugs' => [
        'login', 'logout', 'register', 'dashboard', 'profile', 'my', 'platform', 'website',
        'users', 'service_plans', 'zones', 'customers', 'subscriptions', 'invoices', 'weight-logs',
        'payments', 'sms_broadcasts', 'settings', 'export', 'reports', 'expense_categories', 'vendors',
        'expense_budgets', 'recurring_expenses', 'expenses', 'webhook', 'pay', 'forms', 'storage',
        'images', 'build', 'forgot-password', 'reset-password', 'verify-email', 'confirm-password',
        'password', 'email', 'up',
    ],

    'form_types' => [
        'contact'     => ['label' => 'Contact message'],
        'quote'       => ['label' => 'Quote request'],
        'bin_request' => ['label' => 'Bin request'],
        'pickup'      => ['label' => 'Special pickup'],
    ],

    'icons' => [
        'truck', 'trash', 'recycle', 'leaf', 'calendar', 'map-pin', 'phone', 'envelope', 'shield-check',
        'users', 'building', 'home', 'clock', 'chart', 'globe', 'heart', 'check-circle', 'bolt', 'scale',
        'sparkles', 'chat', 'cube', 'currency', 'sun', 'device-phone',
    ],

    /*
    |--------------------------------------------------------------------------
    | Section types
    |--------------------------------------------------------------------------
    */
    'sections' => [

        'hero' => [
            'label' => 'Hero banner',
            'description' => 'Full-width opening banner with a photo, headline and buttons.',
            'fields' => [
                'eyebrow'         => ['type' => 'text', 'label' => 'Small label above heading'],
                'heading'         => ['type' => 'text', 'label' => 'Headline'],
                'subheading'      => ['type' => 'textarea', 'label' => 'Supporting text'],
                'image'           => ['type' => 'image', 'label' => 'Background photo'],
                'cta_label'       => ['type' => 'text', 'label' => 'Main button text'],
                'cta_url'         => ['type' => 'url', 'label' => 'Main button link'],
                'secondary_label' => ['type' => 'text', 'label' => 'Second button text'],
                'secondary_url'   => ['type' => 'url', 'label' => 'Second button link'],
                'badges'          => ['type' => 'repeater', 'label' => 'Highlights under the buttons', 'item_label' => 'Highlight', 'fields' => [
                    'text' => ['type' => 'text', 'label' => 'Text'],
                ]],
            ],
        ],

        'page_header' => [
            'label' => 'Page header',
            'description' => 'Banner at the top of inner pages.',
            'fields' => [
                'eyebrow'    => ['type' => 'text', 'label' => 'Small label above heading'],
                'heading'    => ['type' => 'text', 'label' => 'Heading'],
                'subheading' => ['type' => 'textarea', 'label' => 'Intro text'],
                'image'      => ['type' => 'image', 'label' => 'Background photo'],
            ],
        ],

        'stats' => [
            'label' => 'Numbers / statistics',
            'description' => 'A row of big numbers, e.g. homes served or tonnes collected.',
            'fields' => $intro + $background + [
                'items' => ['type' => 'repeater', 'label' => 'Numbers', 'item_label' => 'Number', 'fields' => [
                    'value' => ['type' => 'text', 'label' => 'Number (e.g. 12,000+)'],
                    'label' => ['type' => 'text', 'label' => 'Label'],
                    'icon'  => ['type' => 'icon', 'label' => 'Icon'],
                ]],
            ],
        ],

        'features' => [
            'label' => 'Feature cards (icons)',
            'description' => 'Grid of icon cards — reasons to choose you, values, benefits.',
            'fields' => $intro + $background + [
                'columns' => ['type' => 'select', 'label' => 'Columns', 'options' => ['2' => '2', '3' => '3', '4' => '4'], 'default' => '3'],
                'items'   => ['type' => 'repeater', 'label' => 'Cards', 'item_label' => 'Card', 'fields' => [
                    'icon'  => ['type' => 'icon', 'label' => 'Icon'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'text'  => ['type' => 'textarea', 'label' => 'Text'],
                ]],
            ],
        ],

        'image_text' => [
            'label' => 'Image + text',
            'description' => 'A photo beside a block of text, with optional checklist and button.',
            'fields' => [
                'eyebrow'        => ['type' => 'text', 'label' => 'Small label above heading'],
                'heading'        => ['type' => 'text', 'label' => 'Heading'],
                'body'           => ['type' => 'markdown', 'label' => 'Text'],
                'image'          => ['type' => 'image', 'label' => 'Photo'],
                'image_position' => ['type' => 'select', 'label' => 'Photo position', 'options' => ['left' => 'Left', 'right' => 'Right'], 'default' => 'right'],
                'bullets'        => ['type' => 'repeater', 'label' => 'Checklist', 'item_label' => 'Point', 'fields' => [
                    'text' => ['type' => 'text', 'label' => 'Text'],
                ]],
            ] + $background + $cta,
        ],

        'services' => [
            'label' => 'Service cards (photos)',
            'description' => 'Cards with a photo, title, text and link.',
            'fields' => $intro + $background + [
                'items' => ['type' => 'repeater', 'label' => 'Services', 'item_label' => 'Service', 'fields' => [
                    'image'      => ['type' => 'image', 'label' => 'Photo'],
                    'title'      => ['type' => 'text', 'label' => 'Title'],
                    'text'       => ['type' => 'textarea', 'label' => 'Text'],
                    'link_label' => ['type' => 'text', 'label' => 'Link text'],
                    'link_url'   => ['type' => 'url', 'label' => 'Link'],
                ]],
            ],
        ],

        'steps' => [
            'label' => 'Steps / how it works',
            'description' => 'Numbered steps explaining a process.',
            'fields' => $intro + $background + [
                'items' => ['type' => 'repeater', 'label' => 'Steps', 'item_label' => 'Step', 'fields' => [
                    'icon'  => ['type' => 'icon', 'label' => 'Icon'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'text'  => ['type' => 'textarea', 'label' => 'Text'],
                ]],
            ],
        ],

        'impact' => [
            'label' => 'Impact banner',
            'description' => 'Dark photo banner with a message and big impact numbers.',
            'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Small label above heading'],
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'body'    => ['type' => 'textarea', 'label' => 'Text'],
                'image'   => ['type' => 'image', 'label' => 'Background photo'],
                'items'   => ['type' => 'repeater', 'label' => 'Numbers', 'item_label' => 'Number', 'fields' => [
                    'value' => ['type' => 'text', 'label' => 'Number'],
                    'label' => ['type' => 'text', 'label' => 'Label'],
                ]],
            ] + $cta,
        ],

        'schedule' => [
            'label' => 'Collection plans / schedule',
            'description' => 'Table of collection frequencies (weekly, monthly…) and what they include.',
            'fields' => $intro + $background + [
                'items' => ['type' => 'repeater', 'label' => 'Rows', 'item_label' => 'Row', 'fields' => [
                    'title'     => ['type' => 'text', 'label' => 'Plan / area'],
                    'frequency' => ['type' => 'text', 'label' => 'Frequency (e.g. Weekly)'],
                    'days'      => ['type' => 'text', 'label' => 'Days / timing'],
                    'note'      => ['type' => 'text', 'label' => 'Best for'],
                ]],
                'footnote' => ['type' => 'text', 'label' => 'Note under the table'],
            ],
        ],

        'plans' => [
            'label' => 'Pricing plans',
            'description' => 'Pricing cards — pulled live from your Service Plans, or typed in manually.',
            'fields' => $intro + $background + [
                'source'   => ['type' => 'select', 'label' => 'Where prices come from', 'options' => [
                    'service_plans' => 'Active Service Plans (live from the dashboard)',
                    'manual'        => 'Manual cards below',
                ], 'default' => 'service_plans'],
                'currency' => ['type' => 'text', 'label' => 'Currency label', 'default' => 'GH₵'],
                'plan_cta_label' => ['type' => 'text', 'label' => 'Button text on each card', 'default' => 'Get started'],
                'plan_cta_url'   => ['type' => 'url', 'label' => 'Button link on each card', 'default' => '/request-a-bin'],
                'items'    => ['type' => 'repeater', 'label' => 'Manual cards', 'item_label' => 'Plan', 'fields' => [
                    'name'        => ['type' => 'text', 'label' => 'Name'],
                    'price'       => ['type' => 'text', 'label' => 'Price'],
                    'period'      => ['type' => 'text', 'label' => 'Period (e.g. / month)'],
                    'description' => ['type' => 'textarea', 'label' => 'Short description'],
                    'features'    => ['type' => 'textarea', 'label' => 'Features (one per line)'],
                    'highlighted' => ['type' => 'select', 'label' => 'Highlight this card', 'options' => ['no' => 'No', 'yes' => 'Yes'], 'default' => 'no'],
                ]],
            ],
        ],

        'gallery' => [
            'label' => 'Photo gallery',
            'description' => 'Grid of photos that open full-size when clicked.',
            'fields' => $intro + $background + [
                'items' => ['type' => 'repeater', 'label' => 'Photos', 'item_label' => 'Photo', 'fields' => [
                    'image'   => ['type' => 'image', 'label' => 'Photo'],
                    'caption' => ['type' => 'text', 'label' => 'Caption'],
                ]],
            ],
        ],

        'testimonials' => [
            'label' => 'Testimonials',
            'description' => 'Quotes from happy customers.',
            'fields' => $intro + $background + [
                'items' => ['type' => 'repeater', 'label' => 'Testimonials', 'item_label' => 'Testimonial', 'fields' => [
                    'quote' => ['type' => 'textarea', 'label' => 'Quote'],
                    'name'  => ['type' => 'text', 'label' => 'Name'],
                    'role'  => ['type' => 'text', 'label' => 'Role / location'],
                    'image' => ['type' => 'image', 'label' => 'Photo (optional)'],
                ]],
            ],
        ],

        'team' => [
            'label' => 'Team',
            'description' => 'Photos and roles of your people.',
            'fields' => $intro + $background + [
                'items' => ['type' => 'repeater', 'label' => 'People', 'item_label' => 'Person', 'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Photo'],
                    'name'  => ['type' => 'text', 'label' => 'Name'],
                    'role'  => ['type' => 'text', 'label' => 'Role'],
                ]],
            ],
        ],

        'logos' => [
            'label' => 'Partners / clients logos',
            'description' => 'A strip of partner or client logos.',
            'fields' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
            ] + $background + [
                'items' => ['type' => 'repeater', 'label' => 'Logos', 'item_label' => 'Logo', 'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Logo'],
                    'name'  => ['type' => 'text', 'label' => 'Name'],
                    'url'   => ['type' => 'url', 'label' => 'Link (optional)'],
                ]],
            ],
        ],

        'faq' => [
            'label' => 'FAQ',
            'description' => 'Questions and answers that expand when clicked.',
            'fields' => $intro + $background + [
                'items' => ['type' => 'repeater', 'label' => 'Questions', 'item_label' => 'Question', 'fields' => [
                    'question' => ['type' => 'text', 'label' => 'Question'],
                    'answer'   => ['type' => 'textarea', 'label' => 'Answer'],
                ]],
            ],
        ],

        'cta' => [
            'label' => 'Call to action',
            'description' => 'Bold banner asking visitors to act.',
            'fields' => [
                'heading'         => ['type' => 'text', 'label' => 'Heading'],
                'text'            => ['type' => 'textarea', 'label' => 'Text'],
                'image'           => ['type' => 'image', 'label' => 'Background photo (optional)'],
                'cta_label'       => ['type' => 'text', 'label' => 'Main button text'],
                'cta_url'         => ['type' => 'url', 'label' => 'Main button link'],
                'secondary_label' => ['type' => 'text', 'label' => 'Second button text'],
                'secondary_url'   => ['type' => 'url', 'label' => 'Second button link'],
            ],
        ],

        'rich_text' => [
            'label' => 'Text block',
            'description' => 'Long-form text — policies, articles, announcements.',
            'fields' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'body'    => ['type' => 'markdown', 'label' => 'Text'],
                'width'   => ['type' => 'select', 'label' => 'Width', 'options' => ['narrow' => 'Narrow (reading)', 'wide' => 'Wide'], 'default' => 'narrow'],
            ] + $background,
        ],

        'form' => [
            'label' => 'Contact / request form',
            'description' => 'A form that sends enquiries straight into Website → Leads.',
            'fields' => $intro + [
                'form_type'    => ['type' => 'select', 'label' => 'Form type', 'options' => [
                    'contact' => 'Contact message', 'quote' => 'Quote request', 'bin_request' => 'Bin request', 'pickup' => 'Special pickup',
                ], 'default' => 'contact'],
                'submit_label' => ['type' => 'text', 'label' => 'Submit button text', 'default' => 'Send message'],
                'show_contact' => ['type' => 'select', 'label' => 'Show contact details beside form', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
                'image'        => ['type' => 'image', 'label' => 'Side photo (when contact details are hidden)'],
                'map_embed'    => ['type' => 'url', 'label' => 'Google Maps embed link (optional)'],
            ] + $background,
        ],

        'posts' => [
            'label' => 'Latest news',
            'description' => 'Your most recent news posts.',
            'fields' => $intro + $background + [
                'count' => ['type' => 'number', 'label' => 'How many posts', 'default' => 3],
                'paginate' => ['type' => 'select', 'label' => 'Show page numbers', 'options' => ['no' => 'No', 'yes' => 'Yes'], 'default' => 'no'],
            ],
        ],

        'newsletter' => [
            'label' => 'Newsletter sign-up',
            'description' => 'Email sign-up box; subscribers appear under Website → Subscribers.',
            'fields' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'text'    => ['type' => 'textarea', 'label' => 'Text'],
                'button'  => ['type' => 'text', 'label' => 'Button text', 'default' => 'Subscribe'],
            ] + $background,
        ],

        'video' => [
            'label' => 'Video',
            'description' => 'Embed a YouTube video.',
            'fields' => $intro + $background + [
                'youtube_url' => ['type' => 'url', 'label' => 'YouTube link'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Site-wide settings (Website → Settings)
    |--------------------------------------------------------------------------
    */
    'settings' => [
        'brand' => [
            'label' => 'Brand',
            'fields' => [
                'site_name'     => ['type' => 'text', 'label' => 'Website name', 'default' => 'Elite Waste Management'],
                'tagline'       => ['type' => 'text', 'label' => 'Tagline', 'default' => 'Cleaner homes. Cleaner streets. Cleaner Ghana.'],
                'logo'          => ['type' => 'image', 'label' => 'Logo (leave empty to use the dashboard logo)'],
                'favicon'       => ['type' => 'image', 'label' => 'Browser tab icon (optional)'],
                'primary_color' => ['type' => 'color', 'label' => 'Main colour', 'default' => '#730D9B'],
                'accent_color'  => ['type' => 'color', 'label' => 'Accent colour', 'default' => '#F37A11'],
                'dark_color'    => ['type' => 'color', 'label' => 'Dark colour (footer, dark sections)', 'default' => '#1E0B2B'],
            ],
        ],
        'contact' => [
            'label' => 'Contact details',
            'fields' => [
                'phone'        => ['type' => 'text', 'label' => 'Phone', 'default' => '+233 30 000 0000'],
                'whatsapp'     => ['type' => 'text', 'label' => 'WhatsApp number (digits, with country code)', 'default' => ''],
                'email'        => ['type' => 'text', 'label' => 'Email', 'default' => 'info@elitewasteghana.com'],
                'address'      => ['type' => 'textarea', 'label' => 'Office address', 'default' => "Accra, Ghana"],
                'office_hours' => ['type' => 'text', 'label' => 'Office hours', 'default' => 'Mon – Sat, 7:00am – 6:00pm'],
                'map_embed'    => ['type' => 'url', 'label' => 'Google Maps embed link', 'default' => ''],
            ],
        ],
        'social' => [
            'label' => 'Social media',
            'fields' => [
                'facebook'  => ['type' => 'url', 'label' => 'Facebook', 'default' => ''],
                'instagram' => ['type' => 'url', 'label' => 'Instagram', 'default' => ''],
                'x'         => ['type' => 'url', 'label' => 'X (Twitter)', 'default' => ''],
                'linkedin'  => ['type' => 'url', 'label' => 'LinkedIn', 'default' => ''],
                'youtube'   => ['type' => 'url', 'label' => 'YouTube', 'default' => ''],
                'tiktok'    => ['type' => 'url', 'label' => 'TikTok', 'default' => ''],
            ],
        ],
        'header' => [
            'label' => 'Header & footer',
            'fields' => [
                'announcement'     => ['type' => 'text', 'label' => 'Announcement bar text (leave empty to hide)', 'default' => ''],
                'announcement_url' => ['type' => 'url', 'label' => 'Announcement bar link', 'default' => ''],
                'header_cta_label' => ['type' => 'text', 'label' => 'Header button text', 'default' => 'Request a Bin'],
                'header_cta_url'   => ['type' => 'url', 'label' => 'Header button link', 'default' => '/request-a-bin'],
                'login_label'      => ['type' => 'text', 'label' => 'Staff/client login link text (empty hides it)', 'default' => 'Login'],
                'footer_about'     => ['type' => 'textarea', 'label' => 'Footer description', 'default' => 'Reliable household and commercial waste collection, bin distribution and recycling across Ghana.'],
                'footer_col1_title' => ['type' => 'text', 'label' => 'Footer column 1 title', 'default' => 'Company'],
                'footer_col2_title' => ['type' => 'text', 'label' => 'Footer column 2 title', 'default' => 'Services'],
                'footer_col3_title' => ['type' => 'text', 'label' => 'Footer column 3 title', 'default' => 'Help'],
                'copyright'        => ['type' => 'text', 'label' => 'Copyright line', 'default' => 'Elite Waste Management. All rights reserved.'],
            ],
        ],
        'seo' => [
            'label' => 'SEO & tracking',
            'fields' => [
                'meta_title'       => ['type' => 'text', 'label' => 'Default page title suffix', 'default' => 'Elite Waste Management'],
                'meta_description' => ['type' => 'textarea', 'label' => 'Default description (search results)', 'default' => 'Reliable weekly and monthly waste collection, bin distribution and recycling for homes and businesses in Ghana.'],
                'og_image'         => ['type' => 'image', 'label' => 'Default share image', 'default' => 'images/site/hero-collection.jpg'],
                'ga_id'            => ['type' => 'text', 'label' => 'Google Analytics ID (G-XXXX)', 'default' => ''],
            ],
        ],
        'leads' => [
            'label' => 'Forms & leads',
            'fields' => [
                'form_success'      => ['type' => 'text', 'label' => 'Thank-you message after a form is sent', 'default' => 'Thank you! Our team will get back to you shortly.'],
                'lead_notify_phone' => ['type' => 'text', 'label' => 'Send an SMS alert for new leads to (phone, optional)', 'default' => ''],
            ],
        ],
    ],
];
