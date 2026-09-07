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
}
