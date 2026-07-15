<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boq;
use App\Models\UploadedDocument;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BoqController extends Controller
{
    public function index(): View
    {
        return view('admin.boqs.index');
    }

    public function show(string $uuid): View
    {
        $boq = Boq::where('uuid', $uuid)
            ->with(['project', 'client', 'items.unit', 'uploadedDocuments.uploadedBy'])
            ->firstOrFail();

        return view('admin.boqs.show', compact('boq'));
    }

    public function downloadDocument(string $uuid, string $documentUuid): StreamedResponse|Response
    {
        $boq = Boq::where('uuid', $uuid)->firstOrFail();

        $document = UploadedDocument::where('uuid', $documentUuid)
            ->where('boq_id', $boq->id)
            ->firstOrFail();

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }
}
