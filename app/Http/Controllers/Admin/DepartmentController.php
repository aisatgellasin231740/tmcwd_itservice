<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount(['users', 'tickets'])->orderBy('name')->get();
        return view('admin.departments.index', compact('departments'));
    }

    public function store(StoreDepartmentRequest $request)
    {
        Department::create([
            'name'      => $request->validated('name'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Department added.');
    }

    public function update(StoreDepartmentRequest $request, Department $department)
    {
        $department->update([
            'name'      => $request->validated('name'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        // Block if any open/active tickets are still assigned to this department
        $openCount = $department->tickets()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        if ($openCount > 0) {
            return back()->with('error',
                "Cannot delete \"{$department->name}\" — it has {$openCount} open ticket(s). " .
                "Resolve or close them first, or reassign them to another department."
            );
        }

        $department->delete(); // soft-delete

        return back()->with('success', "\"{$department->name}\" has been deleted.");
    }
}
