<?php


//$post_data =str_replace("mytext=","",file_get_contents("php://input"));


$post_data = $_POST['mytext'];

$post_data = json_decode($post_data,true);

print_r($post_data);

//define connection info
$server = "localhost";
$user = "root";
$pass = "";
$db = "thebox";

$link = mysqli_connect($server,$user,$pass,$db);
mysqli_set_charset($link,"utf8");

$command = "";


//get ad list
if($command == "get_ad_list"){


}

//new ad
if ($command == "new_ad"){

}

//upload image

if ($command == "upload_image"){

}

//bookmark ad
if ($command == "bookmark_ad"){


}

//send activation key
if ($command == "send_activation_key"){


}
//apply activation key
if ($command == "apply_activation_key"){


}


?>