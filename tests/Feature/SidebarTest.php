<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_owner_sees_grouped_sections_with_messaging_and_settings(): void
    {
        $owner = tap(User::factory()->create())->assignRole('Owner');

        $this->actingAs($owner)->get(route('sms_logs.index'))
            ->assertOk()
            ->assertSeeInOrder(['Customers &amp; Collection', 'Billing', 'Messaging', 'Finance', 'Administration', 'My Billing'], false)
            ->assertSee(route('settings.index'))
            ->assertSee('aria-current="page"', false)
            ->assertDontSee('Platform (Provider)');
    }

    public function test_collector_does_not_see_admin_only_sections(): void
    {
        $collector = tap(User::factory()->create())->assignRole('Collector');

        $this->actingAs($collector)->get(route('my.services.index'))
            ->assertOk()
            ->assertSee('My Billing')
            ->assertDontSee('Messaging')
            ->assertDontSee(route('sms_logs.index'))
            ->assertDontSee(route('settings.index'));
    }
}
