<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function index()
    {
        $priorities = Priority::ordered()->get();
        return view('admin.priorities.index', compact('priorities'));
    }

    public function update(Request $request, Priority $priority)
    {
        $data = $request->validate([
            'sla_hours'  => ['required', 'numeric', 'min:0.1', 'max:9999'],
            'color_code' => ['required', 'string', 'max:50'],
        ]);

        $priority->update($data);

        return back()->with('success', "{$priority->name} SLA settings updated.");
    }
}
