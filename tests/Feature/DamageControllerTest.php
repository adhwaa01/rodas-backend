<?php

namespace Tests\Feature;

use App\Models\DamageReport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DamageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_surveyor_can_create_a_pending_damage_report(): void
    {
        $surveyor = $this->createUser('petugas_surveyor');

        $response = $this->actingAs($surveyor, 'sanctum')
            ->postJson('/api/petugas/damage-reports', [
                'damage_type' => 'Lubang',
                'severity' => 'Sedang',
                'latitude' => -6.2,
                'longitude' => 106.8,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.user_id', $surveyor->id);

        $this->assertDatabaseHas('damage_reports', [
            'user_id' => $surveyor->id,
            'damage_type' => 'Lubang',
            'status' => 'pending',
        ]);
    }

    public function test_surveyor_cannot_access_another_users_report_or_change_status(): void
    {
        $surveyor = $this->createUser('petugas_surveyor');
        $anotherSurveyor = $this->createUser('petugas_surveyor');
        $report = $this->createReport($anotherSurveyor);

        $this->actingAs($surveyor, 'sanctum')
            ->getJson("/api/petugas/damage-reports/{$report->id}")
            ->assertForbidden();

        $this->actingAs($anotherSurveyor, 'sanctum')
            ->patchJson("/api/petugas/damage-reports/{$report->id}", [
                'status' => 'verified',
            ])
            ->assertOk();

        $this->assertDatabaseHas('damage_reports', [
            'id' => $report->id,
            'status' => 'pending',
            'verified_by' => null,
        ]);
    }

    public function test_admin_verification_records_the_verifier_and_note(): void
    {
        $admin = $this->createUser('admin');
        $surveyor = $this->createUser('petugas_surveyor');
        $report = $this->createReport($surveyor);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/damage-reports/{$report->id}/verify", [
                'status' => 'verified',
                'verification_note' => 'Sudah diperiksa.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'verified')
            ->assertJsonPath('data.verifier.id', $admin->id);

        $this->assertDatabaseHas('damage_reports', [
            'id' => $report->id,
            'status' => 'verified',
            'verified_by' => $admin->id,
            'verification_note' => 'Sudah diperiksa.',
        ]);

        $this->assertNotNull($report->fresh()->verified_at);
    }

    private function createUser(string $roleName): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => $roleName]
        );

        return User::create([
            'name' => 'Pengguna '.$roleName,
            'email' => uniqid($roleName.'-', true).'@example.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);
    }

    private function createReport(User $user): DamageReport
    {
        return DamageReport::create([
            'user_id' => $user->id,
            'damage_type' => 'Lubang',
            'status' => 'pending',
        ]);
    }
}
