<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PhotoStorage
{
    public function storeMany(Model $imageable, array $files, string $folder, string $category): void
    {
        foreach ($files as $file) {
            $this->store($imageable, $file, $folder, $category);
        }
    }

    public function store(Model $imageable, UploadedFile $file, string $folder, string $category): void
    {
        $directory = public_path('photo/'.$folder);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = now()->format('YmdHis').'-'.Str::random(8).'.'.$file->extension();
        $file->move($directory, $filename);

        $imageable->photos()->create([
            'path' => 'photo/'.$folder.'/'.$filename,
            'category' => $category,
        ]);
    }
}
