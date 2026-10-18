<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\Website\WebsiteLead;
use App\Models\Website\WebsiteMedia;
use App\Models\Website\WebsitePage;
use App\Models\Website\WebsitePost;
use App\Models\Website\WebsiteSection;
use App\Models\Website\WebsiteSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function editor(string $role = 'Owner'): User
    {
        Role::findOrCreate($role);
        return tap(User::factory()->create())->assignRole($role);
    }

    public function test_seeded_homepage_and_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Cleaner homes, cleaner streets, cleaner Ghana.');

        foreach (WebsitePage::where('is_home', false)->pluck('slug') as $slug) {
            $this->get("/{$slug}")->assertOk();
        }

        $post = WebsitePost::first();
        $this->get("/news/{$post->slug}")->assertOk()->assertSee($post->title);
    }

    public function test_homepage_url_redirects_and_unknown_page_404s(): void
    {
        $this->get('/home')->assertRedirect(route('site.home'));
        $this->get('/no-such-page')->assertNotFound();
    }

    public function test_draft_pages_are_hidden_from_public_but_previewable_by_editors(): void
    {
        WebsitePage::where('slug', 'about')->update(['status' => 'Draft']);

        $this->get('/about')->assertNotFound();
        $this->actingAs($this->editor())->get('/about')->assertOk()->assertSee('Draft preview');
    }

    public function test_hidden_sections_are_not_rendered(): void
    {
        // The seeded testimonials are placeholders and start hidden
        $this->get('/')->assertDontSee('Replace this with a real quote');
    }

    public function test_live_tokens_show_real_counts(): void
    {
        Customer::create(['name' => 'A', 'phone' => '0240000001', 'type' => 'Residential']);
        Customer::create(['name' => 'B', 'phone' => '0240000002', 'type' => 'Residential']);
        cache()->forget('website.tokens');

        $this->get('/')->assertSee('2+')->assertDontSee('{customers}');
    }

    public function test_bin_request_form_creates_a_lead(): void
    {
        $this->from('/request-a-bin')->post(route('site.forms.submit'), [
            'form_type' => 'bin_request',
            'name'      => 'Ama Mensah',
            'phone'     => '0241234567',
            'location'  => 'East Legon',
            'frequency' => 'Weekly',
        ])->assertRedirect('/request-a-bin')->assertSessionHas('form_success');

        $lead = WebsiteLead::sole();
        $this->assertSame('bin_request', $lead->type);
        $this->assertSame('New', $lead->status);
        $this->assertSame('East Legon', $lead->location);
    }

    public function test_form_requires_a_way_to_contact(): void
    {
        $this->post(route('site.forms.submit'), ['form_type' => 'contact', 'name' => 'No Contact'])
            ->assertSessionHasErrors(['phone', 'email']);
        $this->assertSame(0, WebsiteLead::count());
    }

    public function test_honeypot_submissions_are_dropped(): void
    {
        $this->post(route('site.forms.submit'), [
            'form_type' => 'contact', 'name' => 'Bot', 'email' => 'bot@example.com', 'website' => 'http://spam',
        ])->assertSessionHas('form_success');

        $this->assertSame(0, WebsiteLead::count());
    }

    public function test_newsletter_subscribe_is_idempotent(): void
    {
        $this->post(route('site.forms.subscribe'), ['email' => 'Kofi@Example.com'])->assertSessionHas('subscribe_success');
        $this->post(route('site.forms.subscribe'), ['email' => 'kofi@example.com']);

        $this->assertSame(1, WebsiteSubscriber::count());
    }

    public function test_cms_is_restricted_to_owners_admins_and_superadmins(): void
    {
        $this->get(route('website.pages.index'))->assertRedirect(route('login'));
        $this->actingAs($this->editor('Collector'))->get(route('website.pages.index'))->assertForbidden();
    }

    public function test_editor_can_open_every_cms_screen(): void
    {
        $this->actingAs($this->editor('Admin'));
        $page = WebsitePage::where('slug', 'home')->first();

        foreach ([
            route('website.pages.index'), route('website.pages.create'), route('website.pages.edit', $page),
            route('website.sections.edit', $page->sections->first()), route('website.posts.index'),
            route('website.posts.create'), route('website.posts.edit', WebsitePost::first()), route('website.media.index'),
            route('website.menu.index'), route('website.settings.show'), route('website.leads.index'),
            route('website.subscribers.index'), route('website.media.picker'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_section_update_keeps_only_known_fields_and_safe_links(): void
    {
        $this->actingAs($this->editor());
        $section = WebsiteSection::where('type', 'hero')->first();

        $this->put(route('website.sections.update', $section), [
            'is_visible' => '1',
            'data' => json_encode([
                'heading'   => 'New headline',
                'cta_url'   => 'javascript:alert(1)',
                'evil'      => 'dropped',
                'badges'    => [['text' => 'One', '_open' => true], 'not-a-row'],
            ]),
        ])->assertSessionHas('success');

        $data = $section->fresh()->data;
        $this->assertSame('New headline', $data['heading']);
        $this->assertNull($data['cta_url']);
        $this->assertArrayNotHasKey('evil', $data);
        $this->assertSame([['text' => 'One']], $data['badges']);

        $this->get('/')->assertSee('New headline');
    }

    public function test_pages_cannot_take_dashboard_addresses(): void
    {
        $this->actingAs($this->editor())
            ->post(route('website.pages.store'), ['title' => 'Customers', 'slug' => 'customers', 'status' => 'Published'])
            ->assertSessionHasErrors('slug');
    }

    public function test_new_page_gets_a_header_and_can_be_built(): void
    {
        $this->actingAs($this->editor());

        $this->post(route('website.pages.store'), ['title' => 'Careers', 'status' => 'Published'])->assertRedirect();
        $page = WebsitePage::where('slug', 'careers')->firstOrFail();
        $this->assertSame(['page_header'], $page->sections->pluck('type')->all());

        $this->post(route('website.sections.store', $page), ['type' => 'faq'])->assertRedirect();
        $this->assertSame(['page_header', 'faq'], $page->fresh()->sections->pluck('type')->all());

        $faq = $page->fresh()->sections->last();
        $this->post(route('website.sections.move', $faq), ['direction' => 'up']);
        $this->assertSame(['faq', 'page_header'], $page->fresh()->sections->pluck('type')->all());

        $this->get('/careers')->assertOk();
    }

    public function test_media_upload_lands_in_library(): void
    {
        Storage::fake('public');
        $this->actingAs($this->editor());

        $this->postJson(route('website.media.store'), ['files' => [UploadedFile::fake()->image('truck.jpg', 800, 600)]])
            ->assertOk()->assertJsonPath('items.0.name', 'truck.jpg');

        $media = WebsiteMedia::where('original_name', 'truck.jpg')->sole();
        Storage::disk('public')->assertExists(substr($media->path, strlen('storage/')));
    }

    public function test_media_upload_rejects_non_images(): void
    {
        Storage::fake('public');
        $this->actingAs($this->editor());

        $this->postJson(route('website.media.store'), ['files' => [UploadedFile::fake()->create('evil.svg', 1, 'image/svg+xml')]])
            ->assertStatus(422);
    }

    public function test_settings_save_and_apply_to_the_site(): void
    {
        $this->actingAs($this->editor());

        $this->put(route('website.settings.update'), [
            'data' => json_encode(['site_name' => 'Elite Waste GH', 'primary_color' => '#0f766e', 'phone' => '+233 24 111 2222']),
        ])->assertSessionHas('success');

        $this->get('/')->assertSee('Elite Waste GH')->assertSee('15 118 110')->assertSee('+233 24 111 2222');
    }

    public function test_lead_pipeline_and_conversion_to_customer(): void
    {
        $user = $this->editor();
        $this->actingAs($user);
        $lead = WebsiteLead::create(['type' => 'bin_request', 'name' => 'Yaw Boateng', 'phone' => '0209998888', 'location' => 'Tema']);

        $this->post(route('website.leads.activities.store', $lead), ['type' => 'call', 'body' => 'Called, wants weekly.']);
        $this->assertSame('Contacted', $lead->fresh()->status);

        $this->patch(route('website.leads.update', $lead), ['status' => 'Qualified', 'assigned_to' => $user->id]);
        $this->assertSame('Qualified', $lead->fresh()->status);
        $this->assertSame(2, $lead->activities()->count());

        $this->post(route('website.leads.convert', $lead))
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasInput('name', 'Yaw Boateng');

        $this->post(route('customers.store'), ['name' => 'Yaw Boateng', 'phone' => '0209998888', 'type' => 'Residential']);

        $lead->refresh();
        $this->assertSame('Won', $lead->status);
        $this->assertSame(Customer::where('phone', '0209998888')->value('id'), $lead->customer_id);
    }

    public function test_menu_links_can_point_to_pages_or_urls(): void
    {
        $this->actingAs($this->editor());
        $page = WebsitePage::where('slug', 'terms')->first();

        $this->post(route('website.menu.store'), ['location' => 'header', 'label' => 'Legal', 'website_page_id' => $page->id])->assertSessionHas('success');
        $this->post(route('website.menu.store'), ['location' => 'header', 'label' => 'Bad', 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');

        $this->get('/')->assertSee('Legal');
    }
}
