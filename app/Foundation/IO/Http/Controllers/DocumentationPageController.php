<?php

namespace App\Foundation\IO\Http\Controllers;

use App\Foundation\UseCases\RenderReadme;
use Inertia\Inertia;
use Inertia\Response;

class DocumentationPageController
{
    public function show(RenderReadme $renderReadme): Response
    {
        return Inertia::render('Documentation', [
            'html' => $renderReadme->execute(),
        ]);
    }
}
