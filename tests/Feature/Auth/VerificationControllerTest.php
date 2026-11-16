<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VerificationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_from_the_verification_notice(): void
    {
        $this->get(route('verification.notice'))
            ->assertRedirectToRoute('login');
    }

    public function test_authenticated_user_can_view_their_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertViewIs('auth.verify-email')
            ->assertSeeText($user->email);
    }

    public function test_unverified_user_can_request_a_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.send'))
            ->assertRedirectToRoute('verification.notice')
            ->assertSessionHas('status', 'Verification link sent — check your inbox.');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verified_user_is_not_sent_another_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirectToRoute('dashboard');

        Notification::assertNothingSent();
    }

    public function test_valid_signed_link_marks_the_authenticated_user_as_verified(): void
    {
        $user = User::factory()->unverified()->create();
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ],
        );

        $this->actingAs($user)
            ->get($signedUrl)
            ->assertRedirectToRoute('dashboard')
            ->assertSessionHas('status', 'Your account is verified. Welcome to OgaFarm!');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_signed_link_with_a_mismatched_email_hash_is_forbidden(): void
    {
        $user = User::factory()->unverified()->create();
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            [
                'id' => $user->id,
                'hash' => sha1('different@example.test'),
            ],
        );

        $this->actingAs($user)
            ->get($signedUrl)
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }
}
