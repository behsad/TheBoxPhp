<?php


$post_data = $_POST['myjson'];

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

    $location_filter = $post_data['location_filter'];
    $car_type_filter = $post_data['car_type_filter'];
    $last_ad_id = $post_data['last_ad_id'];

    $filter = "";
    if ($location_filter != 0) {
        $filter = "province=" . $location_filter;

    }

    if ($car_type_filter != 0) {
        if ($location_filter != 0) {
            $filter = $filter . " AND ";
        }
        $filter = $filter . "car_type=" . $car_type_filter;
    }

    if ($filter != "") {
        $filter = "WHERE " . $filter;
    }

    $filter2 = "";
    if ($last_ad_id != 0) {


        if ($filter != "") {
            $filter2 = " AND ";
        } else {
            $filter2 = " WHERE ";
        }
        $filter2 = $filter2 . "id<" . $last_ad_id;
    }

    //order ads by id in descending type and set limit number for showing the ads
    $query = "SELECT * FROM ad $filter $filter2 ORDER BY id DESC LIMIT 2 ";


    $result = mysqli_query($link, $query);

    $ad_list = array();

    while ($row = mysqli_fetch_assoc($result)) {

        $ad_list[] = $row;

    }

    echo "<thebox>".json_encode($ad_list)."</thebox>";

    exit();
}

//get my ad list
if ($command == "get_my_ad_list") {

    $user_id = $post_data['user_id'];
    $query = "SELECT * FROM ad WHERE user_id =$user_id";

    $result = mysqli_query($link, $query);

    $ad_list = array();

    while ($row = mysqli_fetch_assoc($result)) {

        $ad_list[] = $row;

    }

    echo "<thebox>".json_encode($ad_list)."</thebox>";


    exit();
}

//get bookmark ad list
if ($command == "get_bookmark_ad_list") {
    $user_id = $post_data['user_id'];

    $query = "SELECT * FROM bookmark WHERE user_id =$user_id";

    $result = mysqli_query($link, $query);

    $ad_list = array();


    while ($row = mysqli_fetch_assoc($result)) {

        $query2 = "SELECT * FROM ad WHERE id =" . $row['ad_id'];

        $result2 = mysqli_query($link, $query2);

        $row2 = mysqli_fetch_assoc($result2);

        $ad_list[] = $row2;

    }
    echo "<thebox>".json_encode($ad_list)."</thebox>";


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
        echo "<thebox>"."New record created successfully"."</thebox>";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($link);

    }
    exit();
}

//upload image
if (isset($_POST['image'])) {

    $image_name = rand(1000000000, 99999999999);
    $image_name = "images/" . $image_name . ".png";

    $imgsrc= base64_decode($_POST['image']);

    $fp = fopen($image_name,'w');
    fwrite($fp,$imgsrc);

    if(fclose($fp))
    {
        echo "<thebox>".$image_name."</thebox>";

    }else{
        echo "<thebox>"."0"."<thebox>";
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
    echo "<thebox>"."ok"."</thebox>";


    exit();
}

//send activation key
if ($command == "send_activation_key") {

    //delete old activation key
    $query = "DELETE FROM activation WHERE mobile ='" . $post_data['mobile'] . "'";
    mysqli_query($link, $query);


    //generate new activation key
    $activation_key = rand(1000, 9999);

    $query = "INSERT INTO activation (mobile,activation_key)VALUES ('" . $post_data['mobile'] . "','$activation_key')";
    mysqli_query($link, $query);

    echo "<thebox>"."activation ok"."</thebox>";

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

        //delete old activation key
        $query = "DELETE FROM activation WHERE mobile ='$mobile'";
        mysqli_query($link, $query);


        $query = "SELECT * FROM user WHERE  mobile = '$mobile'";
        $result = mysqli_query($link, $query);
        $num = mysqli_num_rows($result);

        //get user agent
        $agent = $_SERVER['HTTP_USER_AGENT'];


        if ($num == 0) {

            //add user email , mobile number and agent to user table
            $query = "INSERT INTO user (mobile,email,agent) VALUES ('$mobile','$email','$agent')";
            mysqli_query($link, $query);

            $user_id = mysqli_insert_id($link);

        } else {

            $row = mysqli_fetch_assoc($result);
            $user_id=$row['id'];

            //update user email and agent
            $query = "UPDATE user SET email = '$email',agent = '$agent' WHERE mobile = '$mobile'";
            mysqli_query($link, $query);

        }
        echo "<thebox>".$user_id."</thebox>";

    } else {//activation Error
        echo "<thebox>"."activation key Error"."</thebox>";
    }


    exit();
}


?>