<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OnboardingController extends Controller
{
    public function complete(Request $request): Response
    {
        $request->user()->markOnboardingCompleted();

        return response()->noContent();
    }
}
