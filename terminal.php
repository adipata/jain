<?php
require_once 'common.php';

use JsonRPC\Server;
use lu\pata\jain\Db;
use lu\pata\jain\KeyTool;

$server = new Server();
$server->getProcedureHandler()
    ->withCallback('help', function () {
        return file_get_contents('./help.txt', true);;
    })
    ->withCallback('login', function ($user,$password) {
        $db=new Db();
        return $db->verifyUser($user, $password);
    })
    ->withCallback('load', function ($token,$name,$alg,$data,$type="secret") {
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        $bindata=hex2bin(str_replace("\n","",$data));
        if(!$bindata) throw new Exception("Invalid input data. Must be HEX ASCII.");

        $ktool=new KeyTool();
        $keyDetails=$ktool->load($alg,$type,$bindata);

        $db->addKey($uid, $name,$alg,$type,$keyDetails["bits"], $bindata);
        return "Ok";
    })
    ->withCallback('list', function ($token) {
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        return $db->getTableKeys($uid);
    })
    ->withCallback('gen', function ($token,$name,$alg,$size) {
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        $ktool=new KeyTool();
        switch ($alg){
            case "aes":
                $aes=$ktool->generate($alg,$size);
                $db->addKey($uid, $name,$alg,"secret",$size, $aes);
                break;
            case "rsa":
                $rsa=$ktool->generate($alg,$size);
                $db->addKey($uid, $name."_pub",$alg,"pub",$size, $rsa["pubKey"]);
                $db->addKey($uid, $name."_priv",$alg,"priv",$size, $rsa["privKey"]);
                break;
            default:

                throw new Exception("Unknown algorithm '$alg'.");
        }
        return "Ok";
    })
    ->withCallback('get', function ($token,$name) {
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        $key=$db->getKey($uid,$name);
        $ktool=new KeyTool();
        return $ktool->getKeyDetails($key);
    })
    ->withCallback('delete', function ($token,$name) {
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        $db->deleteKey($uid,$name);
        return "Ok";
    })
    ->withCallback('enc', function ($token,$keyname,$data,$opt="",$iv="") {
        $data=hex2bin(str_replace("\n","",$data));
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        $key=$db->getKey($uid,$keyname);
        $ktool=new KeyTool();
        return $ktool->encrypt($key,$data,$opt,$iv);
    })
    ->withCallback('dec', function ($token,$keyname,$data,$opt="",$iv="",$pad="pad") {
        $data=hex2bin(str_replace("\n","",$data));
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        $ktool=new KeyTool();
        return $ktool->decrypt($db,$uid,$keyname,$data,$opt,$iv,$pad);
    })
    ->withCallback('b2h', function ($token,$in) {
        $in=str_replace("\n","",$in);
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        return bin2hex(base64_decode($in));
    })
    ->withCallback('h2b', function ($token,$in) {
        $in=str_replace("\n","",$in);
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        return base64_encode(hex2bin($in));
    })
    ->withCallback('methods', function($token){
        $db = new Db();
        $uid = $db->getUserIdByToken($token);
        $ktool=new KeyTool();
        return $ktool->getMethods();
    })
;

echo $server->execute();
