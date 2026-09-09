@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="mb-6"><h1 class="text-2xl font-bold text-gray-900">Ticket Categories</h1></div>

@if(session('error'))
<div class="mb-4 flex items-center gap-2.5 px-3 py-2.5 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs shadow-sm">
    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>{{ session('error') }}</span>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Tickets</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td class="font-medium text-gray-800">{{ $cat->name }}</td>
                    <td class="text-gray-500">{{ $cat->tickets_count }}</td>
                    <td>
                        @if($cat->is_active)
                            <span class="badge bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="badge bg-red-100 text-red-700">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-1.5">
                            <button onclick="editCat({{ $cat->id }}, '{{ addslashes($cat->name) }}', {{ $cat->is_active ? 1 : 0 }})"
                                    class="btn-secondary btn-sm">Edit</button>
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}"
                                  onsubmit="return confirm('Delete \'{{ addslashes($cat->name) }}\'?\n\nExisting tickets will keep this category label but it will be removed from new ticket dropdowns.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg
                                               border border-red-200 text-red-600 bg-white hover:bg-red-50 transition-colors">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card" x-data="catForm()" x-init="init()">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800" x-text="editId ? 'Edit Category' : 'Add Category'"></h2>
        </div>
        <div class="card-body">
            <form :action="editId ? `/admin/categories/${editId}` : '{{ route('admin.categories.store') }}'"
                  method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" x-bind:value="editId ? 'PUT' : 'POST'">
                <div>
                    <label class="form-label">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="name"
                           class="form-input @error('name') border-red-400 @enderror" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="cat_active" name="is_active" value="1"
                           x-bind:checked="isActive" @change="isActive = $event.target.checked"
                           class="rounded border-gray-300 text-blue-600">
                    <label for="cat_active" class="text-sm text-gray-700">Active</label>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary" x-text="editId ? 'Save Changes' : 'Add Category'"></button>
                    <button type="button" @click="reset()" x-show="editId" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function catForm() {
    return { editId: null, name: '', isActive: true,
        init() { window.editCat = (id, name, active) => { this.editId = id; this.name = name; this.isActive = !!active; }; },
        reset() { this.editId = null; this.name = ''; this.isActive = true; }
    }
}
</script>
@endpush
