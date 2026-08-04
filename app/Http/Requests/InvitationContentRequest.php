<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvitationContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Identitas Mempelai
            'bride_name' => 'required|string|max:255',
            'bride_nickname' => 'nullable|string|max:255',
            'bride_father' => 'nullable|string|max:255',
            'bride_mother' => 'nullable|string|max:255',
            'bride_photo_url' => 'nullable|url|max:500',
            'groom_name' => 'required|string|max:255',
            'groom_nickname' => 'nullable|string|max:255',
            'groom_father' => 'nullable|string|max:255',
            'groom_mother' => 'nullable|string|max:255',
            'groom_photo_url' => 'nullable|url|max:500',
            'cover_name_display' => 'nullable|string|in:full,nickname,initials',
            'couple_photo_url' => 'nullable|url|max:500',

            // Acara
            'akad_datetime' => 'required|date',
            'akad_venue' => 'required|string|max:500',
            'akad_maps_url' => 'nullable|url|max:500',
            'reception_datetime' => 'nullable|date',
            'reception_venue' => 'nullable|string|max:500',
            'reception_maps_url' => 'nullable|url|max:500',
            'show_reception' => 'boolean',

            // Konten
            'love_story' => 'nullable|string',
            'special_message' => 'nullable|string',

            // Media
            'cover_photo_url' => 'nullable|url|max:500',
            'background_url' => 'nullable|url|max:500',
            'music_url' => 'nullable|url|max:500',
            'music_title' => 'nullable|string|max:255',
            'gallery_photos' => 'nullable|array',
            'gallery_photos.*.url' => 'required|url|max:500',
            'gallery_photos.*.caption' => 'nullable|string|max:255',

            // Amplop Digital
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'qris_image_url' => 'nullable|url|max:500',
            'gopay_number' => 'nullable|string|max:20',
            'ovo_number' => 'nullable|string|max:20',
            'dana_number' => 'nullable|string|max:20',
            'gift_address' => 'nullable|string',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'bride_name' => 'nama mempelai wanita',
            'bride_father' => 'nama ayah mempelai wanita',
            'bride_mother' => 'nama ibu mempelai wanita',
            'bride_photo_url' => 'foto mempelai wanita',
            'groom_name' => 'nama mempelai pria',
            'groom_father' => 'nama ayah mempelai pria',
            'groom_mother' => 'nama ibu mempelai pria',
            'groom_photo_url' => 'foto mempelai pria',
            'cover_name_display' => 'tampilan nama di cover',
            'couple_photo_url' => 'foto pasangan',
            'akad_datetime' => 'tanggal & waktu akad',
            'akad_venue' => 'tempat akad',
            'akad_maps_url' => 'link Google Maps akad',
            'reception_datetime' => 'tanggal & waktu resepsi',
            'reception_venue' => 'tempat resepsi',
            'reception_maps_url' => 'link Google Maps resepsi',
            'love_story' => 'cerita cinta',
            'special_message' => 'pesan khusus',
            'cover_photo_url' => 'foto cover',
            'background_url' => 'background halaman',
            'music_url' => 'musik latar',
            'music_title' => 'judul musik',
            'gallery_photos' => 'foto galeri',
            'bank_name' => 'nama bank',
            'account_number' => 'nomor rekening',
            'account_name' => 'nama pemilik rekening',
            'qris_image_url' => 'gambar QRIS',
            'gopay_number' => 'nomor GoPay',
            'ovo_number' => 'nomor OVO',
            'dana_number' => 'nomor DANA',
            'gift_address' => 'alamat kirim hadiah',
        ];
    }
}
