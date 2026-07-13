<?php

namespace App\Http\Controllers;

use App\Models\Tenancy;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class TenancyDocumentController extends Controller
{
    public function __invoke(Request $request, Media $media): Response
    {
        abort_unless($media->model instanceof Tenancy, Response::HTTP_NOT_FOUND);
        abort_unless($media->collection_name === 'documents', Response::HTTP_NOT_FOUND);

        $this->authorize('view', $media->model);

        return response()->file($media->getPath(), [
            'Content-Disposition' => 'inline; filename="'.$media->file_name.'"',
        ]);
    }
}
