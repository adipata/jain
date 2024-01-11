<?php


namespace lu\pata\jain;
use Exception;
use mysqli;
use dekor\ArrayToTextTable;

class Db
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "jain", "jain","jain");

        if ($this->conn->connect_error) {
            throw new Exception("DB connection failed: " . $this->conn->connect_error);
        }
    }

    public function verifyUser($user,$password) {
        $sql="select * from users where name='".addslashes($user)."' and password='".md5(addslashes($password))."'";
        $result = $this->conn->query($sql);
        if($result) {
            $row = $result->fetch_assoc();
            if($row) {
                $token = generateRandomString();
                $this->conn->query("update users set token='$token' where id=" . $row["id"]);
                return $token;
            }
        }
        return "";
    }

    public function getUserIdByToken($token){
        $result=$this->conn->query("select * from users where token='$token'");
        if($result){
            $row = $result->fetch_assoc();
            if($row) {
                return $row["id"];
            }
        }
        throw new Exception("Access token is not valid. Please 'exit' and 'login' again.");
    }

    public function addKey($uid,$name,$alg,$type,$size,$data){
        $sql="insert into `keys` (`user_id`,`name`,`alg`,`type`,`size`,`data`) values ($uid,'$name','$alg','$type',$size,'".addslashes($data)."')";
        if(!$this->conn->query($sql)){
            throw new Exception($this->conn->error);
        }
    }

    public function addBuffer($content,$uid,$name){
        $sql="insert into buffer (content,user_id,name) values ('$content',$uid,'$name')";
        if(!$this->conn->query($sql)){
            throw new Exception($this->conn->error);
        }
    }

    public function getTableKeys($uid){
        $result=$this->conn->query("select * from `keys` where user_id=$uid order by id asc");
        if($result){
            $data=array();
            while($row = $result->fetch_assoc()){
                $data[]=array("Name"=>$row["name"],"Algorithm"=>$row["alg"],"Key type"=>$row["type"],"Key size"=>$row["size"], "Data size"=>strlen($row["data"]),"Creation date"=>$row["cdate"]);
            }
            return (new ArrayToTextTable($data))->render();
        } else {
            throw new Exception($this->conn->error);
        }
    }

    public function getBuffer($uid){
        $result=$this->conn->query("select * from `buffer` where user_id=$uid order by id asc");
        if($result){
            $data=array();
            while($row = $result->fetch_assoc()){
                $data[]=array("Id"=>$row["id"],"Name"=>$row["name"], "Data size"=>strlen($row["content"]));
            }
            return (new ArrayToTextTable($data))->render()."\r\n";
        } else {
            throw new Exception($this->conn->error);
        }
    }

    public function getKey($uid,$name){
        $result=$this->conn->query("select * from `keys` where name='$name' and user_id=$uid");
        if($result){
            $row=$result->fetch_assoc();
            if($row){
                return $row;
            } else {
                throw new Exception("Key '$name' not found.");
            }
        } else {
            throw new Exception($this->conn->error);
        }
    }

    public function getBufferItem($uid,$id){
        $result=$this->conn->query("select * from `buffer` where id=$id and user_id=$uid");
        if($result){
            $row=$result->fetch_assoc();
            if($row){
                return $row;
            } else {
                throw new Exception("Buffer '$id' not found.\r\n");
            }
        } else {
            throw new Exception($this->conn->error);
        }
    }

    public function deleteKey($uid,$name){
        if($name!="all")
            $sql="delete from `keys` where name='$name' and user_id=$uid";
        else
            $sql="delete from `keys` where user_id=$uid";
        $this->conn->query($sql);
    }
}