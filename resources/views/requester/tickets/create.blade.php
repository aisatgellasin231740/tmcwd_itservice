@extends('layouts.app')
@section('title', 'New IT Request')

@section('content')
<div class="mb-6">
    <a href="{{ route('requester.tickets.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to My Tickets
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Submit New IT Request</h1>
    <p class="text-sm text-gray-500 mt-1">Describe your issue and our IT team will get back to you.</p>
</div>

<div class="max-w-2xl">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('requester.tickets.store') }}"
                  enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Title --}}
                <div>
                    <label for="title" class="form-label">Request Title <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title"
                           value="{{ old('title') }}"
                           placeholder="Brief summary of the issue (e.g. Cannot print from Finance PC #3)"
                           class="form-input @error('title') border-red-400 @enderror">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Department --}}
                <div>
                    <label for="department_id" class="form-label">Your Department <span class="text-red-500">*</span></label>
                    <select id="department_id" name="department_id"
                            class="form-select @error('department_id') border-red-400 @enderror">
                        <option value="">Select department...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ old('department_id', auth()->user()->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Category & Priority (2 columns) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="form-label">Category <span class="text-red-500">*</span></label>
                        <select id="category_id" name="category_id"
                                class="form-select @error('category_id') border-red-400 @enderror">
                            <option value="">Select category...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="priority_id" class="form-label">Priority <span class="text-red-500">*</span></label>
                        <select id="priority_id" name="priority_id"
                                class="form-select @error('priority_id') border-red-400 @enderror">
                            <option value="">Select priority...</option>
                            @foreach($priorities as $pri)
                                <option value="{{ $pri->id }}" {{ old('priority_id') == $pri->id ? 'selected' : '' }}>
                                    {{ $pri->name }}
                                    @if(strtolower($pri->name) === 'urgent') — System Down / Cannot Work @endif
                                </option>
                            @endforeach
                        </select>
                        @error('priority_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Priority guide --}}
                <div class="rounded-lg bg-blue-50 border border-blue-100 px-4 py-3 text-xs text-blue-700 space-y-1">
                    <p class="font-semibold">Priority Guide:</p>
                    <p>🔴 <strong>Urgent</strong> — System completely down, cannot work (e.g. Billing system, SCADA) — SLA: 2 hours</p>
                    <p>🟠 <strong>High</strong> — Major disruption, most staff affected — SLA: 8 hours</p>
                    <p>🟡 <strong>Medium</strong> — Partial disruption, workaround available — SLA: 2 days</p>
                    <p>🔵 <strong>Low</strong> — Minor issue or non-urgent request — SLA: 5 days</p>
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="form-label">Description <span class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="5"
                              placeholder="Please describe the issue in detail: what happened, when it started, what you have already tried..."
                              class="form-textarea @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                    @error('description')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Attachments --}}
                <div>
                    <label class="form-label">Attachments <span class="text-gray-400">(optional)</span></label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition"
                         x-data="fileUpload()">
                        <input type="file" id="attachments" name="attachments[]" multiple
                               accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                               class="hidden" @change="handleFiles($event)">
                        <label for="attachments" class="cursor-pointer">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <p class="text-sm text-gray-600">Click to attach files or drag & drop</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF, DOC, XLS, ZIP — max 10 MB each, up to 5 files</p>
                        </label>
                        <ul x-show="files.length" class="mt-3 text-left space-y-1">
                            <template x-for="file in files" :key="file.name">
                                <li class="text-xs text-gray-600 flex items-center gap-2">
                                    <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                    </svg>
                                    <span x-text="file.name"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    @error('attachments.*')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Submit Request
                    </button>
                    <a href="{{ route('requester.tickets.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fileUpload() {
    return {
        files: [],
        handleFiles(event) {
            this.files = Array.from(event.target.files);
        }
    }
}
</script>
@endpush
