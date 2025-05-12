<?php

namespace App\Domain\Projects\Controllers;

use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Projects\Models\Task;
use App\Domain\Projects\Requests\TaskAttachmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TaskAttachmentsController extends Controller
{
    public function index(Task $task)
    {
        $media = $task->getMedia('attachments');

        return response()->json([
            'data' => MediaDTO::collection($media),
        ]);
    }

    public function store(TaskAttachmentRequest $request, Task $task)
    {
        $file  = $request->file('file');
        $media = $task->addMedia($file)->toMediaCollection('attachments');

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function show(Task $task, Media $media)
    {
        $this->authorizeMedia($task, $media);

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ]);
    }

    public function download(Task $task, Media $media)
    {
        $this->authorizeMedia($task, $media);
        $path    = $media->getPath();
        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $media->file_name . '"',
        ];

        return response()->download($path, $media->file_name, $headers);
    }

    public function preview(Task $task, Media $media)
    {
        $this->authorizeMedia($task, $media);
        $path    = $media->getPath();
        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ];

        return response()->file($path, $headers);
    }

    public function destroy(Task $task, Media $media)
    {
        $this->authorizeMedia($task, $media);
        $media->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function authorizeMedia(Task $task, Media $media): void
    {
        if (Task::class !== $media->model_type || $media->model_id !== $task->id) {
            abort(Response::HTTP_NOT_FOUND, 'Attachment not found for this task.');
        }
    }
}
