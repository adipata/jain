<?php


namespace lu\pata\jain;


use Exception;

class KeyTool
{
    public function load($alg,$type,$kdata){
        switch ($alg){
            case "rsa":
                return $this->loadRSAKey($type,$kdata);
            case "aes":
                if(strlen($kdata)!=16 && strlen($kdata)!=32) throw new Exception("Incorrect AES key size ".strlen($kdata)." byte.");
                if($type!="secret") throw new Exception("Please provide 'secret' as type.");
                return array("bits"=>strlen($kdata)*8);
            default:
                throw new Exception("Unknown algorithm '$alg'.");
        }
    }

    public function generate($alg,$size){
        switch ($alg){
            case "aes":
                return $this->generateAES($size);
            case "rsa":
                return $this->generateRSA($size);
            default:
                throw new Exception("Unknown algorithm '$alg'.");
        }
    }

    public function getKeyDetails($key){
        switch($key["alg"]){
            case "aes":
                return $this->getKeyCommonDetails($key);
            case "rsa":
                return $this->getKeyCommonDetails($key)."\n".$this->getRSADetails($key);
            default:
                throw new Exception("Cannot get details for key with algorithm '".$key["alg"]."'.");
        }
    }

    public function encrypt($key,$data,$opt,$iv){
        switch($key["alg"]){
            case "rsa":
                switch($key["type"]){
                    case "pub":
                        return bin2hex($this->encryptRSAPublic($this->publicDerToPem($key["data"]),$data));
                    case "priv":
                        return bin2hex($this->encryptRSAPrivate($this->privateDerToPem($key["data"]),$data));
                    default:
                        throw new Exception("Unexpected key type '".$key["type"]."'.");
                }
            case "aes":
                return bin2hex($this->encryptAES($key["data"],$data,$opt,$iv));
            default:
                throw new Exception("Unknown encryption algorithm '".$key["alg"]."'.");
        }
    }

    public function decrypt($db,$uid,$keyname,$data,$opt,$iv,$pad){
        $key=$db->getKey($uid,$keyname);

        switch($key["alg"]){
            case "rsa":
                switch($key["type"]){
                    case "pub":
                        return bin2hex($this->decryptRSAPublic($this->publicDerToPem($key["data"]),$data,$pad));
                    case "priv":
                        return bin2hex($this->decryptRSAPrivate($this->privateDerToPem($key["data"]),$data,$pad));
                    default:
                        throw new Exception("Unexpected key type '".$key["type"]."'.");
                }
            case "aes":
                return bin2hex($this->decryptAES($key["data"],$data,$opt,$iv,$pad));
            default:
                throw new Exception("Unknown decryption algorithm '".$key["alg"]."'.");
        }
    }

    /** private zone -------------------------------------------------------------- **/

    private function encryptAES($kdata,$data,$opt,$iv){
        if($opt!="cbc" && $opt!="ecb") throw new Exception("Please provide 'cbc' or 'ecb' as option.");
        $size=strlen($kdata)*8;
        $iv=hex2bin($iv);
        $method="aes-".$size."-".$opt;
        $ivlen = openssl_cipher_iv_length($method);
        if($opt==="cbc" && strlen($iv)!=$ivlen) throw new Exception("Incorrect IV. Needs to have a length of $ivlen bytes.");

        $enc=openssl_encrypt($data,$method,$kdata, OPENSSL_RAW_DATA,$iv);
        if($enc){
            return $enc;
        } else {
            throw new Exception(openssl_error_string());
        }
    }

    private function decryptAES($kdata, $data,$opt,$iv,$pad){
        if($opt!="cbc" && $opt!="ecb") throw new Exception("Please provide 'cbc' or 'ecb' as option.");
        $size=strlen($kdata)*8;
        $iv=hex2bin($iv);
        $method="aes-".$size."-".$opt;
        $ivlen = openssl_cipher_iv_length($method);
        if($opt==="cbc" && strlen($iv)!=$ivlen) throw new Exception("Incorrect IV. Needs to have a length of $ivlen bytes.");

        $options=OPENSSL_RAW_DATA;
        if($pad==="nopad") $options=$options|OPENSSL_ZERO_PADDING;

        $dec=openssl_decrypt($data,$method,$kdata, $options,$iv);
        if($dec){
            return $dec;
        } else {
            throw new Exception(openssl_error_string());
        }
    }

    private function encryptRSAPublic($key,$data){
        if (openssl_public_encrypt($data, $encrypted, $key)){
            return $encrypted;
        } else {
            throw new Exception(openssl_error_string());
        }
    }

    private function encryptRSAPrivate($key,$data){
        if (openssl_private_encrypt($data, $encrypted, $key)){
            return $encrypted;
        } else {
            throw new Exception(openssl_error_string());
        }
    }

    private function decryptRSAPublic($key,$data,$pad){
        if($pad==="nopad")
            $pad=OPENSSL_NO_PADDING;
        else
            $pad=OPENSSL_PKCS1_PADDING;
        if (openssl_public_decrypt($data, $decrypted, $key,$pad)){
            return $decrypted;
        } else {
            throw new Exception(openssl_error_string());
        }
    }

    private function decryptRSAPrivate($key,$data,$pad){
        if($pad==="nopad")
            $pad=OPENSSL_NO_PADDING;
        else
            $pad=OPENSSL_PKCS1_PADDING;
        if (openssl_private_decrypt($data, $decrypted, $key,$pad)){
            return $decrypted;
        } else {
            throw new Exception(openssl_error_string());
        }
    }

    private function getKeyCommonDetails($key){
        return $key["alg"]." ".$key["size"]."\n".bin2hex($key["data"]);
    }

    private function getRSADetails($key){
        switch($key["type"]){
            case "pub":
                $rsaDetails=$this->loadRSAPublic($key["data"]);
                return "\n".$rsaDetails["key"]
                    ."\nn: ".bin2hex($rsaDetails["rsa"]["n"])
                    ."\ne: ".bin2hex($rsaDetails["rsa"]["e"])
                    ;
            case "priv":
                $rsaDetails=$this->loadRSAPrivate($key["data"]);
                return "\n".$rsaDetails["key"]
                    ."\nn: ".bin2hex($rsaDetails["rsa"]["n"])
                    ."\ne: ".bin2hex($rsaDetails["rsa"]["e"])
                    ."\nd: ".bin2hex($rsaDetails["rsa"]["d"])
                    ."\np: ".bin2hex($rsaDetails["rsa"]["p"])
                    ."\nq: ".bin2hex($rsaDetails["rsa"]["q"])
                    ."\ndmp1: ".bin2hex($rsaDetails["rsa"]["dmp1"])
                    ."\ndmq1: ".bin2hex($rsaDetails["rsa"]["dmq1"])
                    ."\niqmp: ".bin2hex($rsaDetails["rsa"]["iqmp"])
                    ;
            default:
                throw new Exception("Unexpected RSA key type '".$key["type"]."'.");
        }



    }

    private function generateAES($size){
        if($size!=128 && $size!=256) throw new Exception("Unsupported size for AES '$size'.");
        return openssl_random_pseudo_bytes($size/8);
    }

    private function generateRSA($size){
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => $size,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        $res = openssl_pkey_new($config);

        openssl_pkey_export($res, $privKey);

        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        return array("pubKey"=>$this->pemToDer($pubKey),"privKey"=>$this->pemToDer($privKey));
    }

    private function loadRSAKey($type,$kdata){
        switch($type){
            case "pub":
                return $this->loadRSAPublic($kdata);
            default:
                throw new Exception("Unknown key type '$type'. It can be 'pub' or 'priv'.");
        }
    }

    private function loadRSAPublic($kdata){
        $pub_key = openssl_pkey_get_public($this->publicDerToPem($kdata));
        if(!$pub_key) throw new Exception(openssl_error_string());
        return openssl_pkey_get_details($pub_key);
    }

    private function loadRSAPrivate($kdata){
        $priv_key = openssl_pkey_get_private($this->privateDerToPem($kdata));
        if(!$priv_key) throw new Exception(openssl_error_string());
        openssl_pkey_export($priv_key, $pKeyContent);
        $out=openssl_pkey_get_details($priv_key);
        $out["key"]=$pKeyContent;
        return $out;
    }

    private function publicDerToPem($kdata){
        $o="-----BEGIN PUBLIC KEY-----\n";
        $o.=base64_encode($kdata)."\n";
        $o.="-----END PUBLIC KEY-----\n";
        return $o;
    }

    private function privateDerToPem($kdata){
        $o="-----BEGIN PRIVATE KEY-----\n";
        $o.=base64_encode($kdata)."\n";
        $o.="-----END PRIVATE KEY-----\n";
        return $o;
    }

    private function pemToDer($pem){
        $pem=str_replace("-----BEGIN PUBLIC KEY-----","",$pem);
        $pem=str_replace("-----END PUBLIC KEY-----","",$pem);
        $pem=str_replace("-----BEGIN PRIVATE KEY-----","",$pem);
        $pem=str_replace("-----END PRIVATE KEY-----","",$pem);
        $pem=str_replace("\n","",$pem);
        return base64_decode($pem);
    }

}