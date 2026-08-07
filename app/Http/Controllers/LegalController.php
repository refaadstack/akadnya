<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    /**
     * Show the Terms & Conditions page.
     */
    public function terms(): Response
    {
        return Inertia::render('Legal/Terms', [
            'meta' => [
                'title' => 'Syarat & Ketentuan | MyAkad',
                'description' => 'Syarat & Ketentuan penggunaan layanan MyAkad - platform undangan digital. Bacalah sebelum membuat akun dan menggunakan layanan.',
            ],
        ]);
    }

    /**
     * Show the Privacy Policy page.
     */
    public function privacy(): Response
    {
        return Inertia::render('Legal/Privacy', [
            'meta' => [
                'title' => 'Kebijakan Privasi | MyAkad',
                'description' => 'Kebijakan Privasi MyAkad - bagaimana kami mengumpulkan, menggunakan, dan melindungi data pribadi Anda.',
            ],
        ]);
    }
}
