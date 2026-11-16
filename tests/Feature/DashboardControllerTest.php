<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public static function currentRoleDashboardViews(): array
    {
        return [
            'farmer' => ['farmer', 'dashboards.farmer'],
            'supplier' => ['supplier', 'dashboards.supplier'],
            'buyer' => ['buyer', 'dashboards.buyer'],
            'transporter' => ['transporter', 'dashboards.transporter'],
            'job seeker' => ['job_seeker', 'dashboards.job-seeker'],
        ];
    }

    #[DataProvider('currentRoleDashboardViews')]
    public function test_currently_supported_role_sees_its_dashboard(string $role, string $view): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::from($role));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs($view);
    }
}
