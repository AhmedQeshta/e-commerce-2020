<?php

use App\Models\Language;
use Illuminate\Support\Facades\Config;

function get_languages(){

    return Language::active()->Selection()->get();
}

function get_default_lang(){
  return   Config::get('app.locale');
}

//don't use use in controller.php from folder controller
//function uploadImage($folder, $image)
//{
//    $image->store('/', $folder);
//    $filename = $image->hashName();
//    $path = 'images/' . $folder . '/' . $filename;
//    return $path;
//}



function uploadVideo($folder, $video)
{
    $video->store('/', $folder);
    $filename = $video->hashName();
    $path = 'video/' . $folder . '/' . $filename;
    return $path;
}


