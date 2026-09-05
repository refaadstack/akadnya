<?php

use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->service = new MediaService;
});

function makeAudioUpload(string $name, string $content, ?string $mime = null): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'audio_test');
    file_put_contents($path, $content);

    return new UploadedFile($path, $name, $mime, null, true);
}

test('music upload accepts common audio formats', function () {
    $samples = [
        // MP3 frame sync
        'lagu.mp3' => "\xFF\xFB\x90\x64\x00\x00".str_repeat("\x00", 300),
        // WAV RIFF header
        'suara.wav' => "RIFF\x24\x00\x00\x00WAVEfmt \x10\x00\x00\x00\x01\x00\x01\x00\x40\x1f\x00\x00\x80\x3e\x00\x00\x02\x00\x10\x00data".str_repeat("\x00", 100),
        // OGG container magic
        'audio.ogg' => "OggS\x00\x02\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00".str_repeat("\x00", 100),
        // M4A ftyp box
        'rekaman.m4a' => "\x00\x00\x00\x20ftypM4A \x00\x00\x00\x00M4A mp42isom".str_repeat("\x00", 100),
        // FLAC magic
        'hi-fi.flac' => "fLaC\x00\x00\x00\x22\x04\x80\x00\x00\x00\x00\x00".str_repeat("\x00", 100),
    ];

    foreach ($samples as $name => $content) {
        $url = $this->service->upload(makeAudioUpload($name, $content), 'music');

        expect($url)->toContain('/storage/invitations/music/');
    }
});

test('music upload accepts files finfo cannot classify when extension is audio', function () {
    // Some MP3 encoders produce files finfo reports as text/plain or other
    // non-audio MIME types; the audio extension keeps them accepted
    $url = $this->service->upload(makeAudioUpload('lagu.mp3', 'some bytes finfo cannot classify'), 'music');

    expect($url)->toContain('/storage/invitations/music/');
});

test('music upload rejects non-audio files', function () {
    expect(fn () => $this->service->upload(makeAudioUpload('catatan.txt', 'hello world'), 'music'))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => $this->service->upload(makeAudioUpload('foto.png', "\x89PNG\r\n\x1a\n".str_repeat("\x00", 100)), 'music'))
        ->toThrow(InvalidArgumentException::class);
});

function makeImageUpload(string $name, string $content, ?string $mime = null): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'image_test');
    file_put_contents($path, $content);

    return new UploadedFile($path, $name, $mime, null, true);
}

test('background upload stores under invitations/backgrounds', function () {
    $file = makeImageUpload('background.jpg', "\xFF\xD8\xFF\xE0".str_repeat("\x00", 100), 'image/jpeg');

    $url = $this->service->upload($file, 'background');

    expect($url)->toContain('/storage/invitations/backgrounds/');
});

test('background upload rejects non-image files', function () {
    $file = makeImageUpload('dokumen.txt', 'hello world', 'text/plain');

    expect(fn () => $this->service->upload($file, 'background'))
        ->toThrow(InvalidArgumentException::class);
});

test('displayUrl returns host-relative path for app storage urls', function () {
    expect(MediaService::displayUrl('https://akadnya.com/storage/invitations/gallery/foto.jpeg'))
        ->toBe('/storage/invitations/gallery/foto.jpeg');
});

test('displayUrl leaves external urls untouched', function () {
    expect(MediaService::displayUrl('https://cdn.example.com/foto.jpg'))
        ->toBe('https://cdn.example.com/foto.jpg');
});

test('displayUrl passes through null and empty values', function () {
    expect(MediaService::displayUrl(null))->toBeNull();
    expect(MediaService::displayUrl(''))->toBe('');
});
