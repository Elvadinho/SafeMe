<?php

namespace Modules\Children\Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Children\Models\MissingReport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** The full story: report → admin review → public alert → sighting → found. */
class MissingChildFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local'); // nothing is written to the real disk
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'child_name'      => 'Junior Mballa',
            'child_age'       => 7,
            'child_gender'    => 'male',
            'child_photo'     => UploadedFile::fake()->image('child.jpg', 400, 400),
            'description'     => 'Red T-shirt, blue shorts',
            'last_seen_at'    => now()->subHours(2)->toIso8601String(),
            'last_seen_lat'   => '3.8480000',
            'last_seen_lng'   => '11.5021000',
            'last_seen_place' => 'Marché Mokolo, Yaoundé',
            'reporter_name'   => 'Marie Mballa',
            'reporter_phone'  => '6 90 00 00 00',
            'relationship'    => 'parent',
            'declaration'     => '1',
            'documents'       => [
                ['type' => 'birth_certificate', 'file' => UploadedFile::fake()->create('acte.pdf', 300, 'application/pdf')],
            ],
        ], $overrides);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => UserRole::Admin])->save();

        return $admin;
    }

    private function submitReport(array $overrides = []): MissingReport
    {
        $publicId = $this->postJson('/api/v1/reports', $this->payload($overrides))->assertCreated()->json('public_id');

        return MissingReport::where('public_id', $publicId)->sole();
    }

    #[Test]
    public function a_guest_report_is_saved_as_pending_with_private_files(): void
    {
        $response = $this->postJson('/api/v1/reports', $this->payload())
            ->assertCreated()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('missing_documents', ['reporter_id']) // the CNI is still missing
            ->assertJsonMissingPath('reporter_phone');

        $report = MissingReport::where('public_id', $response->json('public_id'))->sole();

        $this->assertSame('+237690000000', $report->reporter_phone); // normalized
        Storage::disk('local')->assertExists($report->child_photo_path);
        Storage::disk('local')->assertExists($report->documents->first()->path);
    }

    #[Test]
    public function a_reporter_cannot_approve_their_own_report(): void
    {
        $report = $this->submitReport(['status' => 'approved']);

        $this->assertSame('pending', $report->status->value);
    }

    #[Test]
    public function invalid_data_is_refused(): void
    {
        $this->postJson('/api/v1/reports', $this->payload([
            'last_seen_lat'  => '48.85',   // Paris
            'reporter_phone' => '12345',
            'declaration'    => null,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['last_seen_lat', 'reporter_phone', 'declaration']);
    }

    #[Test]
    public function the_same_phone_cannot_flood_reports(): void
    {
        foreach (range(1, 3) as $i) {
            $this->postJson('/api/v1/reports', $this->payload())->assertCreated();
        }

        $this->postJson('/api/v1/reports', $this->payload())->assertTooManyRequests();
    }

    #[Test]
    public function a_pending_report_is_not_public(): void
    {
        $report = $this->submitReport();

        $this->getJson('/api/v1/alerts')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/v1/alerts/{$report->public_id}")->assertNotFound();
        $this->getJson("/api/v1/alerts/{$report->public_id}/photo")->assertNotFound();
    }

    #[Test]
    public function the_reporter_can_follow_the_case_and_add_documents_with_their_phone(): void
    {
        $report = $this->submitReport();

        // Wrong phone → 404 (we do not even confirm the report exists)
        $this->postJson("/api/v1/reports/{$report->public_id}/follow", ['reporter_phone' => '699999999'])
            ->assertNotFound();

        $this->postJson("/api/v1/reports/{$report->public_id}/documents", [
            'reporter_phone' => '+237 690 000 000',
            'documents'      => [['type' => 'reporter_id', 'file' => UploadedFile::fake()->image('cni.jpg')]],
        ])->assertCreated()->assertJsonPath('missing_documents', []);

        $this->postJson("/api/v1/reports/{$report->public_id}/follow", ['reporter_phone' => '690000000'])
            ->assertOk()
            ->assertJsonPath('report.status', 'pending')
            ->assertJsonCount(2, 'documents');
    }

    #[Test]
    public function citizens_cannot_moderate(): void
    {
        $report = $this->submitReport();
        $citizen = User::factory()->create();

        $this->postJson("/api/v1/admin/reports/{$report->public_id}/approve")->assertUnauthorized();
        $this->actingAs($citizen)->postJson("/api/v1/admin/reports/{$report->public_id}/approve")->assertForbidden();
    }

    #[Test]
    public function an_admin_sees_the_proof_and_the_reporter_phone(): void
    {
        $report = $this->submitReport();
        $admin = $this->admin();

        $this->actingAs($admin)->getJson('/api/v1/admin/reports')
            ->assertOk()
            ->assertJsonPath('data.0.reporter_phone', '+237690000000');

        $document = $report->documents->first();
        $this->actingAs($admin)
            ->get("/api/v1/admin/reports/{$report->public_id}/documents/{$document->id}")
            ->assertOk();

        // Pending photo: visible to the admin (to verify), not to the public
        $this->actingAs($admin, 'sanctum')->get("/api/v1/alerts/{$report->public_id}/photo")->assertOk();
    }

    #[Test]
    public function the_full_story_from_report_to_found(): void
    {
        $report = $this->submitReport();
        $admin = $this->admin();

        // 1. The admin approves → the alert is public, without private data
        $this->actingAs($admin)->postJson("/api/v1/admin/reports/{$report->public_id}/approve")
            ->assertOk()->assertJsonPath('report.status', 'approved');

        $this->app['auth']->forgetGuards(); // back to a guest visitor

        $this->getJson('/api/v1/alerts?lat=3.86&lng=11.52&radius_km=10')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.alert.child_name', 'Junior Mballa')
            ->assertJsonMissingPath('data.0.alert.reporter_phone');

        // Far away (Douala) → not in the "near me" list
        $this->getJson('/api/v1/alerts?lat=4.05&lng=9.76&radius_km=20')->assertJsonCount(0, 'data');

        $this->get("/api/v1/alerts/{$report->public_id}/photo")->assertOk();

        // 2. A citizen saw the child
        $sightingId = $this->postJson("/api/v1/alerts/{$report->public_id}/sightings", [
            'lat'           => '3.8700000',
            'lng'           => '11.5200000',
            'seen_at'       => now()->subMinutes(10)->toIso8601String(),
            'note'          => 'Near the bus station, with an adult',
            'witness_phone' => '677 11 22 33',
        ])->assertCreated()->assertJsonMissingPath('sighting.witness_phone')->json('sighting.id');

        // Not confirmed yet → the public search zone has not moved
        $this->getJson("/api/v1/alerts/{$report->public_id}")
            ->assertJsonCount(0, 'sightings')
            ->assertJsonPath('search_zone.lat', 3.848);

        // 3. The admin confirms → the search zone moves to the sighting
        $this->actingAs($admin)->postJson("/api/v1/admin/sightings/{$sightingId}/confirm")->assertOk();

        $this->getJson("/api/v1/alerts/{$report->public_id}")
            ->assertJsonCount(1, 'sightings')
            ->assertJsonPath('search_zone.lat', 3.87);

        // 4. Found! It disappears from the public list, and cannot go back
        $this->actingAs($admin)->postJson("/api/v1/admin/reports/{$report->public_id}/found")
            ->assertOk()->assertJsonPath('report.status', 'found');

        $this->getJson('/api/v1/alerts')->assertJsonCount(0, 'data');

        $this->actingAs($admin)->postJson("/api/v1/admin/reports/{$report->public_id}/approve")->assertStatus(409);
    }

    #[Test]
    public function a_rejected_report_shows_the_reason_to_the_reporter(): void
    {
        $report = $this->submitReport();

        $this->actingAs($this->admin())->postJson("/api/v1/admin/reports/{$report->public_id}/reject", [
            'reason' => 'The birth certificate does not match the reporter ID.',
        ])->assertOk();

        $this->app['auth']->forgetGuards();

        $this->postJson("/api/v1/reports/{$report->public_id}/follow", ['reporter_phone' => '690000000'])
            ->assertOk()
            ->assertJsonPath('report.status', 'rejected')
            ->assertJsonPath('rejection_reason', 'The birth certificate does not match the reporter ID.');
    }
}
