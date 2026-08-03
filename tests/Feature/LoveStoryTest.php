<?php

use App\Models\Invitation;
use App\Models\LoveStory;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $this->user = User::factory()->create();
    $template = Template::factory()->create();
    $basePackage = Product::factory()->create([
        'type' => 'base_package',
        'slug' => 'base',
        'price' => 50000,
    ]);

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder($this->user, $template, $basePackage, []);
    $orderService->updateOrderStatus($order, 'paid');

    $this->invitation = Invitation::where('user_id', $this->user->id)->firstOrFail();
    $this->user->forceFill(['active_invitation_id' => $this->invitation->id])->save();
});

test('love story page shows existing stories and text fields', function () {
    $first = LoveStory::factory()->for($this->invitation)->create(['sort_order' => 0]);
    $second = LoveStory::factory()->for($this->invitation)->create(['sort_order' => 1]);

    $this->invitation->content()->updateOrCreate(
        ['invitation_id' => $this->invitation->id],
        ['love_story' => 'Kisah kami', 'special_message' => 'Terima kasih']
    );

    $this->actingAs($this->user)
        ->get(route('dashboard.love-story'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('love_story', 'Kisah kami')
            ->where('special_message', 'Terima kasih')
            ->has('stories', 2)
            ->where('stories.0.id', $first->id)
            ->where('stories.1.id', $second->id)
            ->where('stories.0.sort_order', 0)
            ->where('stories.1.sort_order', 1)
        );
});

test('love story update creates new stories and syncs content', function () {
    $this->actingAs($this->user)
        ->post(route('dashboard.love-story.update'), [
            'love_story' => 'Kisah kami dimulai',
            'special_message' => 'Pesan untuk tamu',
            'stories' => [
                ['title' => 'Pertama Bertemu', 'date_label' => 'Januari 2020', 'description' => 'Kami bertemu di kampus.', 'sort_order' => 0],
                ['title' => 'Lamaran', 'date_label' => 'Maret 2025', 'description' => null, 'sort_order' => 1],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->invitation->fresh()->loveStories)->toHaveCount(2);

    $stories = $this->invitation->fresh()->loveStories;
    expect($stories[0]->title)->toBe('Pertama Bertemu')
        ->and($stories[0]->date_label)->toBe('Januari 2020')
        ->and($stories[0]->sort_order)->toBe(0)
        ->and($stories[1]->title)->toBe('Lamaran')
        ->and($stories[1]->sort_order)->toBe(1);

    $content = $this->invitation->fresh()->content;
    expect($content->love_story)->toBe('Kisah kami dimulai')
        ->and($content->special_message)->toBe('Pesan untuk tamu');
});

test('love story update edits existing stories, honors sort order and deletes removed', function () {
    $keep = LoveStory::factory()->for($this->invitation)->create([
        'title' => 'Lama 1',
        'sort_order' => 0,
    ]);
    $remove = LoveStory::factory()->for($this->invitation)->create([
        'title' => 'Lama 2',
        'sort_order' => 1,
    ]);

    $this->actingAs($this->user)
        ->post(route('dashboard.love-story.update'), [
            'stories' => [
                ['id' => $keep->id, 'title' => 'Lama 1 (diedit)', 'date_label' => null, 'description' => 'Deskripsi baru', 'sort_order' => 5],
            ],
        ])
        ->assertRedirect();

    expect($this->invitation->fresh()->loveStories)->toHaveCount(1);

    $story = $this->invitation->fresh()->loveStories->first();
    expect($story->id)->toBe($keep->id)
        ->and($story->title)->toBe('Lama 1 (diedit)')
        ->and($story->description)->toBe('Deskripsi baru')
        ->and($story->sort_order)->toBe(5);

    expect(LoveStory::find($remove->id))->toBeNull();
});

test('love story update rejects stories missing title', function () {
    $this->actingAs($this->user)
        ->post(route('dashboard.love-story.update'), [
            'stories' => [
                ['title' => '', 'date_label' => 'Januari 2020', 'description' => null, 'sort_order' => 0],
            ],
        ])
        ->assertSessionHasErrors('stories.0.title');

    expect($this->invitation->fresh()->loveStories)->toBeEmpty();
});
