<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Class FileService.
 */
class FileService
{
    public function upload($file, $type): string
    {
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = null;
        if ($type == 'image') {
            $file->move(public_path('uploads/images'), $filename);
            $path = 'uploads/images/' . $filename;
        }
        if ($type == 'file') {
            $file->move(public_path('uploads/files'), $filename);
            $path = 'uploads/files/' . $filename;
        }
        return $path;
    }

    public function delete($filename): void
    {
        File::delete($filename);
    }

    public function update($oldFilename, $newFile, $type): string
    {
        $this->delete($oldFilename);
        return $this->upload($newFile, $type);
    }
}
