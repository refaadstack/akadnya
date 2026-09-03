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
                'title' => 'Syarat & Ketentuan | Akadnya.com',
                'description' => 'Syarat & Ketentuan penggunaan layanan Akadnya.com - platform undangan digital. Bacalah sebelum membuat akun dan menggunakan layanan.',
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
                'title' => 'Kebijakan Privasi | Akadnya.com',
                'description' => 'Kebijakan Privasi Akadnya.com - bagaimana kami mengumpulkan, menggunakan, dan melindungi data pribadi Anda.',
            ],
        ]);
    }
}
