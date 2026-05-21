<?php

namespace Database\Seeders;

use App\Models\Platform\PlatformService;
use Illuminate\Database\Seeder;

class PlatformServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'slug'              => 'email',
                'name'              => 'Email Hosting',
                'type'              => 'Email',
                'unit_price'        => 6.00,
                'currency'          => 'USD',
                'billing_cycle'     => 'Monthly',
                'is_quantity_based' => true,
                'unit_label'        => 'email account',
                'default_quantity'  => 1,
                'min_quantity'      => 1,
                'grace_days'        => 7,
                'description'       => 'Professional email accounts at your domain.',
                'features'          => ['Custom domain', '30GB storage', 'Webmail + IMAP', 'Spam protection'],
                'customer_addable'  => true,
                'is_active'         => true,
                'sort_order'        => 10,
            ],
            [
                'slug'              => 'hosting',
                'name'              => 'Web Hosting',
                'type'              => 'Hosting',
                'unit_price'        => 10.00,
                'currency'          => 'USD',
                'billing_cycle'     => 'Monthly',
                'is_quantity_based' => false,
                'default_quantity'  => 1,
                'min_quantity'      => 1,
                'grace_days'        => 7,
                'description'       => 'Managed hosting for the application.',
                'features'          => ['SSL included', 'Daily backups', '99.9% uptime', '24/7 monitoring'],
                'customer_addable'  => false,
                'is_active'         => true,
                'sort_order'        => 20,
            ],
            [
                'slug'              => 'domain',
                'name'              => 'Domain Name',
                'type'              => 'Domain',
                'unit_price'        => 50.00,
                'currency'          => 'USD',
                'billing_cycle'     => 'Yearly',
                'is_quantity_based' => true,
                'unit_label'        => 'domain',
                'default_quantity'  => 1,
                'min_quantity'      => 1,
                'grace_days'        => 14,
                'description'       => 'Domain registration & renewal.',
                'features'          => ['Privacy protection', 'DNS management', 'Email forwarding'],
                'customer_addable'  => false,
                'is_active'         => true,
                'sort_order'        => 30,
            ],
            [
                'slug'                  => 'sms',
                'name'                  => 'SMS Bundle (Ghana)',
                'type'                  => 'SMS',
                'unit_price'            => 10.00,
                'currency'              => 'USD',
                'billing_cycle'         => 'Monthly',
                'is_quantity_based'     => true,
                'unit_label'            => '1000 SMS bundle',
                'default_quantity'      => 1,
                'min_quantity'          => 1,
                'sms_messages_per_unit' => 1000,
                'grace_days'            => 3,
                'description'           => '1,000 SMS to Ghana numbers per bundle.',
                'features'              => ['Branded sender ID', 'Delivery reports', 'No expiry within month'],
                'customer_addable'      => true,
                'is_active'             => true,
                'sort_order'            => 40,
            ],
        ];

        foreach ($services as $svc) {
            PlatformService::firstOrCreate(['slug' => $svc['slug']], $svc);
        }
    }
}
