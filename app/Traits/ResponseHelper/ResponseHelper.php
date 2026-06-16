<?php

namespace App\Traits\ResponseHelper;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ResponseHelper
{
    public function Success($data = null,$message=null,$code =200) :JsonResponse
    {
        $array =
        [
            "data" =>$data,
            "message" =>$message,
            "status"=>$code
        ];

        return response()->json($array,$code);
    }

    public function Error($data = null,$message=null,$code =500) :JsonResponse
    {
        $array =
        [
            "data" =>$data,
            "message" =>$message,
            "status"=>$code
        ];

        return response()->json($array,$code);
    }

    public function Validation($data = null,$message=null,$code =422) :JsonResponse
    {
        $array =
        [
            "data" =>$data,
            "message" =>$message,
            "status"=>$code
        ];

        return response()->json($array,$code);
    }
}
