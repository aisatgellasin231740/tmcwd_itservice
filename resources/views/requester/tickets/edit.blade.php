@extends('layouts.app')
@section('title', 'Edit ' . $ticket->ticket_number)

@section('content')
<div class="mb-6">
    <a href="{{ route('requester.tickets.show', $ticket) }}"
       class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Ticket
    </a>
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Request</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                <span class="font-mono">{{ $ticket->ticket_number }}</span>
                &mdash; You can only edit while the ticket is still
                <span class="font-semibold text-blue-700">Open</span>.
            </p>
        </div>
    </div>
</div>

<div class="max-w-2xl">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('requester.tickets.update', $ticket) }}"
                  class="space-y-5">
                @csrf
                @method('PATCH')

                {{-- Title --}}
                <div>
                    <label for="title" class="form-label">
                        Request Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title"
                           value="{{ old('title', $ticket->title) }}"
                           class="form-input @error('title') border-red-400 @enderror">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="category_id" class="form-label">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" name="category_id"
                            class="form-select @error('category_id') border-red-400 @enderror">
                        <option value="">Select category...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $ticket->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="form-label">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" rows="6"
                              class="form-textarea @error('description') border-red-400 @enderror">{{ old('description', $ticket->description) }}</textarea>
                    @error('description')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Note: dept and priority are locked (IT-side concerns) --}}
                <p class="text-xs text-gray-400">
                    Department and priority cannot be changed after submission.
                    If these need adjusting, please add a comment and the IT team will help.
                </p>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="{{ route('requester.tickets.show', $ticket) }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
