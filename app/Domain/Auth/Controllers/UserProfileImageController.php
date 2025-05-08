<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserProfileImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        $user->clearMediaCollection('profile');

        $user->addMediaFromRequest('image')
            ->toMediaCollection('profile')
        ;

        $user->update([
            'avatar_url' => route('user.profile-image.show', absolute: false),
        ]);

        return response()->json([
            'message'     => 'Profile image uploaded successfully.',
            'originalUrl' => route('user.profile-image.show', absolute: false),
            'thumbUrl'    => route('user.profile-image.show', ['thumb' => true], absolute: false),
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
            return response()->json(['message' => 'No profile image found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'originalUrl' => $media->getUrl(),
            'thumbUrl'    => $media->getUrl('thumb'),
            'name'        => $media->file_name,
            'size'        => $media->size,
            'mimeType'    => $media->mime_type,
        ]);
    }

    public function delete(Request $request)
    {
        $user = $request->user();
        $user->clearMediaCollection('profile');

        return response()->json(['message' => 'Profile image deleted.'], Response::HTTP_NO_CONTENT);
    }
}
