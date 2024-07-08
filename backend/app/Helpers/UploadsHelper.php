<?php
namespace App\Helpers;

use Illuminate\Support\Facades\URL;

class UploadsHelper{
	
    public static function uploadImage($file, $pathRelative  = '') {
        $path = UploadsHelper::handleMkdirAbsolute($pathRelative);
        $fileName = UploadsHelper::getFileName($file, $path);
        // Upload
        $result = $file->move($path, $fileName);
        return [
            'path' => $result->getPathName(),
            'url' => URL::route('home').'/'.$pathRelative.'/'.$fileName
        ];
    }

    public static function handleMkdirAbsolute($path) {
        $pathArr = explode('/', $path);
        $pathTmp = '';
        foreach ($pathArr as $key => $folderName) {
            if ($key == 0) {
                $pathTmp .= $folderName;
            } else {
                $pathTmp .= '/'.$folderName;
            }
            if (!file_exists(public_path($pathTmp))) {
                mkdir(public_path($pathTmp));
            }
        }
        return public_path($pathTmp);
    }
    public static function getFileName($file, $path) {
        $ext = $file->extension();
        $fileName = str_replace('.'.$ext, '', $file->getClientOriginalName());
        if (file_exists($path.'/'.$file->getClientOriginalName())) {
            return $fileName.'-'.time().'.'.$ext;
        } else {
            return $file->getClientOriginalName();
        }
    }
}