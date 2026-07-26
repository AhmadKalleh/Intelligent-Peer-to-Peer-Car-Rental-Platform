<?php

namespace App\Traits\Upload;
use Illuminate\Support\Str;

trait UplodeImageHelper
{

    public function uploadImage($file, $folderName)
    {
        if (!$file) {
            throw new \Exception('File not found.');
        }

        // حساب hash
        $hash = hash_file('sha256', $file->getRealPath());

        // اسم الملف يعتمد على hash (اختياري وذكي 🔥)
        $filename = $hash . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs($folderName, $filename, 'public');

        return [
            'path' => $path,
            'hash' => $hash,
        ];
    }
}
