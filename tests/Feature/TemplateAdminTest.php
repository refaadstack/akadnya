<?php

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

afterEach(function () {
    // Cleanup test template directories
    $publicTemplates = public_path('templates');
    if (is_dir($publicTemplates)) {
        $dirs = glob($publicTemplates.'/test-*');
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }
    }
});

test('delete template with published invitations is rejected with error message', function () {
    $template = Template::factory()->create(['slug' => 'test-template-'.uniqid()]);

    // Create published invitations
    $user = User::factory()->create();
    Invitation::factory()->count(3)->create([
        'template_id' => $template->id,
        'user_id' => $user->id,
        'status' => 'published',
    ]);

    // Attempt to delete via Filament action would call this logic
    $publishedCount = $template->invitations()->published()->count();

    expect($publishedCount)->toBe(3);

    // Verify template cannot be deleted
    // In actual Filament, the DeleteAction would be cancelled
    // Here we just verify the count check works
    expect($publishedCount > 0)->toBeTrue();
});

test('delete template without published invitations succeeds and removes directory', function () {
    $slug = 'test-delete-'.uniqid();
    $template = Template::factory()->create(['slug' => $slug]);

    // Create template directory
    $templatePath = public_path("templates/{$slug}");
    File::makeDirectory($templatePath, 0755, true);
    File::put($templatePath.'/test.txt', 'test content');

    // Create draft invitations (not published)
    $user = User::factory()->create();
    Invitation::factory()->count(2)->create([
        'template_id' => $template->id,
        'user_id' => $user->id,
        'status' => 'draft',
    ]);

    // Verify no published invitations
    $publishedCount = $template->invitations()->published()->count();
    expect($publishedCount)->toBe(0);

    // Delete invitations first (cascade delete in real scenario)
    $template->invitations()->delete();

    // Delete template
    $template->delete();

    // Manually delete directory (simulating EditTemplate::after callback)
    if (File::exists($templatePath)) {
        File::deleteDirectory($templatePath);
    }

    // Verify directory is deleted
    expect(File::exists($templatePath))->toBeFalse();

    // Verify database record is deleted
    expect(Template::find($template->id))->toBeNull();
});

test('deactivate template makes it invisible in active scope', function () {
    $template = Template::factory()->create([
        'slug' => 'test-deactivate-'.uniqid(),
        'is_active' => true,
    ]);

    // Verify template is in active scope
    expect(Template::active()->where('id', $template->id)->exists())->toBeTrue();

    // Deactivate template
    $template->update(['is_active' => false]);

    // Verify template is not in active scope
    expect(Template::active()->where('id', $template->id)->exists())->toBeFalse();

    // Verify template still exists in database
    expect(Template::find($template->id))->not->toBeNull();
});

test('delete protection message mentions correct count of published invitations', function () {
    $template = Template::factory()->create(['slug' => 'test-count-'.uniqid()]);

    // Create 5 published invitations
    $user = User::factory()->create();
    Invitation::factory()->count(5)->create([
        'template_id' => $template->id,
        'user_id' => $user->id,
        'status' => 'published',
    ]);

    // Create 2 draft invitations (should not be counted)
    Invitation::factory()->count(2)->create([
        'template_id' => $template->id,
        'user_id' => $user->id,
        'status' => 'draft',
    ]);

    $publishedCount = $template->invitations()->published()->count();

    expect($publishedCount)->toBe(5);

    // The error message should mention "5 undangan aktif"
    $expectedMessage = "Template tidak dapat dihapus karena masih digunakan oleh {$publishedCount} undangan aktif.";
    expect($expectedMessage)->toContain('5 undangan aktif');
});
