<?php

namespace App\Staff\IO\Http\Controllers;

use App\Staff\Entities\Models\Physician;
use Inertia\Inertia;
use Inertia\Response;

class PhysicianPageController
{
    public function show(Physician $physician): Response
    {
        return Inertia::render('Physician', [
            'physicianId' => $physician->id,
        ]);
    }
}
