<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\UserInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class UserInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_send_an_invitation_by_email(): void
    {
        Notification::fake();

        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'email' => 'invitee@example.com',
        ]);

        $response
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('status', 'Invitation sent.');

        $invitation = UserInvitation::query()->where('email', 'invitee@example.com')->first();

        $this->assertNotNull($invitation);
        $this->assertSame($admin->id, $invitation->invited_by);
        $this->assertTrue($invitation->isPending());
        $this->assertTrue($invitation->expires_at->isFuture());

        Notification::assertSentOnDemand(UserInvitationNotification::class, function (UserInvitationNotification $notification, array $channels, object $notifiable) use ($invitation): bool {
            $mail = $notification->toMail($notifiable);

            return in_array('mail', $channels, true)
                && $notifiable->routes['mail'] === 'invitee@example.com'
                && str_contains($mail->actionUrl, (string) $invitation->id);
        });
    }

    public function test_invited_user_can_view_the_acceptance_screen(): void
    {
        $this->withoutVite();

        $token = Str::random(64);
        $invitation = UserInvitation::query()->create([
            'email' => 'invitee@example.com',
            'token_hash' => hash('sha256', $token),
            'invited_by' => User::factory()->superAdmin()->create()->id,
            'expires_at' => Carbon::now()->addDays(3),
        ]);

        $response = $this->get(route('invitations.accept', [
            'invitation' => $invitation,
            'token' => $token,
        ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Auth/AcceptInvitation')
                ->where('invitation.email', 'invitee@example.com')
                ->where('invitation.token', $token)
            );
    }

    public function test_invited_user_can_activate_their_account_with_password_setup(): void
    {
        $token = Str::random(64);
        $invitation = UserInvitation::query()->create([
            'email' => 'invitee@example.com',
            'token_hash' => hash('sha256', $token),
            'invited_by' => User::factory()->superAdmin()->create()->id,
            'expires_at' => Carbon::now()->addDays(3),
        ]);

        $response = $this->post(route('invitations.activate', $invitation), [
            'token' => $token,
            'name' => 'Invited User',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $user = User::query()->where('email', 'invitee@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Invited User', $user->name);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('read_only', $user->role->value);
        $this->assertAuthenticatedAs($user);

        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertSame($user->id, $invitation->fresh()->accepted_user_id);
    }

    public function test_expired_invitations_cannot_be_accepted(): void
    {
        $token = Str::random(64);
        $invitation = UserInvitation::query()->create([
            'email' => 'invitee@example.com',
            'token_hash' => hash('sha256', $token),
            'invited_by' => User::factory()->superAdmin()->create()->id,
            'expires_at' => Carbon::now()->subMinute(),
        ]);

        $this->get(route('invitations.accept', [
            'invitation' => $invitation,
            'token' => $token,
        ]))->assertGone();

        $this->post(route('invitations.activate', $invitation), [
            'token' => $token,
            'name' => 'Expired User',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertGone();

        $this->assertDatabaseMissing('users', [
            'email' => 'invitee@example.com',
        ]);
    }

    public function test_invitation_cannot_be_accepted_with_an_invalid_token(): void
    {
        $token = Str::random(64);
        $invitation = UserInvitation::query()->create([
            'email' => 'invitee@example.com',
            'token_hash' => hash('sha256', $token),
            'invited_by' => User::factory()->superAdmin()->create()->id,
            'expires_at' => Carbon::now()->addDays(3),
        ]);

        $this->get(route('invitations.accept', [
            'invitation' => $invitation,
            'token' => 'invalid-token',
        ]))->assertForbidden();

        $this->post(route('invitations.activate', $invitation), [
            'token' => 'invalid-token',
            'name' => 'Invalid User',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'invitee@example.com',
        ]);
    }
}
