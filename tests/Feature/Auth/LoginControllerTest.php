<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public static function loginIdentifiers(): array
    {
        return [
            'email' => ['email'],
            'phone number' => ['phone_number'],
        ];
    }

    #[DataProvider('loginIdentifiers')]
    public function test_valid_credentials_authenticate_user_using_supported_identifier(string $identifier): void
    {
        $user = User::factory()->create([
            'phone_number' => '+234800000004',
        ]);
        $login = $identifier === 'email' ? $user->email : $user->phone_number;

        $this->post(route('login.store'), [
            'login' => $login,
            'password' => 'password',
        ])
            ->assertRedirectToRoute('dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_return_a_login_error_and_leave_guest_unauthenticated(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'login' => 'missing@example.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirectToRoute('login')
            ->assertSessionHasErrors([
                'login' => 'These credentials do not match our records.',
            ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirectToRoute('landing');

        $this->assertGuest();
    }
}
