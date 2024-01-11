<?php

namespace lu\pata\jain;

class BufferTool{
    public function getBufferDetails($bufferItem,$format){
        $r = "ID: ".$bufferItem["id"]."\r\n";
        $r .= "Name: ".$bufferItem["name"]."\r\n";
        $r .= "Size: ".strlen($bufferItem["content"])."\r\n";
        if($format=="h")
            $r .= bin2hex($bufferItem["content"])."\r\n";
        else
            $r .= base64_encode($bufferItem["content"])."\r\n";
        return $r;
    }
}