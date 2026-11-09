<?php

namespace Modules\Documents\Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Documents\Models\DocumentMatch;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LostAndFoundTest extends TestCase
{
    use RefreshDatabase;

    private function found(array $overrides = []): array
    {
        return $this->postJson('/api/v1/found-documents', array_merge([
            'doc_type'           => 'national_id',
            'owner_name'         => 'TEMGOUA Anantia',
            'number_last_digits' => '458',
            'found_city'         => 'Yaoundé',
            'found_place'        => 'Taxi, Poste centrale',
            'found_at'           => now()->toDateString(),
            'finder_name'        => 'Paul Finder',
            'finder_phone'       => '677 00 00 01',
        ], $overrides))->assertCreated()->json();
    }

    private function lost(array $overrides = []): array
    {
        return $this->postJson('/api/v1/lost-documents', array_merge([
            'doc_type'    => 'national_id',
            'owner_name'  => 'Anantia Témgoua', // same person, written differently
            'owner_phone' => '690 00 00 02',
        ], $overrides))->assertCreated()->json();
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => UserRole::Admin])->save();

        return $admin;
    }

    #[Test]
    public function the_public_search_needs_a_name_and_hides_private_data(): void
    {
        $this->found();

        $this->getJson('/api/v1/found-documents')->assertUnprocessable();

        $this->getJson('/api/v1/found-documents?name=anantia temgoua')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.owner_name', 'T****** A******')
            ->assertJsonMissingPath('data.0.finder_phone')
            ->assertDontSee('677000001');
    }

    #[Test]
    public function a_lost_declaration_is_matched_with_an_existing_found_document(): void
    {
        $this->found();

        $lost = $this->lost();

        $this->assertSame(1, $lost['matches_found']);
    }

    #[Test]
    public function a_found_document_is_matched_with_an_existing_lost_declaration(): void
    {
        $this->lost();
        $this->found();

        $this->assertSame(1, DocumentMatch::count());
    }

    #[Test]
    public function different_last_digits_do_not_match(): void
    {
        $this->found(['number_last_digits' => '458']);
        $lost = $this->lost(['number_last_digits' => '999']);

        $this->assertSame(0, $lost['matches_found']);
    }

    #[Test]
    public function contacts_are_shared_only_after_an_admin_confirms(): void
    {
        $found = $this->found();
        $lost = $this->lost();

        // Before confirmation: the owner sees the match but not the finder's phone
        $this->postJson("/api/v1/lost-documents/{$lost['public_id']}/follow", ['phone' => '690000002'])
            ->assertOk()
            ->assertJsonPath('matches.0.status', 'pending')
            ->assertJsonPath('matches.0.finder', null);

        // Wrong phone → 404
        $this->postJson("/api/v1/lost-documents/{$lost['public_id']}/follow", ['phone' => '699999999'])
            ->assertNotFound();

        // The admin confirms
        $match = DocumentMatch::sole();
        $this->actingAs($this->admin())->postJson("/api/v1/admin/document-matches/{$match->id}/confirm")
            ->assertOk()->assertJsonPath('match.status', 'confirmed');

        $this->app['auth']->forgetGuards();

        // Now both sides see each other's contact
        $this->postJson("/api/v1/lost-documents/{$lost['public_id']}/follow", ['phone' => '690000002'])
            ->assertJsonPath('matches.0.finder.phone', '+237677000001');

        $this->postJson("/api/v1/found-documents/{$found['public_id']}/follow", ['phone' => '677000001'])
            ->assertJsonPath('matches.0.owner.phone', '+237690000002');

        // A matched document is no longer in the public search
        $this->getJson('/api/v1/found-documents?name=anantia temgoua')->assertJsonCount(0, 'data');
    }

    #[Test]
    public function citizens_cannot_confirm_matches(): void
    {
        $this->found();
        $this->lost();

        $this->actingAs(User::factory()->create())
            ->postJson('/api/v1/admin/document-matches/' . DocumentMatch::sole()->id . '/confirm')
            ->assertForbidden();
    }
}
