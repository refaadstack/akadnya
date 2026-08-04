<?php

namespace Database\Factories;

use App\Models\Guest;
use App\Models\GuestBookEntry;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestBookEntryFactory extends Factory
{
    protected $model = GuestBookEntry::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'guest_id' => Guest::factory(),
            'event_type' => 'check_in',
        ];
    }
}
