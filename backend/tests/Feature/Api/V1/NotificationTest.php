<?php

namespace Tests\Feature\Api\V1;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

it('supports unread_only filter and only returns unread notifications', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $unread = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => true,
    ]);

    Notification::factory()->create([
        'user_id' => $otherUser->id,
        'is_read' => false,
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/notifications?unread_only=true');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $unread->id)
        ->assertJsonPath('data.0.is_read', false);
});

it('supports is_read filter for read and unread notifications', function () {
    $user = User::factory()->create();

    $read = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => true,
    ]);

    $unread = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $readResponse = $this->actingAs($user)->getJson('/api/v1/notifications?is_read=true');
    $readResponse->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $read->id)
        ->assertJsonPath('data.0.is_read', true);

    $unreadResponse = $this->actingAs($user)->getJson('/api/v1/notifications?is_read=false');
    $unreadResponse->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $unread->id)
        ->assertJsonPath('data.0.is_read', false);
});

it('includes preview_message target_path and target_params in notification responses', function () {
    $user = User::factory()->create();
    $referenceId = (string) Str::uuid();
    $longMessage = str_repeat('a', 200);

    Notification::factory()->create([
        'user_id' => $user->id,
        'type' => 'LOGBOOK_ACCEPTED',
        'reference_id' => $referenceId,
        'message' => $longMessage,
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/notifications');

    $response->assertOk()
        ->assertJsonPath('data.0.preview_message', Str::limit($longMessage, 120))
        ->assertJsonPath('data.0.target_path', '/staff/history')
        ->assertJsonPath('data.0.target_params.logbook_id', $referenceId);
});

it('marks notification as read idempotently', function () {
    $user = User::factory()->create();

    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $firstResponse = $this->actingAs($user)->putJson("/api/v1/notifications/{$notification->id}/read");
    $firstResponse->assertOk()
        ->assertJsonPath('is_read', true);

    $firstUpdatedAt = $notification->fresh()->updated_at;
    expect($firstUpdatedAt)->not->toBeNull();

    $secondResponse = $this->actingAs($user)->putJson("/api/v1/notifications/{$notification->id}/read");
    $secondResponse->assertOk()
        ->assertJsonPath('is_read', true);

    $secondUpdatedAt = $notification->fresh()->updated_at;
    expect($secondUpdatedAt)->not->toBeNull();
    expect($secondUpdatedAt?->toISOString())->toBe($firstUpdatedAt?->toISOString());
});

it('prevents marking other users notification as read', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $notification = Notification::factory()->create([
        'user_id' => $owner->id,
        'is_read' => false,
    ]);

    $this->actingAs($otherUser)
        ->putJson("/api/v1/notifications/{$notification->id}/read")
        ->assertForbidden();

    $notification->refresh();
    expect($notification->is_read)->toBeFalse();
});

it('marks all notifications as read via read-all endpoint', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $unread1 = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $unread2 = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $alreadyRead = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => true,
    ]);

    $otherUserNotification = Notification::factory()->create([
        'user_id' => $otherUser->id,
        'is_read' => false,
    ]);

    $response = $this->actingAs($user)->putJson('/api/v1/notifications/read-all');

    $response->assertOk()
        ->assertJsonPath('message', 'All notifications marked as read');

    expect($unread1->fresh()->is_read)->toBeTrue();
    expect($unread2->fresh()->is_read)->toBeTrue();
    expect($alreadyRead->fresh()->is_read)->toBeTrue();

    expect($otherUserNotification->fresh()->is_read)->toBeFalse();
});
