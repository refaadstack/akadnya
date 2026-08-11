<?php

use App\Models\Invitation;
use App\Models\InvitationGallery;
use App\Models\MediaUpload;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Template;
use App\Models\User;
use App\Models\UserFeature;
use App\Services\MediaService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function quotaJpegUpload(string $name): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'quota_image');
    file_put_contents($path, "\xFF\xD8\xFF\xE0".str_repeat("\x00", 200));

    return new UploadedFile($path, $name, 'image/jpeg', null, true);
}

function quotaFeatureForUser(User $user, string $feature, array $metadata = []): UserFeature
{
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-'.uniqid(),
        'status' => 'paid',
        'total_amount' => 1000,
    ]);
    $item = OrderItem::create([
        'order_id' => $order->id,
        'item_type' => 'product',
        'item_id' => 1,
        'name' => $feature,
        'price' => 1000,
    ]);

    return UserFeature::create([
        'user_id' => $user->id,
        'feature' => $feature,
        'order_item_id' => $item->id,
        'metadata' => $metadata,
        'activated_at' => now(),
    ]);
}

beforeEach(function () {
    Storage::fake('public');

    $this->withoutMiddleware(PreventRequestForgery::class);

    $this->user = User::factory()->create();
    $this->template = Template::factory()->create();
    $this->invitation = Invitation::factory()->for($this->user)->for($this->template)->create();
    $this->user->forceFill(['active_invitation_id' => $this->invitation->id])->save();
});

test('upload records the file against the user quota', function () {
    $response = $this->actingAs($this->user)
        ->post(route('media.upload.cover'), ['file' => quotaJpegUpload('cover.jpg')]);

    $response->assertOk()->assertJson(['success' => true]);

    expect(MediaUpload::where('user_id', $this->user->id)->count())->toBe(1);
    expect(MediaUpload::first()->type)->toBe('cover');
    expect(MediaUpload::first()->invitation_id)->toBe($this->invitation->id);

    $service = app(MediaService::class);
    expect($service->usedByUser($this->user))->toBeGreaterThan(0);
});

test('upload is rejected when it would exceed the storage quota', function () {
    MediaUpload::create([
        'user_id' => $this->user->id,
        'invitation_id' => $this->invitation->id,
        'url' => 'https://example.com/storage/invitations/media/full.jpg',
        'size' => MediaService::BASE_QUOTA_BYTES - 50,
        'type' => 'gallery',
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('media.upload.cover'), ['file' => quotaJpegUpload('lagi.jpg')]);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($response->json('message'))->toContain('Kuota penyimpanan');
});

test('extra_storage add-on extends the storage quota', function () {
    MediaUpload::create([
        'user_id' => $this->user->id,
        'invitation_id' => $this->invitation->id,
        'url' => 'https://example.com/storage/invitations/media/full.jpg',
        'size' => MediaService::BASE_QUOTA_BYTES - 1024,
        'type' => 'gallery',
    ]);

    quotaFeatureForUser($this->user, 'extra_storage', ['storage_gb' => 1]);

    $response = $this->actingAs($this->user)
        ->post(route('media.upload.cover'), ['file' => quotaJpegUpload('lagi.jpg')]);

    $response->assertOk()->assertJson(['success' => true]);
});

test('deleting a gallery photo removes its quota record', function () {
    $service = app(MediaService::class);
    $url = $service->uploadFor($this->user, $this->invitation, quotaJpegUpload('foto.jpg'), 'gallery');

    $photo = InvitationGallery::create([
        'invitation_id' => $this->invitation->id,
        'image_url' => $url,
        'sort_order' => 1,
    ]);

    $this->actingAs($this->user)
        ->delete(route('dashboard.gallery.destroy', $photo))
        ->assertRedirect();

    expect(MediaUpload::where('url', $url)->count())->toBe(0);
});