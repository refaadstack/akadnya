<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Services\GrantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GrantController extends Controller
{
    public function __construct(
        protected GrantService $grantService
    ) {}

    public function activate(Request $request, string $slug): RedirectResponse
    {
        $template = Template::active()->where('slug', $slug)->firstOrFail();

        $invitation = $this->grantService->activateTemplate(
            $request->user(),
            $template
        );

        return redirect()->route('dashboard.editor')
            ->with('success', "Undangan {$template->name} berhasil diaktifkan. Selamat mengisi!");
    }
}
