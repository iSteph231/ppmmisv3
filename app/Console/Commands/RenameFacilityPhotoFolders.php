<?php

namespace App\Console\Commands;

use App\Models\FacilityRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RenameFacilityPhotoFolders extends Command
{
    protected $signature = 'facility-requests:rename-photo-folders';

    protected $description = 'Move facility photos into requester and facility folders and update their saved paths';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $count = 0;

        foreach (FacilityRequest::with('user')->where(function ($query): void {
            $query->whereNotNull('before_photo_path')->orWhereNotNull('after_photo_path');
        })->get() as $facilityRequest) {
            $oldPaths = [];
            DB::transaction(function () use ($facilityRequest, $disk, &$oldPaths): void {
                $facilityRequest = FacilityRequest::whereKey($facilityRequest->id)->lockForUpdate()->firstOrFail();
                $oldDirectory = 'facility-requests/'.$facilityRequest->id;
                foreach (['before_photo_path', 'after_photo_path'] as $field) {
                    $oldPath = $facilityRequest->{$field};
                    if (! $oldPath || dirname($oldPath) !== $oldDirectory) {
                        continue;
                    }
                    if (! $disk->exists($oldPath)) {
                        throw new \RuntimeException('Missing photo: '.$oldPath);
                    }
                    $newPath = $facilityRequest->photoDirectory().'/'.basename($oldPath);
                    if ($disk->exists($newPath) || ! $disk->copy($oldPath, $newPath)) {
                        throw new \RuntimeException('Cannot safely move photo to '.$newPath);
                    }
                    $facilityRequest->{$field} = $newPath;
                    $oldPaths[] = $oldPath;
                }
                $facilityRequest->save();
            });

            foreach ($oldPaths as $oldPath) {
                $disk->delete($oldPath);
                $count++;
            }
            $oldDirectory = 'facility-requests/'.$facilityRequest->id;
            if ($disk->directoryExists($oldDirectory) && $disk->allFiles($oldDirectory) === []) {
                $disk->deleteDirectory($oldDirectory);
            }
        }

        $this->info('Moved '.$count.' photos into requester and facility folders.');

        return self::SUCCESS;
    }
}
