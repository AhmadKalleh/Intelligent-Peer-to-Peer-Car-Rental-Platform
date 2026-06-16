<?php

namespace App\Traits\Upload;


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

        $filename = $file->getClientOriginalName();
        $path = $file->storeAs($folderName, $filename, 'public');

        return $path;

    }

}
