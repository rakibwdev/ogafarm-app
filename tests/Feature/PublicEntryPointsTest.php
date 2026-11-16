<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PublicEntryPointsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_view_the_landing_page(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertViewIs('landing')
            ->assertSeeText("Nigeria's agricultural marketplace");
    }

    public function test_valid_invite_code_redirects_to_registration_and_is_stored_in_session(): void
    {
        $this->get(route('invite.resolve', ['code' => 'oga-ab12cd']))
            ->assertRedirectToRoute('register')
            ->assertSessionHas('referral_code', 'OGA-AB12CD');
    }

    public function test_malformed_invite_code_redirects_to_registration_without_attribution(): void
    {
        $this->get(route('invite.resolve', ['code' => 'not-a-code']))
            ->assertRedirectToRoute('register')
            ->assertSessionMissing('referral_code');
    }
}
