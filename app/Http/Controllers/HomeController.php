<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __invoke()
    {
        return redirect()->route(auth()->check() ? 'admin.dashboard' : 'admin.login');
    }
}
