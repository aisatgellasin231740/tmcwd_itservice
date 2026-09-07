<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function download(Request $request, Attachment $attachment)
    {
        // Authorize: user must be able to view the parent ticket
        $this->authorize('view', $attachment->ticket);

        if (! Storage::disk('local')->exists($attachment->stored_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download(
            $attachment->stored_path,
            $attachment->original_name
        );
    }
}
