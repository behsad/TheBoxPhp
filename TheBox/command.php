<?php


//$post_data =str_replace("mytext=","",file_get_contents("php://input"));


$post_data = $_POST['mytext'];

$post_data = json_decode($post_data, true);

//print_r($post_data);

$command = $post_data['command'];


//define connection info
$server = "localhost";
$user = "root";
$pass = "";
$db = "thebox";

$link = mysqli_connect($server, $user, $pass, $db);
mysqli_set_charset($link, "utf8");


//get ad list
if ($command == "get_ad_list") {

    exit();
}


//new ad
if ($command == "new_ad") {


    $date = new DateTime();
    $date->setTimezone(new DateTimeZone("Asia/Tehran"));
    $current_timestamp = $date->getTimestamp();

    $query = "INSERT INTO `ad` (`user_id`, `title`, `description`, `province`, `city`, `district`, `car_type`, `price_type`, `price`, `date`, `image1`, `image2`, `image3`, `status`)
              VALUES ('" . $post_data['user_id'] . "', '" . $post_data['title'] . "', '" . $post_data['description'] . "', '" . $post_data['province'] . "', '" . $post_data['city'] . "', '" . $post_data['district'] . "', '" . $post_data['car_type'] . "', '" . $post_data['price_type'] . "', '" . $post_data['price'] . "', '$current_timestamp', '" . $post_data['image1'] . "', '" . $post_data['image2'] . "', '" . $post_data['image3'] . "', 0)";

    //check if add

    if (mysqli_query($link, $query)) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($link);

    }
    exit();
}

//upload image
if ($command == "upload_image") {

    $image_name = rand(1000000000, 99999999999);
    //find temp image
    $image_location = $_FILES['image']['tmp_name'];
    //save temp image nad check
    if (move_uploaded_file($image_location, "images/" . $image_name . ".png")) {
        echo "image uploaded";
    } else {
        echo "Error - image not uploaded";
    }


    exit();
}


//bookmark ad
if ($command == "bookmark_ad") {

    $user_id = $post_data['user_id'];
    $ad_id = $post_data['ad_id'];

    $query = "SELECT * FROM bookmark WHERE user_id =$user_id AND ad_id=$ad_id ";

    $result = mysqli_query($link, $query);
    $num = mysqli_num_rows($result);

    if ($num != 0) {
        //remove bookmark
        $query = "DELETE FROM bookmark WHERE user_id =$user_id AND ad_id=$ad_id ";
        mysqli_query($link, $query);
    } else {
        //insert bookmark
        $query = "INSERT INTO bookmark(user_id,ad_id)VALUES ($user_id,$ad_id)";
        mysqli_query($link, $query);


    }


    exit();
}

//send activation key
if ($command == "send_activation_key") {

    echo $activation_key = rand(1000, 9999);

    $query = "INSERT INTO activation (mobile,activation_key)VALUES ('" . $post_data['mobile'] . "','$activation_key')";
    mysqli_query($link, $query);

    exit();
}


//apply activation key
if ($command == "apply_activation_key") {

    $mobile = $post_data['mobile'];
    $email = $post_data['email'];
    $activation_key = $post_data['activation_key'];

    $query = "SELECT * FROM activation WHERE  mobile = '$mobile' AND activation_key = '$activation_key' ";
    $result = mysqli_query($link, $query);
    $num = mysqli_num_rows($result);


    //check activation key
    if ($num != 0) {//activation Ok


        //get user agent
        $agent = $_SERVER['HTTP_USER_AGENT'];

        //add user email and mobile number to user table
        $query = "INSERT INTO user (mobile,email,agent) VALUES ('$mobile','$email','$agent')";
        mysqli_query($link,$query);




    } else {//activation Error
        echo "activation key Error";
    }


    exit();
}


?>