<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestController extends Controller
{
    /**
     * Display guest list
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $invitation = Invitation::where('user_id', $user->id)->firstOrFail();

        // Get guests with RSVP data
        $guestsQuery = $invitation->guests()
            ->with('rsvp')
            ->orderBy('created_at', 'desc');

        // Filter by category if provided
        if ($request->has('category') && $request->category !== 'all') {
            $guestsQuery->where('category', $request->category);
        }

        // Search by name or phone
        if ($request->has('search') && $request->search) {
            $guestsQuery->where(function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }

        $guests = $guestsQuery->paginate(20);

        // Get statistics
        $stats = [
            'total' => $invitation->guests()->count(),
            'family' => $invitation->guests()->where('category', 'family')->count(),
            'friends' => $invitation->guests()->where('category', 'friends')->count(),
            'colleagues' => $invitation->guests()->where('category', 'colleagues')->count(),
            'others' => $invitation->guests()->where('category', 'others')->count(),
            'confirmed' => $invitation->rsvps()->where('attendance', 'hadir')->count(),
            'declined' => $invitation->rsvps()->where('attendance', 'tidak_hadir')->count(),
            'pending' => $invitation->guests()->count() - $invitation->guests()->whereHas('rsvp')->count(),
        ];

        return inertia('Dashboard/Guests/Index', [
            'guests' => $guests->through(fn ($guest) => [
                'id' => $guest->id,
                'name' => $guest->name,
                'phone' => $guest->phone,
                'category' => $guest->category,
                'unique_code' => $guest->unique_code,
                'max_pax' => $guest->max_pax,
                'notes' => $guest->notes,
                'personal_link' => $guest->getPersonalLink(),
                'has_rsvp' => $guest->hasRsvp(),
                'rsvp' => $guest->rsvp ? [
                    'attendance' => $guest->rsvp->attendance,
                    'pax_count' => $guest->rsvp->pax_count,
                    'message' => $guest->rsvp->message,
                ] : null,
            ]),
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category ?? 'all',
            ],
        ]);
    }

    /**
     * Store a new guest
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $invitation = Invitation::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'category' => 'required|in:family,friends,colleagues,others',
            'max_pax' => 'required|integer|min:1|max:10',
            'notes' => 'nullable|string|max:500',
        ]);

        $guest = $invitation->guests()->create($validated);

        return back()->with('success', 'Tamu berhasil ditambahkan');
    }

    /**
     * Update guest
     */
    public function update(Request $request, Guest $guest)
    {
        $user = $request->user();

        // Ensure guest belongs to user's invitation
        if ($guest->invitation->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'category' => 'required|in:family,friends,colleagues,others',
            'max_pax' => 'required|integer|min:1|max:10',
            'notes' => 'nullable|string|max:500',
        ]);

        $guest->update($validated);

        return back()->with('success', 'Tamu berhasil diupdate');
    }

    /**
     * Delete guest
     */
    public function destroy(Request $request, Guest $guest)
    {
        $user = $request->user();

        // Ensure guest belongs to user's invitation
        if ($guest->invitation->user_id !== $user->id) {
            abort(403);
        }

        $guest->delete();

        return back()->with('success', 'Tamu berhasil dihapus');
    }

    /**
     * Bulk import guests from CSV/Excel
     */
    public function import(Request $request)
    {
        $user = $request->user();
        $invitation = Invitation::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        $imported = 0;
        $errors = [];
        $row = 0;

        DB::beginTransaction();
        try {
            // Skip header row
            fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== false) {
                $row++;

                // Expected format: name, phone, category, max_pax, notes
                if (count($data) < 4) {
                    $errors[] = "Baris {$row}: Format tidak lengkap";

                    continue;
                }

                $name = trim($data[0]);
                $phone = trim($data[1] ?? '');
                $category = trim($data[2] ?? 'others');
                $maxPax = (int) ($data[3] ?? 1);
                $notes = trim($data[4] ?? '');

                if (empty($name)) {
                    $errors[] = "Baris {$row}: Nama tidak boleh kosong";

                    continue;
                }

                if (! in_array($category, ['family', 'friends', 'colleagues', 'others'])) {
                    $category = 'others';
                }

                $invitation->guests()->create([
                    'name' => $name,
                    'phone' => $phone,
                    'category' => $category,
                    'max_pax' => max(1, min(10, $maxPax)),
                    'notes' => $notes,
                ]);

                $imported++;
            }

            fclose($handle);
            DB::commit();

            $message = "{$imported} tamu berhasil diimport";
            if (count($errors) > 0) {
                $message .= '. '.count($errors).' baris gagal diimport.';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);

            return back()->with('error', 'Gagal import tamu: '.$e->getMessage());
        }
    }

    /**
     * Export guests to CSV
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $invitation = Invitation::where('user_id', $user->id)->firstOrFail();

        $guests = $invitation->guests()->with('rsvp')->get();

        $filename = 'guests-'.date('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($guests) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['Nama', 'Telepon', 'Kategori', 'Max Pax', 'Catatan', 'Status RSVP', 'Jumlah Hadir', 'Pesan']);

            foreach ($guests as $guest) {
                fputcsv($file, [
                    $guest->name,
                    $guest->phone,
                    $guest->category,
                    $guest->max_pax,
                    $guest->notes,
                    $guest->rsvp ? $guest->rsvp->attendance : 'Belum konfirmasi',
                    $guest->rsvp ? $guest->rsvp->pax_count : '',
                    $guest->rsvp ? $guest->rsvp->message : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Send WhatsApp invitation link
     */
    public function sendWhatsApp(Request $request, Guest $guest)
    {
        $user = $request->user();

        // Ensure guest belongs to user's invitation
        if ($guest->invitation->user_id !== $user->id) {
            abort(403);
        }

        if (empty($guest->phone)) {
            return back()->with('error', 'Tamu tidak memiliki nomor telepon');
        }

        // Debug: Log guest data
        \Log::info('Sending WhatsApp to guest', [
            'guest_id' => $guest->id,
            'guest_name' => $guest->name,
            'guest_phone' => $guest->phone,
        ]);

        // Generate WhatsApp link
        $phone = preg_replace('/[^0-9]/', '', $guest->phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62'.substr($phone, 1);
        }

        // Get bride and groom names from invitation content
        $content = $guest->invitation->content;
        $brideName = $content->bride_name ?? null;
        $groomName = $content->groom_name ?? null;

        // Get personal link
        $personalLink = $guest->getPersonalLink();

        \Log::info('Bride and Groom names', [
            'bride_name' => $brideName,
            'groom_name' => $groomName,
            'personal_link' => $personalLink,
        ]);

        // If names are not set, use generic message
        if (! $brideName || ! $groomName) {
            $message = urlencode(
                "Kepada Yth.\n".
                "Bapak/Ibu/Saudara/i *{$guest->name}*\n\n".
                "Assalamu'alaikum Warahmatullahi Wabarakatuh\n\n".
                "Tanpa mengurangi rasa hormat, kami mengundang Anda untuk hadir di acara pernikahan kami.\n\n".
                "Untuk info lengkap acara, silakan buka undangan digital kami:\n".
                $personalLink."\n\n".
                "Merupakan suatu kehormatan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu.\n\n".
                "Wassalamu'alaikum Warahmatullahi Wabarakatuh"
            );
        } else {
            $message = urlencode(
                "Kepada Yth.\n".
                "Bapak/Ibu/Saudara/i *{$guest->name}*\n\n".
                "Assalamu'alaikum Warahmatullahi Wabarakatuh\n\n".
                "Tanpa mengurangi rasa hormat, kami mengundang Anda untuk hadir di acara pernikahan kami:\n\n".
                "*{$brideName} & {$groomName}*\n\n".
                "Untuk info lengkap acara, silakan buka undangan digital kami:\n".
                $personalLink."\n\n".
                "Merupakan suatu kehormatan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu.\n\n".
                "Wassalamu'alaikum Warahmatullahi Wabarakatuh\n\n".
                "Hormat kami,\n".
                "{$brideName} & {$groomName}"
            );
        }

        \Log::info('WhatsApp message generated', [
            'message_preview' => substr(urldecode($message), 0, 200),
        ]);

        $whatsappUrl = "https://wa.me/{$phone}?text={$message}";

        return redirect()->away($whatsappUrl);
    }
}
