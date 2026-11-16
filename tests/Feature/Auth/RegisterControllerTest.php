<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public static function currentRegistrationRolesAndDestinations(): array
    {
        return [
            'farmer' => ['farmer', 'subscription.plans'],
            'supplier' => ['supplier', 'subscription.plans'],
            'buyer' => ['buyer', 'dashboard'],
            'transporter' => ['transporter', 'dashboard'],
            'job seeker' => ['job_seeker', 'dashboard'],
        ];
    }

    #[DataProvider('currentRegistrationRolesAndDestinations')]
    public function test_registration_assigns_the_current_role_and_redirects_as_implemented(
        string $role,
        string $destination,
    ): void {
        Notification::fake();

        $email = "new-{$role}@example.test";

        $response = $this->post(route('register.store'), [
            'name' => 'Test Member',
            'email' => $email,
            'phone_number' => '+234800000001',
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
            'role' => $role,
            'terms' => 'on',
        ]);

        $response->assertRedirectToRoute($destination);

        $user = User::query()->where('email', $email)->firstOrFail();

        $this->assertModelExists($user);
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user->id,
            'role_name' => $role,
            'is_active' => true,
        ]);
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_rejects_an_empty_payload(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [])
            ->assertRedirectBackWithErrors([
                'name',
                'email',
                'phone_number',
                'password',
                'role',
                'terms',
            ]);

        $this->assertGuest();
    }

    public function test_public_registration_rejects_the_admin_role(): void
    {
        $email = 'admin-registration@example.test';

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Test Member',
                'email' => $email,
                'phone_number' => '+234800000002',
                'password' => 'TestPassword123!',
                'password_confirmation' => 'TestPassword123!',
                'role' => 'admin',
                'terms' => 'on',
            ])
            ->assertRedirectBackWithErrors(['role']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    public function test_registration_rejects_mismatched_password_confirmation(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Test Member',
                'email' => 'password-mismatch@example.test',
                'phone_number' => '+234800000003',
                'password' => 'TestPassword123!',
                'password_confirmation' => 'DifferentPassword123!',
                'role' => 'buyer',
                'terms' => 'on',
            ])
            ->assertRedirectBackWithErrors(['password']);

        $this->assertGuest();
    }
}
