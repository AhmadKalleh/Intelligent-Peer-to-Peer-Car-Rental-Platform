<?php

namespace App\Traits\Upload;
use Illuminate\Support\Str;

trait UplodeImageHelper
{

    public function uplodeImage($file,$folderName)
    {

        if (!$file) {
            return response()->json([
                'data' => [],
                'message' => 'File not found.',
                'status' => 400
            ]);
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs($folderName, $filename, 'public');

        return $path;

    }

}
