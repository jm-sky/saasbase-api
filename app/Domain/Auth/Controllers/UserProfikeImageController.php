<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        return response()->json([
            'message'     => 'Profile image uploaded successfully.',
            'originalUrl' => $user->getFirstMediaUrl('profile'),
            'thumbUrl'    => $user->getFirstMediaUrl('profile', 'thumb'),
        ]);
    }

    public function show(Request $request)
    {
        $user  = $request->user();
        $media = $user->getFirstMedia('profile');

        if (!$media) {
            return response()->json(['message' => 'No profile image found.'], 404);
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

        return response()->json(['message' => 'Profile image deleted.']);
    }
}
