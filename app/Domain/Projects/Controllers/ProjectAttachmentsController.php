<?php

namespace App\Domain\Projects\Controllers;

use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Requests\ProjectAttachmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProjectAttachmentsController extends Controller
{
    use AuthorizesRequests;

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $media = $project->getMedia('attachments');

        return response()->json([
            'data' => MediaDTO::collection($media),
        ]);
    }

    public function store(ProjectAttachmentRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $file = $request->file('file');
        $media = $project->addMedia($file)->toMediaCollection('attachments');

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function show(Project $project, Media $media)
    {
        $this->authorize('view', $project);
        $this->authorizeMedia($project, $media);

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ]);
    }

    public function download(Project $project, Media $media)
    {
        $this->authorize('view', $project);
        $this->authorizeMedia($project, $media);
        $path = $media->getPath();
        $headers = [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'attachment; filename="'.$media->file_name.'"',
        ];

        return response()->download($path, $media->file_name, $headers);
    }

    public function preview(Project $project, Media $media)
    {
        $this->authorize('view', $project);
        $this->authorizeMedia($project, $media);
        $path = $media->getPath();
        $headers = [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline; filename="'.$media->file_name.'"',
        ];

        return response()->file($path, $headers);
    }

    public function destroy(Project $project, Media $media)
    {
        $this->authorize('update', $project);
        $this->authorizeMedia($project, $media);
        $media->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function authorizeMedia(Project $project, Media $media): void
    {
        if ($media->model_type !== Project::class || $media->model_id !== $project->id) {
            abort(Response::HTTP_NOT_FOUND, 'Attachment not found for this project.');
        }
    }
}
