<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\UploadProfileImageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UserProfileImageController extends Controller
{
    public function upload(UploadProfileImageRequest $request)
    {
        /** @var User $user */
        $user = $request->user();

        $user->clearMediaCollection('profile');

        $user->addMediaFromRequest('image')
            ->toMediaCollection('profile')
        ;

        $avatarUrl = $user->getMediaSignedUrl('profile');
        $thumbUrl  = $user->getMediaSignedUrl('profile', 'thumb');

        $user->update([
            'avatar_url' => $avatarUrl,
        ]);

        return response()->json([
            'message'     => 'Profile image uploaded successfully.',
            'originalUrl' => $avatarUrl,
            'thumbUrl'    => $thumbUrl,
        ]);
    }

    public function show(Request $request)
    {
        $user  = $request->user();
        $thumb = $request->query('thumb', false);
        $media = $thumb ? $user->getFirstMedia('profile', 'thumb') : $user->getFirstMedia('profile');

        if ($thumb & !$media) {
            $media = $user->getFirstMedia('profile');
        }

        if (!$media) {
            return response()->json(['message' => 'No profile image found.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $stream = Storage::disk($media->disk)->readStream($media->getPath());

        return Response::stream(function () use ($stream) {
            fpassthru($stream);
        }, HttpResponse::HTTP_OK, [
            'Content-Type'        => $media->mime_type,
            'Content-Length'      => $media->size,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ]);
    }

    public function showForUser(User $user, Request $request)
    {
        $thumb = $request->query('thumb', false);
        $media = $thumb ? $user->getFirstMedia('profile', 'thumb') : $user->getFirstMedia('profile');

        if ($thumb & !$media) {
            $media = $user->getFirstMedia('profile');
        }

        if (!$media) {
            return response()->json(['message' => 'No profile image found.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $stream = Storage::disk($media->disk)->readStream($media->getPath());

        return Response::stream(function () use ($stream) {
            fpassthru($stream);
        }, HttpResponse::HTTP_OK, [
            'Content-Type'        => $media->mime_type,
            'Content-Length'      => $media->size,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ]);
    }

    public function delete(Request $request)
    {
        $user = $request->user();
        $user->clearMediaCollection('profile');

        $user->update([
            'avatar_url' => null,
        ]);

        return response()->json(['message' => 'Profile image deleted.'], HttpResponse::HTTP_NO_CONTENT);
    }
}
