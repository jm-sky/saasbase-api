<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Storage;

trait WithFakeStorage
{
    public function fakeStorage(string $disk): void
    {
        Storage::fake($disk);

        config()->set('media-library.disk_name', 'media');

        config()->set('filesystems.disks.media', [
            'driver' => 'local',
            'root'   => Storage::disk($disk)->path(''),
        ]);
    }
}
