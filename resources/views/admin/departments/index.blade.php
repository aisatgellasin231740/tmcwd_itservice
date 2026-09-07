@extends('layouts.app')
@section('title', 'Departments')

@section('content')
<div class="mb-6"><h1 class="text-2xl font-bold text-gray-900">Departments</h1></div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- List --}}
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Users</th><th>Tickets</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($departments as $dept)
                <tr>
                    <td class="font-medium text-gray-800">{{ $dept->name }}</td>
                    <td class="text-gray-500">{{ $dept->users_count }}</td>
                    <td class="text-gray-500">{{ $dept->tickets_count }}</td>
                    <td>
                        @if($dept->is_active)
                            <span class="badge bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="badge bg-red-100 text-red-700">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button onclick="editDept({{ $dept->id }}, '{{ addslashes($dept->name) }}', {{ $dept->is_active ? 1 : 0 }})"
                                class="btn-secondary btn-sm">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Add / Edit Form --}}
    <div class="card" x-data="deptForm()" x-init="init()">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800" x-text="editId ? 'Edit Department' : 'Add Department'"></h2>
        </div>
        <div class="card-body">
            <form :action="editId ? `/admin/departments/${editId}` : '{{ route('admin.departments.store') }}'"
                  method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" x-bind:value="editId ? 'PUT' : 'POST'">
                <div>
                    <label class="form-label">Department Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="name"
                           class="form-input @error('name') border-red-400 @enderror" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="dept_active" name="is_active" value="1"
                           x-bind:checked="isActive" @change="isActive = $event.target.checked"
                           class="rounded border-gray-300 text-blue-600">
                    <label for="dept_active" class="text-sm text-gray-700">Active</label>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary" x-text="editId ? 'Save Changes' : 'Add Department'"></button>
                    <button type="button" @click="reset()" x-show="editId" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deptForm() {
    return { editId: null, name: '', isActive: true,
        init() { window.editDept = (id, name, active) => { this.editId = id; this.name = name; this.isActive = !!active; }; },
        reset() { this.editId = null; this.name = ''; this.isActive = true; }
    }
}
</script>
@endpush
