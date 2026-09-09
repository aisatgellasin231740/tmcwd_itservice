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

    public function destroy(Priority $priority)
    {
        $openCount = $priority->tickets()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        if ($openCount > 0) {
            return back()->with('error',
                "Cannot delete the \"{$priority->name}\" priority — it has {$openCount} open ticket(s). " .
                "Resolve or close them first."
            );
        }

        $priority->delete();

        return back()->with('success', "\"{$priority->name}\" priority has been deleted.");
    }
}
