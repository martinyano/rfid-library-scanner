<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit(SettingService $settings)
    {
        return view('admin.settings.edit', [
            'settings' => $settings->all(),
        ]);
    }

    public function update(Request $request, SettingService $settings)
    {
        $data = $request->validate([
            'library_name' => ['required', 'string', 'max:120'],
            'borrow_days' => ['required', 'integer', 'min:1', 'max:90'],
            'max_books' => ['required', 'integer', 'min:1', 'max:20'],
            'due_soon_days' => ['required', 'integer', 'min:0', 'max:30'],
            'auto_close_hours' => ['required', 'integer', 'min:1', 'max:72'],
        ]);

        $settings->setMany($data);

        return back()->with('success', 'Library settings updated successfully.');
    }
}
