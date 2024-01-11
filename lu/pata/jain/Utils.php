<?php


namespace lu\pata\jain;

class Utils{
    public function guidv4($type,$data = null) {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        $f='%s%s-%s-%s-%s-%s%s%s';
        if($type=="h") $f='0x%s%s%s%s%s%s%s%s';

        // Output the 36 character UUID.
        return vsprintf($f, str_split(bin2hex($data), 4));
    }

}