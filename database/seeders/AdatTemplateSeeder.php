<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\TemplateOrnament;
use App\Models\TemplateSection;
use Illuminate\Database\Seeder;

class AdatTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Template 1: Adat Jawa
        $jawa = Template::updateOrCreate(
            ['slug' => 'adat-jawa'],
            [
                'name' => 'Adat Jawa',
                'thumbnail_url' => '/templates/adat-jawa/thumbnail.jpg',
                'version' => '1.0.0',
                'is_free' => false,
                'price' => 149000,
                'original_price' => 199000,
                'is_active' => true,
            ]
        );

        // Sections untuk Adat Jawa
        // Delete old sections first
        $jawa->sections()->delete();

        $jawaSections = [
            ['file' => 'cover.vue', 'label' => 'Cover', 'sort_order' => 1, 'is_required' => true],
            ['file' => 'opening.vue', 'label' => 'Pembukaan', 'sort_order' => 2, 'is_required' => true],
            ['file' => 'bride-groom.vue', 'label' => 'Mempelai', 'sort_order' => 3, 'is_required' => true],
            ['file' => 'event-details.vue', 'label' => 'Detail Acara', 'sort_order' => 4, 'is_required' => true],
            ['file' => 'love-story.vue', 'label' => 'Cerita Cinta', 'sort_order' => 5, 'is_required' => false],
            ['file' => 'gallery.vue', 'label' => 'Galeri Foto', 'sort_order' => 6, 'is_required' => false],
            ['file' => 'gift.vue', 'label' => 'Amplop Digital', 'sort_order' => 7, 'is_required' => false],
            ['file' => 'rsvp.vue', 'label' => 'RSVP', 'sort_order' => 8, 'is_required' => true],
            ['file' => 'wishes.vue', 'label' => 'Ucapan & Doa', 'sort_order' => 9, 'is_required' => false],
            ['file' => 'closing.vue', 'label' => 'Penutup', 'sort_order' => 10, 'is_required' => true],
        ];

        foreach ($jawaSections as $section) {
            TemplateSection::create(array_merge(['template_id' => $jawa->id], $section));
        }

        // Ornaments untuk Adat Jawa
        // Delete old ornaments first
        $jawa->ornaments()->delete();

        $jawaOrnaments = [
            ['file' => 'batik-corner.vue', 'label' => 'Batik Corner', 'position' => 'top-left'],
            ['file' => 'wayang-divider.vue', 'label' => 'Wayang Divider', 'position' => 'center'],
            ['file' => 'gamelan-footer.vue', 'label' => 'Gamelan Footer', 'position' => 'bottom'],
        ];

        foreach ($jawaOrnaments as $ornament) {
            TemplateOrnament::create(array_merge(['template_id' => $jawa->id], $ornament));
        }

        // Template 2: Adat Minang
        $minang = Template::updateOrCreate(
            ['slug' => 'adat-minang'],
            [
                'name' => 'Adat Minang',
                'thumbnail_url' => '/templates/adat-minang/thumbnail.jpg',
                'version' => '1.0.0',
                'is_free' => false,
                'price' => 149000,
                'original_price' => 199000,
                'is_active' => true,
            ]
        );

        // Sections untuk Adat Minang
        // Delete old sections first
        $minang->sections()->delete();

        $minangSections = [
            ['file' => 'cover.vue', 'label' => 'Cover', 'sort_order' => 1, 'is_required' => true],
            ['file' => 'opening.vue', 'label' => 'Pembukaan', 'sort_order' => 2, 'is_required' => true],
            ['file' => 'bride-groom.vue', 'label' => 'Mempelai', 'sort_order' => 3, 'is_required' => true],
            ['file' => 'event-details.vue', 'label' => 'Detail Acara', 'sort_order' => 4, 'is_required' => true],
            ['file' => 'love-story.vue', 'label' => 'Cerita Cinta', 'sort_order' => 5, 'is_required' => false],
            ['file' => 'gallery.vue', 'label' => 'Galeri Foto', 'sort_order' => 6, 'is_required' => false],
            ['file' => 'gift.vue', 'label' => 'Amplop Digital', 'sort_order' => 7, 'is_required' => false],
            ['file' => 'rsvp.vue', 'label' => 'RSVP', 'sort_order' => 8, 'is_required' => true],
            ['file' => 'wishes.vue', 'label' => 'Ucapan & Doa', 'sort_order' => 9, 'is_required' => false],
            ['file' => 'closing.vue', 'label' => 'Penutup', 'sort_order' => 10, 'is_required' => true],
        ];

        foreach ($minangSections as $section) {
            TemplateSection::create(array_merge(['template_id' => $minang->id], $section));
        }

        // Ornaments untuk Adat Minang
        // Delete old ornaments first
        $minang->ornaments()->delete();

        $minangOrnaments = [
            ['file' => 'gonjong-header.vue', 'label' => 'Gonjong Header', 'position' => 'top'],
            ['file' => 'songket-border.vue', 'label' => 'Songket Border', 'position' => 'side'],
            ['file' => 'rumah-gadang.vue', 'label' => 'Rumah Gadang', 'position' => 'background'],
        ];

        foreach ($minangOrnaments as $ornament) {
            TemplateOrnament::create(array_merge(['template_id' => $minang->id], $ornament));
        }

        // Template 3: Adat Bali
        $bali = Template::updateOrCreate(
            ['slug' => 'adat-bali'],
            [
                'name' => 'Adat Bali',
                'thumbnail_url' => '/templates/adat-bali/thumbnail.jpg',
                'version' => '1.0.0',
                'is_free' => false,
                'price' => 149000,
                'original_price' => 199000,
                'is_active' => true,
            ]
        );

        // Sections untuk Adat Bali
        // Delete old sections first
        $bali->sections()->delete();

        $baliSections = [
            ['file' => 'cover.vue', 'label' => 'Cover', 'sort_order' => 1, 'is_required' => true],
            ['file' => 'opening.vue', 'label' => 'Pembukaan', 'sort_order' => 2, 'is_required' => true],
            ['file' => 'bride-groom.vue', 'label' => 'Mempelai', 'sort_order' => 3, 'is_required' => true],
            ['file' => 'event-details.vue', 'label' => 'Detail Acara', 'sort_order' => 4, 'is_required' => true],
            ['file' => 'love-story.vue', 'label' => 'Cerita Cinta', 'sort_order' => 5, 'is_required' => false],
            ['file' => 'gallery.vue', 'label' => 'Galeri Foto', 'sort_order' => 6, 'is_required' => false],
            ['file' => 'gift.vue', 'label' => 'Amplop Digital', 'sort_order' => 7, 'is_required' => false],
            ['file' => 'rsvp.vue', 'label' => 'RSVP', 'sort_order' => 8, 'is_required' => true],
            ['file' => 'wishes.vue', 'label' => 'Ucapan & Doa', 'sort_order' => 9, 'is_required' => false],
            ['file' => 'closing.vue', 'label' => 'Penutup', 'sort_order' => 10, 'is_required' => true],
        ];

        foreach ($baliSections as $section) {
            TemplateSection::create(array_merge(['template_id' => $bali->id], $section));
        }

        // Ornaments untuk Adat Bali
        // Delete old ornaments first
        $bali->ornaments()->delete();

        $baliOrnaments = [
            ['file' => 'penjor-side.vue', 'label' => 'Penjor Side', 'position' => 'side'],
            ['file' => 'barong-divider.vue', 'label' => 'Barong Divider', 'position' => 'center'],
            ['file' => 'frangipani-float.vue', 'label' => 'Frangipani Float', 'position' => 'floating'],
        ];

        foreach ($baliOrnaments as $ornament) {
            TemplateOrnament::create(array_merge(['template_id' => $bali->id], $ornament));
        }
    }
}
