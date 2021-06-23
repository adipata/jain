<?php
require_once 'common.php';

use lu\pata\jain\Db;

if ($_FILES["input_file"]["size"] > 65535){
    exit ("Could not store buffer, maximum 64Kb. of data is accepted.");
}

$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

$db = new Db();
$db->addBuffer(addslashes(file_get_contents($_FILES['input_file']['tmp_name'])),$db->getUserIdByToken($_POST["token_form"]),$_FILES["input_file"]["name"]);
echo "Buffer created from file ".$_FILES["input_file"]["name"];

