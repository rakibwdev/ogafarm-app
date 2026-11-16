<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SubscriptionPlanAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login_to_view_subscription_plans(): void
    {
        $this->get(route('subscription.plans'))
            ->assertRedirectToRoute('login');
    }

    public function test_authenticated_user_can_view_the_current_subscription_plans(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('subscription.plans'))
            ->assertOk()
            ->assertViewIs('subscriptions.plans')
            ->assertSeeText('3-Month')
            ->assertSeeText('₦30,000')
            ->assertSeeText('6-Month')
            ->assertSeeText('₦54,000')
            ->assertSeeText('12-Month')
            ->assertSeeText('₦95,000');
    }
}
