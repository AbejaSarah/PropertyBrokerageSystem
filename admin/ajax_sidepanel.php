<?php
/*
Copyright (c) 2015-2020 Devendra Katariya (bylancer.com)
Version 1.0
*/
require_once('../includes/config.php');
require_once('../includes/lib/HTMLPurifier/HTMLPurifier.standalone.php');
require_once('../includes/sql_builder/idiorm.php');
require_once('../includes/db.php');
require_once('../includes/classes/class.template_engine.php');
require_once('../includes/classes/class.country.php');
require_once('../includes/functions/func.global.php');
require_once('../includes/functions/func.admin.php');
require_once('../includes/functions/func.sqlquery.php');
require_once('../includes/functions/func.users.php');
require_once('../includes/classes/GoogleTranslate.php');
require_once('../includes/lang/lang_'.$config['lang'].'.php');

admin_session_start();
checkloggedadmin();

if (!isset($_SESSION['admin']['id'])) {
    echo $json = '{"status" : "error","message" : "Access Denied. Please login again."}';
    die();
}

require_once('../includes/seo-url.php');

//SidePanel Ajax Function
if(isset($_GET['action'])){
    if(!check_allow()){
        $message = "permission denied for demo.";
        echo $json = '{"status" : "error","message" : "' . $message . '"}';
        die();
    }

    if ($_GET['action'] == "addAdmin") { addAdmin(); }
    if ($_GET['action'] == "editAdmin") { editAdmin(); }
    if ($_GET['action'] == "addUser") { addUser(); }
    if ($_GET['action'] == "editUser") { editUser(); }

    if ($_GET['action'] == "addCountry") { addCountry(); }
    if ($_GET['action'] == "editCountry") { editCountry(); }
    if ($_GET['action'] == "addState") { addState(); }
    if ($_GET['action'] == "editState") { editState(); }
    if ($_GET['action'] == "addDistrict") { addDistrict(); }
    if ($_GET['action'] == "editDistrict") { editDistrict(); }
    if ($_GET['action'] == "addCity") { addCity(); }
    if ($_GET['action'] == "editCity") { editCity(); }

    if ($_GET['action'] == "addCurrency") { addCurrency(); }
    if ($_GET['action'] == "editCurrency") { editCurrency(); }
    if ($_GET['action'] == "addTimezone") { addTimezone(); }
    if ($_GET['action'] == "editTimezone") { editTimezone(); }
    if ($_GET['action'] == "addLanguage") { addLanguage(); }
    if ($_GET['action'] == "editLanguage") { editLanguage(); }

    if ($_GET['action'] == "addMembershipPlan") { addMembershipPlan(); }
    if ($_GET['action'] == "editMembershipPlan") { editMembershipPlan(); }
    if ($_GET['action'] == "addMembershipPackage") { addMembershipPackage(); }
    if ($_GET['action'] == "editMembershipPackage") { editMembershipPackage(); }

    if ($_GET['action'] == "addStaticPage") { addStaticPage(); }
    if ($_GET['action'] == "editStaticPage") { editStaticPage(); }
    if ($_GET['action'] == "addFAQentry") { addFAQentry(); }
    if ($_GET['action'] == "editFAQentry") { editFAQentry(); }

    if ($_GET['action'] == "expirePostRenew") { expirePostRenew(); }
    if ($_GET['action'] == "postEdit") { postEdit(); }
    if ($_GET['action'] == "transactionEdit") { transactionEdit(); }
    if ($_GET['action'] == "editAdvertise") { editAdvertise(); }
    if ($_GET['action'] == "paymentEdit") { paymentEdit(); }

    if ($_GET['action'] == "SaveCustomFieldsSettings") { SaveCustomFieldsSettings(); }
    if ($_GET['action'] == "SaveSettings") { SaveSettings(); }
    if ($_GET['action'] == "saveEmailTemplate") { saveEmailTemplate(); }
    if ($_GET['action'] == "testEmailTemplate") { testEmailTemplate(); }

    if ($_GET['action'] == "addTestimonial") { addTestimonial(); }
    if ($_GET['action'] == "editTestimonial") { editTestimonial(); }
}



function change_config_file_settings($filePath, $newSettings,$lang)
{
    // Update $fileSettings with any new values
    $fileSettings = array_merge($lang, $newSettings);
    // Build the new file as a string
    $newFileStr = "<?php\n";
    foreach ($fileSettings as $name => $val) {
        // Using var_export() allows you to set complex values such as arrays and also
        // ensures types will be correct
        $newFileStr .= "\$lang['$name'] = " . var_export($val, true) . ";\n";
    }
    // Closing tag intentionally omitted, you can add one if you want

    // Write it back to the file
    file_put_contents($filePath, $newFileStr);

}

function addAdmin(){
    global $config,$lang;

    if (isset($_POST['submit'])) {

        $valid_formats = array("jpg","jpeg","png"); // Valid image formats

        if ($_FILES['file']['name'] != "") {

            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = '../storage/profile/';
                $original_filename = $_FILES['file']['name'];
                $random1 = rand(9999, 100000);
                $random2 = rand(9999, 200000);
                $random3 = $random1 . $random2;
                $extensions = explode(".", $original_filename);
                $extension = $extensions[count($extensions) - 1];
                $uniqueName = $random3 . "." . $extension;
                $uploadfile = $uploaddir . $uniqueName;

                $file_type = "file";

                if ($extension == "jpg" || $extension == "jpeg" || $extension == "gif" || $extension == "png") {
                    $file_type = "image";

                    $size = filesize($_FILES['file']['tmp_name']);

                    $image = $_FILES["file"]["name"];
                    $uploadedfile = $_FILES['file']['tmp_name'];

                    if ($image) {
                        if ($extension == "jpg" || $extension == "jpeg") {
                            $uploadedfile = $_FILES['file']['tmp_name'];
                            $src = imagecreatefromjpeg($uploadedfile);
                        } else if ($extension == "png") {
                            $uploadedfile = $_FILES['file']['tmp_name'];
                            $src = imagecreatefrompng($uploadedfile);
                        } else {
                            $src = imagecreatefromgif($uploadedfile);
                        }

                        list($width, $height) = getimagesize($uploadedfile);

                        $newwidth = 225;
                        $newheight = 225;
                        //$newheight = ($height / $width) * $newwidth;
                        $tmp = imagecreatetruecolor($newwidth, $newheight);

                        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                        $filename = $uploaddir . "small" . $uniqueName;

                        imagejpeg($tmp, $filename, 100);

                        imagedestroy($src);
                        imagedestroy($tmp);
                    }


                }
                //else if it's not bigger then 0, then it's available '
                //and we send 1 to the ajax request
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                    //$time = date('Y-m-d H:i:s', time());
                    $password = escape_html($_POST["password"]);
                    $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);

                    $admins = ORM::for_table($config['db']['pre'].'admins')->create();
                    $admins->username = escape_html($_POST['username']);
                    $admins->password_hash = $pass_hash;
                    $admins->name = escape_html($_POST['name']);
                    $admins->email = escape_html($_POST['email']);
                    $admins->image = escape_html($uniqueName);
                    $admins->save();

                    if ($admins->id()) {
                        $status = "success";
                        $message = $lang['SAVED_SUCCESS'];
                    } else{
                        $status = "error";
                        $message = $lang['ERROR_TRY_AGAIN'];
                    }
                }
            }
            else {
                $error = "Only allowed jpg, jpeg png";
                $status = "error";
                $message = $error;
            }

        } else {
            $error = "Profile Picture Required";
            $status = "error";
            $message = $error;
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}
function editAdmin(){
    global $config,$lang;

    if (isset($_POST['id'])) {
        $password = escape_html($_POST["newPassword"]);

        if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != "")
        {
            $valid_formats = array("jpg","jpeg","png"); // Valid image formats
            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = '../storage/profile/';
                $original_filename = $_FILES['file']['name'];
                $random1 = rand(9999,100000);
                $random2 = rand(9999,200000);
                $random3 = $random1.$random2;
                $extensions = explode(".", $original_filename);
                $extension = $extensions[count($extensions) - 1];
                $uniqueName =  $random3 . "." . $extension;
                $uploadfile = $uploaddir . $uniqueName;

                $file_type = "file";

                if ($extension == "jpg" || $extension == "jpeg" || $extension == "gif" || $extension == "png") {
                    $file_type = "image";

                    $size = filesize($_FILES['file']['tmp_name']);

                    $image = $_FILES["file"]["name"];
                    $uploadedfile = $_FILES['file']['tmp_name'];

                    if ($image) {
                        if ($extension == "jpg" || $extension == "jpeg") {
                            $uploadedfile = $_FILES['file']['tmp_name'];
                            $src = imagecreatefromjpeg($uploadedfile);
                        } else if ($extension == "png") {
                            $uploadedfile = $_FILES['file']['tmp_name'];
                            $src = imagecreatefrompng($uploadedfile);
                        } else {
                            $src = imagecreatefromgif($uploadedfile);
                        }

                        list($width, $height) = getimagesize($uploadedfile);

                        $newwidth = 225;
                        $newheight = 225;
                        //$newheight = ($height / $width) * $newwidth;
                        $tmp = imagecreatetruecolor($newwidth, $newheight);

                        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                        $filename = $uploaddir . "small" . $uniqueName;

                        imagejpeg($tmp, $filename, 100);

                        imagedestroy($src);
                        imagedestroy($tmp);
                    }


                }
                //else if it's not bigger then 0, then it's available '
                //and we send 1 to the ajax request
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {

                    $info = ORM::for_table($config['db']['pre'].'admins')
                        ->select('image')
                        ->find_one($_POST['id']);

                    if($info['image'] != "default_user.png"){
                        if(file_exists($uploaddir.$info['image'])){
                            unlink($uploaddir.$info['image']);
                            unlink($uploaddir."small".$info['image']);
                        }
                    }
                    if(!empty($password)){
                        $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);

                        $admins = ORM::for_table($config['db']['pre'].'admins')->find_one(escape_html($_POST['id']));
                        $admins->name = escape_html($_POST['name']);
                        $admins->password_hash = $pass_hash;
                        $admins->image = $uniqueName;
                        $admins->save();
                    }else{
                        $admins = ORM::for_table($config['db']['pre'].'admins')->find_one(escape_html($_POST['id']));
                        $admins->name = escape_html($_POST['name']);
                        $admins->image = $uniqueName;
                        $admins->save();
                    }

                    if (!$admins) {
                        $status = "error";
                        $message = $lang['ERROR_TRY_AGAIN'];
                    } else{
                        $status = "success";
                        $message = $lang['SAVED_SUCCESS'];
                    }
                }
            }
            else {
                $error = "Only allowed jpg, jpeg png";
                $status = "error";
                $message = $error;
            }

        }
        else{
            if(!empty($password)){
                $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);

                $admins = ORM::for_table($config['db']['pre'].'admins')->find_one(escape_html($_POST['id']));
                $admins->name = escape_html($_POST['name']);
                $admins->password_hash = $pass_hash;
                $admins->username = escape_html($_POST["username"]);
                $admins->save();

            }else{

                $admins = ORM::for_table($config['db']['pre'].'admins')->find_one(escape_html($_POST['id']));
                $admins->name = escape_html($_POST['name']);
                $admins->username = escape_html($_POST["username"]);
                $admins->save();
            }


            if (!$admins) {
                $status = "error";
                $message = $lang['ERROR_TRY_AGAIN'];
            } else{
                $status = "success";
                $message = $lang['SAVED_SUCCESS'];
            }
        }


    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addUser(){
    global $config,$lang;

    if (isset($_POST['submit'])) {

        $valid_formats = array("jpg","jpeg","png"); // Valid image formats

        if($_FILES['file']['name'] != "")
        {
            $valid_formats = array("jpg","jpeg","png"); // Valid image formats
            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = '../storage/profile/';
                $original_filename = $_FILES['file']['name'];
                $random1 = rand(9999,100000);
                $random2 = rand(9999,200000);
                $random3 = $random1.$random2;
                $username = escape_html($_POST['username']);
                $image_name = $username.'_'.$random1.$random2.'.'.$ext;
                $image_name1 = 'small_'.$username.'_'.$random1.$random2.'.'.$ext;

                $filename = $uploaddir . $image_name;
                $filename1 = $uploaddir . $image_name1;

                $uploadedfile = $_FILES['file']['tmp_name'];

                //else if it's not bigger then 0, then it's available '
                //and we send 1 to the ajax request
                if (resizeImage(500, $filename, $uploadedfile)) {
                    resize_crop_image(200, 200, $filename1, $uploadedfile);
                    //$time = date('Y-m-d H:i:s', time());
                    $password = escape_html($_POST["password"]);
                    $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
                    $now = date("Y-m-d H:i:s");

                    $insert_user = ORM::for_table($config['db']['pre'].'user')->create();
                    $insert_user->name = escape_html($_POST['name']);
                    $insert_user->username = escape_html($_POST['username']);
                    $insert_user->password_hash = $pass_hash;
                    $insert_user->email = escape_html($_POST['email']);
                    $insert_user->sex = escape_html($_POST['sex']);
                    $insert_user->description = escape_html($_POST['sex']);
                    $insert_user->country = escape_html($_POST['country']);
                    $insert_user->image = $image_name;
                    $insert_user->created_at = $now;
                    $insert_user->updated_at = $now;
                    $insert_user->save();

                    if ($insert_user->id()) {
                        $status = "success";
                        $message = $lang['SAVED_SUCCESS'];
                    } else{
                        $status = "error";
                        $message = $lang['ERROR_TRY_AGAIN'];
                    }
                }
            }
            else {
                $error = "Only allowed jpg, jpeg png";
                $status = "error";
                $message = $error;
            }

        } else {
            $error = "Profile Picture Required";
            $status = "error";
            $message = $error;
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}
function editUser(){
    global $config,$lang,$link;
    $pdo = ORM::get_db();
    if (isset($_POST['id'])) {
        $password = escape_html($_POST["password"]);

        if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != "")
        {
            $valid_formats = array("jpg","jpeg","png"); // Valid image formats
            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = '../storage/profile/';
                $original_filename = $_FILES['file']['name'];
                $random1 = rand(9999,100000);
                $random2 = rand(9999,200000);

                $image_name = $random1.$random2.'.'.$ext;
                $image_name1 = 'small_'.$random1.$random2.'.'.$ext;

                $filename = $uploaddir . $image_name;
                $filename1 = $uploaddir . $image_name1;

                $uploadedfile = $_FILES['file']['tmp_name'];

                //else if it's not bigger then 0, then it's available '
                //and we send 1 to the ajax request
                if (resizeImage(500, $filename, $uploadedfile)) {
                    resize_crop_image(200, 200, $filename1, $uploadedfile);

                    $info = ORM::for_table($config['db']['pre'].'user')
                        ->select('image')
                        ->find_one(escape_html($_POST['id']));

                    if($info['image'] != "default_user.png"){
                        if(file_exists($uploaddir.$info['image'])){
                            unlink($uploaddir.$info['image']);
                            unlink($uploaddir."small_".$info['image']);
                        }
                    }
                }
            }
            else {
                $error = "Only allowed jpg, jpeg png";
                $status = "error";
                $message = $error;
            }

        }

        $now = date("Y-m-d H:i:s");
        $group_id = escape_html($_POST['group_id']);
        $user_id = escape_html($_POST['id']);
        $user_update = ORM::for_table($config['db']['pre'].'user')->find_one(escape_html($_POST['id']));
        $user_update->set('group_id', $group_id);
        $user_update->set('name', escape_html($_POST['name']));
        $user_update->set('username', escape_html($_POST['username']));
        $user_update->set('email', escape_html($_POST['email']));
        $user_update->set('status', escape_html($_POST['status']));
        $user_update->set('description', escape_html($_POST['about']));
        $user_update->set('sex', escape_html($_POST['sex']));
        $user_update->set('country', escape_html($_POST['country']));
        if(isset($group_id)){
            $check_plan_exist = ORM::for_table($config['db']['pre'].'subscriptions')
                ->where('group_id', $group_id)
                ->count();
            if($check_plan_exist){
                $info = ORM::for_table($config['db']['pre'].'subscriptions')
                    ->where('group_id', $group_id)
                    ->find_one();

                $title = $info['sub_title']." ".$lang['MEMBERSHIPPLAN'];
                $amount = $info['sub_amount'];
                $term = $info['sub_term'];
                $payment_type = "subscr";

                $access_token = uniqid();
                $_SESSION['quickad'][$access_token]['name'] = $title;
                $_SESSION['quickad'][$access_token]['amount'] = $amount;
                $_SESSION['quickad'][$access_token]['payment_type'] = $payment_type;
                $_SESSION['quickad'][$access_token]['sub_id'] = $info['sub_id'];
                $_SESSION['quickad'][$access_token]['payment_mode'] = "Admin";
                $_SESSION['quickad'][$access_token]['folder'] = 'Admin';
                $folder = 'Admin';
                if($term == 'DAILY')
                {
                    $_SESSION['quickad'][$access_token]['plan_interval'] = "day";
                }
                elseif($term == 'WEEKLY')
                {
                    $_SESSION['quickad'][$access_token]['plan_interval'] = "week";
                }
                elseif($term == 'MONTHLY')
                {
                    $_SESSION['quickad'][$access_token]['plan_interval'] = "month";
                }
                elseif($term == 'YEARLY')
                {
                    $_SESSION['quickad'][$access_token]['plan_interval'] = "year";
                }


                $trans_desc = $title;
                $subcription_id = $_SESSION['quickad'][$access_token]['sub_id'];

                // Check that the payment is valid
                $subsc_details = ORM::for_table($config['db']['pre'].'subscriptions')
                    ->where('sub_id', $subcription_id)
                    ->find_one();
                if(!empty($subsc_details)){
                    // output data of each row

                    $term = 0;
                    if($subsc_details['sub_term'] == 'DAILY') {
                        $term = 86400;
                    }
                    elseif($subsc_details['sub_term'] == 'WEEKLY') {
                        $term = 604800;
                    }
                    elseif($subsc_details['sub_term'] == 'MONTHLY') {
                        $term = 2678400;
                    }
                    elseif($subsc_details['sub_term'] == 'YEARLY') {
                        $term = 31536000;
                    }

                    $sub_group_id = $subsc_details['group_id'];
                    $sub_amount = $subsc_details['sub_amount'];

                    $subsc_check = ORM::for_table($config['db']['pre'].'upgrades')
                        ->where('user_id', $user_id)
                        ->count();
                    if($subsc_check == 1)
                    {
                        $txn_type = 'subscr_update';
                    }
                    else
                    {
                        $txn_type = 'subscr_signup';
                    }

                    // Add time to their subscription
                    $expires = (time()+$term);

                    if($txn_type == 'subscr_update')
                    {

                        $query = "UPDATE `".$config['db']['pre']."upgrades` SET `sub_id` = '".escape_html($subcription_id)."',`upgrade_expires` = '".escape_html($expires)."' WHERE `user_id` = '".escape_html($user_id)."' LIMIT 1 ";
                        $pdo->query($query);

                        $person = ORM::for_table($config['db']['pre'].'user')->find_one($user_id);
                        $person->group_id = $sub_group_id;
                        $person->save();

                    }
                    elseif($txn_type == 'subscr_signup')
                    {
                        $unique_subscription_id = uniqid();
                        $subscription_status = "Active";

                        $subscription_stripe_customer_id = isset($_SESSION['quickad'][$access_token]['customer_id'])? $_SESSION['quickad'][$access_token]['customer_id'] : null;
                        $subscription_stripe_subscription_id = isset($_SESSION['quickad'][$access_token]['subscription_id'])? $_SESSION['quickad'][$access_token]['subscription_id'] : null;
                        $subscription_billing_day = isset($_SESSION['quickad'][$access_token]['billing_day'])? $_SESSION['quickad'][$access_token]['billing_day'] : null;
                        $subscription_length = 0;
                        $subscription_interval = isset($_SESSION['quickad'][$access_token]['interval'])? $_SESSION['quickad'][$access_token]['interval'] : null;
                        $subscription_trial_days = isset($_SESSION['quickad'][$access_token]['trial_days'])? $_SESSION['quickad'][$access_token]['trial_days'] : null;
                        $subscription_date_trial_ends = isset($_SESSION['quickad'][$access_token]['date_trial_ends'])? $_SESSION['quickad'][$access_token]['date_trial_ends'] : null;

                        $upgrades_insert = ORM::for_table($config['db']['pre'].'upgrades')->create();
                        $upgrades_insert->sub_id = $subcription_id;
                        $upgrades_insert->user_id = $user_id;
                        $upgrades_insert->upgrade_lasttime = time();
                        $upgrades_insert->upgrade_expires = $expires;
                        $upgrades_insert->unique_id = $unique_subscription_id;
                        $upgrades_insert->stripe_customer_id = $subscription_stripe_customer_id;
                        $upgrades_insert->stripe_subscription_id = $subscription_stripe_subscription_id;
                        $upgrades_insert->billing_day = $subscription_billing_day;
                        $upgrades_insert->length = $subscription_length;
                        $upgrades_insert->interval = $subscription_interval;
                        $upgrades_insert->trial_days = $subscription_trial_days;
                        $upgrades_insert->status = $subscription_status;
                        $upgrades_insert->date_trial_ends = $subscription_date_trial_ends;
                        $upgrades_insert->save();

                        $person = ORM::for_table($config['db']['pre'].'user')->find_one($user_id);
                        $person->group_id = $sub_group_id;
                        $person->save();
                    }

                    //Update Amount in balance table
                    $balance = ORM::for_table($config['db']['pre'].'balance')->find_one(1);
                    $current_amount=$balance['current_balance'];
                    $total_earning=$balance['total_earning'];

                    $updated_amount=($sub_amount+$current_amount);
                    $total_earning=($sub_amount+$total_earning);

                    $balance->current_balance = $updated_amount;
                    $balance->total_earning = $total_earning;
                    $balance->save();

                    $ip = encode_ip($_SERVER, $_ENV);
                    $trans_insert = ORM::for_table($config['db']['pre'].'transaction')->create();
                    $trans_insert->product_name = $title;
                    $trans_insert->product_id = $subcription_id;
                    $trans_insert->seller_id = $user_id;
                    $trans_insert->status = 'success';
                    $trans_insert->amount = $amount;
                    $trans_insert->transaction_gatway = $folder;
                    $trans_insert->transaction_ip = $ip;
                    $trans_insert->transaction_time = time();
                    $trans_insert->transaction_description = $trans_desc;
                    $trans_insert->transaction_method = 'Subscription';
                    $trans_insert->save();

                    unset($_SESSION['quickad'][$access_token]);
                }
                else{
                    unset($_SESSION['quickad'][$access_token]);
                    $status = "error";
                    $message = $lang['INVALID_TRANSACTION'];
                }
            }
        }

        if(!empty($password)){
            $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
            $user_update->set('password_hash', $pass_hash);
        }

        if(isset($image_name)){
            $user_update->set('image', $image_name);
        }
        $user_update->set('updated_at', $now);
        $user_update->save();

        if ($user_update) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addCountry(){
    global $config,$lang;

    if (isset($_POST['submit'])) {

        $insert_country = ORM::for_table($config['db']['pre'].'countries')->create();
        $insert_country->code = escape_html($_POST['code']);
        $insert_country->name = escape_html($_POST['name']);
        $insert_country->asciiname = escape_html($_POST['asciiname']);
        $insert_country->currency_code = escape_html($_POST['currency_code']);
        $insert_country->phone = escape_html($_POST['phone']);
        $insert_country->languages = escape_html($_POST['languages']);
        $insert_country->save();

        if ($insert_country->id()) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}
function editCountry(){
    global $config,$lang;

    if (isset($_POST['code'])) {

        $info = ORM::for_table($config['db']['pre'].'countries')
            ->select('id')
            ->where('code', escape_html($_POST['code']))
            ->find_one();

        $update_country = ORM::for_table($config['db']['pre'].'countries')->find_one($info['id']);
        $update_country->set('name', escape_html($_POST['name']));
        $update_country->set('code', escape_html($_POST['code']));
        $update_country->set('asciiname', escape_html($_POST['asciiname']));
        $update_country->set('currency_code', escape_html($_POST['currency_code']));
        $update_country->set('phone', escape_html($_POST['phone']));
        $update_country->set('languages', escape_html($_POST['languages']));
        $update_country->save();

        if ($update_country) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addState(){
    global $config,$lang;

    if (isset($_POST['code'])) {
        $info = ORM::for_table($config['db']['pre'].'subadmin1')
            ->select('code')
            ->where('country_code', escape_html($_POST['code']))
            ->order_by_desc('code')
            ->find_one();

        $count = count($info);
        if($count > 0){
            $check = substr($info['code'],3);
            $code =  escape_html($_POST['code']).".".($check+1);
        }else{
            $code =  escape_html($_POST['code']).".1";
        }

        $active = isset($_POST['active']) ? '1' : '0';

        $insert_subadmin1 = ORM::for_table($config['db']['pre'].'subadmin1')->create();
        $insert_subadmin1->code = $code;
        $insert_subadmin1->country_code =  escape_html($_POST['code']);
        $insert_subadmin1->name = escape_html($_POST['name']);
        $insert_subadmin1->asciiname = escape_html($_POST['asciiname']);
        $insert_subadmin1->active = $active;
        $insert_subadmin1->save();

        if ($insert_subadmin1->id()) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}
function editState(){
    global $config,$lang;

    if (isset($_POST['code'])) {
        $active = isset($_POST['active']) ? '1' : '0';

        $info = ORM::for_table($config['db']['pre'].'subadmin1')
            ->select('id')
            ->where('code',  escape_html($_POST['code']))
            ->find_one();

        $update_subadmin1 = ORM::for_table($config['db']['pre'].'subadmin1')->find_one($info['id']);
        $update_subadmin1->set('name', escape_html($_POST['name']));
        $update_subadmin1->set('asciiname', escape_html($_POST['asciiname']));
        $update_subadmin1->set('active', $active);
        $update_subadmin1->save();

        if ($update_subadmin1) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addDistrict(){
    global $config,$lang;

    if (isset($_POST['code'])) {
        $info = ORM::for_table($config['db']['pre'].'subadmin2')
            ->select_many('code','country_code','subadmin1_code')
            ->where('subadmin1_code', escape_html($_POST['code']))
            ->order_by_desc('code')
            ->find_one();

        $count = count($info);
        if($count > 0){
            $country = $info['country_code'];
            $subadmin1 = $info['subadmin1_code'];

            $code = $info['code'];
            $pieces = explode(".", $code);
            $code_count = count($pieces);
            if($code_count == 3){
                $subadmin2 = $pieces[2]+1;
            }
            $code = escape_html($_POST['code']).".".$subadmin2;


        }else{
            $code = escape_html($_POST['code']).".1";

            $subadmin1 = escape_html($_POST['code']);
            $pieces = explode(".", $subadmin1);
            $country = $pieces[0];
        }

        $active = isset($_POST['active']) ? '1' : '0';

        $insert_subadmin2 = ORM::for_table($config['db']['pre'].'subadmin2')->create();
        $insert_subadmin2->name = escape_html($_POST['name']);
        $insert_subadmin2->asciiname = escape_html($_POST['asciiname']);
        $insert_subadmin2->code = $code;
        $insert_subadmin2->country_code = $country;
        $insert_subadmin2->subadmin1_code = $subadmin1;
        $insert_subadmin2->active = $active;
        $insert_subadmin2->save();

        if ($insert_subadmin2->id()) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}
function editDistrict(){
    global $config,$lang;

    if (isset($_POST['code'])) {
        $active = isset($_POST['active']) ? '1' : '0';

        $info = ORM::for_table($config['db']['pre'].'subadmin2')
            ->select('id')
            ->where('code', escape_html($_POST['code']))
            ->find_one();

        $update_subadmin2 = ORM::for_table($config['db']['pre'].'subadmin2')->find_one($info['id']);
        $update_subadmin2->set('name', escape_html($_POST['name']));
        $update_subadmin2->set('asciiname', escape_html($_POST['asciiname']));
        $update_subadmin2->set('active', $active);
        $update_subadmin2->save();

        if ($update_subadmin2) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addCity(){
    global $config,$lang;

    if (isset($_POST['submit'])) {
        $active = isset($_POST['active']) ? '1' : '0';

        $insert_city = ORM::for_table($config['db']['pre'].'cities')->create();
        $insert_city->name = escape_html($_POST['name']);
        $insert_city->asciiname = escape_html($_POST['asciiname']);
        $insert_city->country_code = escape_html($_POST['country_code']);
        $insert_city->subadmin1_code = escape_html($_POST['subadmin1_code']);
        $insert_city->subadmin2_code = escape_html($_POST['subadmin2_code']);
        $insert_city->longitude = escape_html($_POST['longitude']);
        $insert_city->latitude = escape_html($_POST['latitude']);
        $insert_city->population = escape_html($_POST['population']);
        $insert_city->time_zone = escape_html($_POST['time_zone']);
        $insert_city->active = $active;
        $insert_city->save();

        if ($insert_city->id()) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}
function editCity(){
    global $config,$lang;

    if (isset($_POST['id'])) {
        $active = isset($_POST['active']) ? '1' : '0';

        $update_city = ORM::for_table($config['db']['pre'].'cities')->find_one(escape_html($_POST['id']));
        $update_city->set('name', escape_html($_POST['name']));
        $update_city->set('asciiname', escape_html($_POST['asciiname']));
        $update_city->set('country_code', escape_html($_POST['country_code']));
        $update_city->set('subadmin1_code', escape_html($_POST['subadmin1_code']));
        $update_city->set('subadmin2_code', escape_html($_POST['subadmin2_code']));
        $update_city->set('longitude', escape_html($_POST['longitude']));
        $update_city->set('latitude', escape_html($_POST['latitude']));
        $update_city->set('population', escape_html($_POST['population']));
        $update_city->set('time_zone', escape_html($_POST['time_zone']));
        $update_city->set('active', $active);
        $update_city->save();

        if ($update_city) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addCurrency()
{
    global $config,$lang;

    if (isset($_POST['submit'])) {

        $in_left = isset($_POST['in_left']) ? '1' : '0';

        $insert_currency = ORM::for_table($config['db']['pre'].'currencies')->create();
        $insert_currency->name = escape_html($_POST['name']);
        $insert_currency->code = escape_html($_POST['code']);
        $insert_currency->html_entity = escape_html($_POST['html_entity']);
        $insert_currency->font_arial = escape_html($_POST['font_arial']);
        $insert_currency->font_code2000 = escape_html($_POST['font_code2000']);
        $insert_currency->unicode_decimal = escape_html($_POST['unicode_decimal']);
        $insert_currency->unicode_hex = escape_html($_POST['unicode_hex']);
        $insert_currency->decimal_places = escape_html($_POST['decimal_places']);
        $insert_currency->decimal_separator = escape_html($_POST['decimal_separator']);
        $insert_currency->thousand_separator = escape_html($_POST['thousand_separator']);
        $insert_currency->in_left = $in_left;
        $insert_currency->save();

        if ($insert_currency->id()) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editCurrency()
{
    global $config,$lang;

    if (isset($_POST['id'])) {
        $in_left = isset($_POST['in_left']) ? '1' : '0';

        $update_currency = ORM::for_table($config['db']['pre'].'currencies')->find_one(escape_html($_POST['id']));
        $update_currency->set('name', escape_html($_POST['name']));
        $update_currency->set('code', escape_html($_POST['code']));
        $update_currency->set('html_entity', escape_html($_POST['html_entity']));
        $update_currency->set('font_arial', escape_html($_POST['font_arial']));
        $update_currency->set('font_code2000', escape_html($_POST['font_code2000']));
        $update_currency->set('unicode_decimal', escape_html($_POST['unicode_decimal']));
        $update_currency->set('unicode_hex', escape_html($_POST['unicode_hex']));
        $update_currency->set('decimal_places', escape_html($_POST['decimal_places']));
        $update_currency->set('decimal_separator', escape_html($_POST['decimal_separator']));
        $update_currency->set('thousand_separator', escape_html($_POST['thousand_separator']));
        $update_currency->set('in_left', $in_left);
        $update_currency->save();

        if ($update_currency) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addTimezone()
{
    global $config,$lang;

    if (isset($_POST['submit'])) {

        $insert_timezone = ORM::for_table($config['db']['pre'].'time_zones')->create();
        $insert_timezone->country_code = escape_html($_POST['country_code']);
        $insert_timezone->time_zone_id = escape_html($_POST['time_zone_id']);
        $insert_timezone->gmt = escape_html($_POST['gmt']);
        $insert_timezone->dst = escape_html($_POST['dst']);
        $insert_timezone->raw = escape_html($_POST['raw']);
        $insert_timezone->save();

        if ($insert_timezone->id()) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editTimezone()
{
    global $config,$lang;

    if (isset($_POST['id'])) {

        $update_timezone = ORM::for_table($config['db']['pre'].'time_zones')->find_one(escape_html($_POST['id']));
        $update_timezone->set('country_code', escape_html($_POST['country_code']));
        $update_timezone->set('time_zone_id', escape_html($_POST['time_zone_id']));
        $update_timezone->set('gmt', escape_html($_POST['gmt']));
        $update_timezone->set('dst', escape_html($_POST['dst']));
        $update_timezone->set('raw', escape_html($_POST['raw']));
        $update_timezone->save();

        if ($update_timezone) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addMembershipPlan()
{
    global $config,$lang;

    if (isset($_POST['submit'])) {

        $recommended = isset($_POST['recommended']) ? "yes" : "no";
        $pay_mode = isset($_POST['pay_mode']) ? $_POST['pay_mode'] : "one_time";
        $active = isset($_POST['active']) ? "1" : "0";

        $insert_subscription = ORM::for_table($config['db']['pre'].'subscriptions')->create();
        $insert_subscription->sub_title = escape_html($_POST['sub_title']);
        $insert_subscription->sub_term = escape_html($_POST['sub_term']);
        $insert_subscription->sub_amount = escape_html($_POST['sub_amount']);
        $insert_subscription->sub_image = escape_html($_POST['sub_image']);
        $insert_subscription->group_id = escape_html($_POST['group_id']);
        $insert_subscription->pay_mode = $pay_mode;
        $insert_subscription->active = $active;
        $insert_subscription->discount_badge = escape_html($_POST['discount_badge']);
        $insert_subscription->recommended = $recommended;
        $insert_subscription->save();

        if ($insert_subscription->id()) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editMembershipPlan()
{
    global $config,$lang;

    if (isset($_POST['submit'])) {

        $recommended = isset($_POST['recommended']) ? "yes" : "no";
        $pay_mode = isset($_POST['pay_mode']) ? $_POST['pay_mode'] : "one_time";
        $active = isset($_POST['active']) ? "1" : "0";

        $pdo = ORM::get_db();
        $query = "UPDATE `".$config['db']['pre']."subscriptions` SET
        `sub_title` = '" . escape_html($_POST['sub_title']) . "',
        `sub_term` = '" . escape_html($_POST['sub_term']) . "',
		`sub_amount` = '" . escape_html($_POST['sub_amount']) . "',
        `sub_image` = '" . escape_html($_POST['sub_image']) . "',
        `pay_mode` = '" . escape_html($pay_mode) . "',
        `active` = '" . $active . "',
        `group_id` = '" . escape_html($_POST['group_id']) . "',
        `recommended` = '" . escape_html($recommended) . "',
        `discount_badge` = '" . escape_html($_POST['discount_badge']) . "'
        WHERE `sub_id` = '".escape_html($_POST['id'])."' LIMIT 1 ";
        $query_result = $pdo->query($query);

        if ($query_result) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addMembershipPackage()
{
    global $config,$lang;

    if (isset($_POST['submit'])) {

        $removable = isset($_POST['group_removable']) ? $_POST['group_removable'] : 0;

        $featured = isset($_POST['featured_project_fee']) ? $_POST['featured_project_fee'] : 0;
        $urgent = isset($_POST['urgent_project_fee']) ? $_POST['urgent_project_fee'] : 0;
        $highlight = isset($_POST['highlight_project_fee']) ? $_POST['highlight_project_fee'] : 0;

        $featured_duration = isset($_POST['featured_duration']) ? $_POST['featured_duration'] : 0;
        $urgent_duration = isset($_POST['urgent_duration']) ? $_POST['urgent_duration'] : 0;
        $highlight_duration = isset($_POST['highlight_duration']) ? $_POST['highlight_duration'] : 0;

        $top_search_result = isset($_POST['top_search_result']) ? "yes" : "no";
        $show_on_home = isset($_POST['show_on_home']) ? "yes" : "no";
        $show_in_home_search = isset($_POST['show_in_home_search']) ? "yes" : "no";

        $insert_usergroup = ORM::for_table($config['db']['pre'].'usergroups')->create();
        $insert_usergroup->group_name = escape_html($_POST['group_name']);
        $insert_usergroup->group_removable = $removable;
        $insert_usergroup->ad_limit = escape_html($_POST['ad_limit']);
        $insert_usergroup->ad_duration = escape_html($_POST['ad_duration']);
        $insert_usergroup->featured_project_fee = $featured;
        $insert_usergroup->urgent_project_fee = $urgent;
        $insert_usergroup->highlight_project_fee = $highlight;
        $insert_usergroup->featured_duration = $featured_duration;
        $insert_usergroup->urgent_duration = $urgent_duration;
        $insert_usergroup->highlight_duration = $highlight_duration;
        $insert_usergroup->top_search_result = $top_search_result;
        $insert_usergroup->show_on_home = $show_on_home;
        $insert_usergroup->show_in_home_search = $show_in_home_search;
        $insert_usergroup->save();

        if ($insert_usergroup->id()) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editMembershipPackage()
{
    global $config,$lang;

    if (isset($_POST['id'])) {
        $removable = isset($_POST['group_removable']) ? $_POST['group_removable'] : 0;
        $featured = isset($_POST['featured_project_fee']) ? $_POST['featured_project_fee'] : 0;
        $urgent = isset($_POST['urgent_project_fee']) ? $_POST['urgent_project_fee'] : 0;
        $highlight = isset($_POST['highlight_project_fee']) ? $_POST['highlight_project_fee'] : 0;

        $featured_duration = isset($_POST['featured_duration']) ? $_POST['featured_duration'] : 0;
        $urgent_duration = isset($_POST['urgent_duration']) ? $_POST['urgent_duration'] : 0;
        $highlight_duration = isset($_POST['highlight_duration']) ? $_POST['highlight_duration'] : 0;

        $top_search_result = isset($_POST['top_search_result']) ? "yes" : "no";
        $show_on_home = isset($_POST['show_on_home']) ? "yes" : "no";
        $show_in_home_search = isset($_POST['show_in_home_search']) ? "yes" : "no";

        $pdo = ORM::get_db();
        $query = "UPDATE `".$config['db']['pre']."usergroups` SET
        `group_name` = '" . escape_html($_POST['group_name']) . "',
        `group_removable` = '" . escape_html($removable) . "',
        `ad_limit` = '" . escape_html($_POST['ad_limit']) . "',
        `ad_duration` = '" . escape_html($_POST['ad_duration']) . "',
        `featured_project_fee` = '" . escape_html($featured) . "',
        `urgent_project_fee` = '" . escape_html($urgent) . "',
        `highlight_project_fee` = '" . escape_html($highlight) . "',
        `featured_duration` = '" . escape_html($featured_duration) . "',
        `urgent_duration` = '" . escape_html($urgent_duration) . "',
        `highlight_duration` = '" . escape_html($highlight_duration) . "',
        `top_search_result` = '" . escape_html($top_search_result) . "',
        `show_on_home` = '" . escape_html($show_on_home) . "',
        `show_in_home_search` = '" . escape_html($show_in_home_search) . "'
        WHERE `group_id` = '".escape_html($_POST['id'])."' LIMIT 1 ";

        $query_result = $pdo->query($query);

        if ($query_result) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addLanguage()
{
    global $config,$lang;
    if (isset($_POST['submit'])) {
        if(isset($_POST['name']) && escape_html($_POST['name']) != ""){

            $post_langname = str_replace(' ', '', strtolower(escape_html($_POST['name'])));

            $filePath = '../includes/lang/lang_'.escape_html($post_langname).'.php';
            if (!file_exists($filePath)) {
                $source = 'en';
                $target = escape_html($_POST['code']);
                $auto_translate = isset($_POST['auto_tran']) ? '1' : '0';
                $active = isset($_POST['active']) ? '1' : '0';

                $trans = new GoogleTranslate();
                $newLangArray = array();
                foreach ($lang as $key => $value)
                {
                    if($auto_translate == 1){
                        $result = $trans->translate($source, $target, $value);
                    }else{
                        $result = $value;
                    }

                    $newLangArray[$key] = $result;
                }
                fopen($filePath, "w");
                change_config_file_settings($filePath, $newLangArray,$lang);

                $lang_filename = escape_html($post_langname);

                $insert_language = ORM::for_table($config['db']['pre'].'languages')->create();
                $insert_language->code = escape_html($_POST['code']);
                $insert_language->name = escape_html($post_langname);
                $insert_language->direction = escape_html($_POST['direction']);
                $insert_language->file_name = $lang_filename;
                $insert_language->active = $active;
                $insert_language->save();

                if ($insert_language->id()) {
                    $status = "success";
                    $message = $lang['SAVED_SUCCESS'];
                } else{
                    $status = "error";
                    $message = $lang['ERROR_TRY_AGAIN'];
                }


            } else {
                $message = "Same language file is exist. Change language name.";
                echo $json = '{"status" : "error","message" : "' . $message . '"}';
                die();
            }
        }
        else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editLanguage()
{
    global $config,$lang;

    if (isset($_POST['id'])) {

        $active = isset($_POST['active']) ? '1' : '0';
        $lang_filename = strtolower(escape_html($_POST['name']));

        $update_language = ORM::for_table($config['db']['pre'].'languages')->find_one(escape_html($_POST['id']));
        $update_language->set('code', escape_html($_POST['code']));
        $update_language->set('name', escape_html($_POST['name']));
        $update_language->set('direction', escape_html($_POST['direction']));
        $update_language->set('file_name', $lang_filename);
        $update_language->set('active', $active);
        $update_language->save();

        if ($update_language) {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        } else{
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }


    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addStaticPage()
{
    global $config,$lang;
    $errors = array();
    $response = array();

    if (isset($_POST['submit'])) {

        if (empty($_POST['name'])) {
            $errors[]['message'] = $lang['PAGENAME_REQ'];
        }
        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['PAGETITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['PAGECONTENT_REQ'];
        }
        if (!count($errors) > 0) {
            if (empty($_POST['slug']))
                $slug = create_slug(escape_html($_POST['name']));
            else
                $slug = create_slug(escape_html($_POST['slug']));
            $active = isset($_POST['active']) ? '1' : '0';

            $insert_page = ORM::for_table($config['db']['pre'].'pages')->create();
            $insert_page->translation_lang = 'en';
            $insert_page->name = escape_html($_POST['name']);
            $insert_page->title = escape_html($_POST['title']);
            $insert_page->content = escape_html($_POST['content']);
            $insert_page->slug = $slug;
            $insert_page->type = escape_html($_POST['type']);
            $insert_page->active = $active;
            $insert_page->save();

            $id = $insert_page->id();

            $update_page = ORM::for_table($config['db']['pre'].'pages')->find_one($id);
            $update_page->set('translation_of', $id);
            $update_page->set('parent_id', $id);
            $update_page->save();

            $rows = ORM::for_table($config['db']['pre'].'languages')
                ->select_many('code','name')
                ->where('active', '1')
                ->where_not_equal('code', 'en')
                ->find_many();

            foreach ($rows as $fetch){
                $insert_page = ORM::for_table($config['db']['pre'].'pages')->create();
                $insert_page->translation_lang = $fetch['code'];
                $insert_page->translation_of = $id;
                $insert_page->parent_id = $id;
                $insert_page->name = escape_html($_POST['name']);
                $insert_page->title = escape_html($_POST['title']);
                $insert_page->content = escape_html($_POST['content']);
                $insert_page->slug = $slug;
                $insert_page->type = escape_html($_POST['type']);
                $insert_page->active = $active;
                $insert_page->save();

            }

            $status = "success";
            $message = $lang['SP_PAGE_ADDED'];

            echo $json = '{"id" : "' . $id . '","status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        }else {
            $status = "error";
            $message = $lang['ERROR'];
        }
    } else {
        $status = "error";
        $message = $lang['UNKNOWN_ERROR'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function editStaticPage()
{
    global $config, $lang;
    $errors = array();
    $response = array();

    if (isset($_POST['id'])) {

        if (empty($_POST['name'])) {
            $errors[]['message'] = $lang['PAGENAME_REQ'];
        }
        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['PAGETITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['PAGECONTENT_REQ'];
        }
        if (!count($errors) > 0) {
            if (empty($_POST['slug']))
                $slug = create_slug(escape_html($_POST['name']));
            else
                $slug = create_slug(escape_html($_POST['slug']));
            $active = isset($_POST['active']) ? '1' : '0';

            $update_page = ORM::for_table($config['db']['pre'] . 'pages')->find_one(escape_html($_POST['id']));
            $update_page->set('name', escape_html($_POST['name']));
            $update_page->set('title', escape_html($_POST['title']));
            $update_page->set('content', escape_html($_POST['content']));
            $update_page->set('slug', $slug);
            $update_page->set('type', escape_html($_POST['type']));
            $update_page->set('active', $active);
            $update_page->save();

            $status = "success";
            $message = $lang['SP_PAGE_EDITED'];

            echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        } else {
            $status = "error";
            $message = $lang['ERROR'];
        }
    }
    else {
        $status = "error";
        $message = $lang['UNKNOWN_ERROR'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();

}

function addFAQentry()
{
    global $config, $lang;
    $errors = array();

    if (isset($_POST['submit'])) {

        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['FAQTITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['FAQCONTENT_REQ'];
        }
        if (!count($errors) > 0) {
            $active = isset($_POST['active']) ? '1' : '0';

            $insert_faq = ORM::for_table($config['db']['pre'] . 'faq_entries')->create();
            $insert_faq->translation_lang = 'en';
            $insert_faq->faq_title = escape_html($_POST['title']);
            $insert_faq->faq_content = escape_html($_POST['content']);
            $insert_faq->active = $active;
            $insert_faq->save();

            $id = $insert_faq->id();

            $pdo = ORM::get_db();
            $query = "UPDATE `" . $config['db']['pre'] . "faq_entries` SET
                `translation_of` = '" . escape_html($id) . "',
                `parent_id` = '" . escape_html($id) . "'
                 WHERE `faq_id` = '" . escape_html($id) . "' LIMIT 1 ";
            $query_result = $pdo->query($query);

            $rows = ORM::for_table($config['db']['pre'] . 'languages')
                ->select_many('code', 'name')
                ->where('active', '1')
                ->where_not_equal('code', 'en')
                ->find_many();

            foreach ($rows as $fetch) {
                $insert_faq = ORM::for_table($config['db']['pre'] . 'faq_entries')->create();
                $insert_faq->translation_lang = $fetch['code'];
                $insert_faq->translation_of = $id;
                $insert_faq->parent_id = $id;
                $insert_faq->faq_title = escape_html($_POST['title']);
                $insert_faq->faq_content = escape_html($_POST['content']);
                $insert_faq->active = $active;
                $insert_faq->save();
            }

            $status = "success";
            $message = $lang['SAVED_SUCCESS'];

            echo $json = '{"id" : "' . $id . '","status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        } else {
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function editFAQentry()
{
    global $config, $lang;
    $errors = array();
    $response = array();

    if (isset($_POST['id'])) {

        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['FAQTITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['FAQCONTENT_REQ'];
        }
        if (!count($errors) > 0) {
            $active = isset($_POST['active']) ? '1' : '0';

            $pdo = ORM::get_db();
            $query = "UPDATE `" . $config['db']['pre'] . "faq_entries` SET
                `faq_title` = '" . escape_html($_POST['title']) . "',
                `faq_content` = '" . escape_html($_POST['content']) . "',
                 `active` = '" . $active . "'
                 WHERE `faq_id` = '" . escape_html($_POST['id']) . "' LIMIT 1 ";
            $query_result = $pdo->query($query);

            $status = "success";
            $message = $lang['SP_PAGE_EDITED'];

            echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        } else {
            $status = "error";
            $message = $lang['ERROR'];
        }
    } else {
        $status = "error";
        $message = $lang['UNKNOWN_ERROR'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function expirePostRenew()
{
    global $config, $lang;
    $pdo = ORM::get_db();
    $timenow = date('Y-m-d H:i:s');

    $ad_duration = isset($_REQUEST['duration']) ? $_REQUEST['duration'] : '7';

    $expire_time = date('Y-m-d H:i:s', strtotime($timenow . ' +' . $ad_duration . ' day'));
    $expire_timestamp = strtotime($expire_time);

    $query = "UPDATE `" . $config['db']['pre'] . "product` SET
    `status` = 'active', `expire_date` = '" . $expire_timestamp . "'
    WHERE  status='expire'";
    $pdo->query($query);

    $status = "success";
    $message = $lang['SAVED_SUCCESS'];

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function approve_all_pending_post()
{
    global $config, $lang, $link;
    if (check_allow()) {
        $items = ORM::for_table($config['db']['pre'] . 'product')
            ->select_many('id', 'product_name', 'user_id')
            ->where('status', 'pending')
            ->find_many();

        if (count($items) > 0) {
            foreach ($items as $info) {
                //Ad approve Email to seller
                $product_id = $info['id'];
                $item_title = $info['product_name'];
                $item_author_id = $info['user_id'];

                $product = ORM::for_table($config['db']['pre'] . 'product')->find_one($product_id);
                $product->set('status', 'active');
                $product->save();

                /*SEND RESUBMISSION AD APPROVE EMAIL*/
                email_template("ad_approve", $item_author_id, null, $product_id, $item_title);
            }
        }
    }
    $status = "success";
    $message = $lang['SAVED_SUCCESS'];
    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function postEdit()
{
    global $config, $lang;
    $errors = array();
    $response = array();

    if (isset($_POST['id'])) {

        if (empty($_POST['category']) or empty($_POST['sub_category'])) {
            $errors[]['message'] = $lang['CAT_REQ'];
        }
        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['ADTITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['DESC_REQ'];
        }
        if (empty($_POST['city'])) {
            $errors[]['message'] = $lang['CITY_REQ'];
        }
        if (!empty($_POST['price'])) {
            if (!is_numeric(escape_html($_POST['price']))) {
                $errors[]['message'] = $lang['PRICE_MUST_NO'];
            }
        }

        if (!count($errors) > 0) {

            $urgent = isset($_POST['urgent']) ? '1' : '0';
            $featured = isset($_POST['featured']) ? '1' : '0';
            $highlight = isset($_POST['highlight']) ? '1' : '0';

            if ($config['post_desc_editor'] == 1)
                $description = escape_html($_POST['content']);
            else
                $description = escape_html($_POST['content']);

            $start_date = escape_html($_POST['start_date']);
            $expire_date = escape_html($_POST['expire_date']);

            $start_time = date('Y-m-d H:i:s', strtotime($_POST['start_date']));
            $expire_time = date('Y-m-d H:i:s', strtotime($_POST['expire_date']));
            $expire_timestamp = strtotime($expire_date);
            $now = date("Y-m-d H:i:s");

            $item_edit = ORM::for_table($config['db']['pre'] . 'product')->find_one(escape_html($_POST['id']));
            $item_edit->set('product_name', escape_html($_POST['title']));
            $item_edit->set('status', escape_html($_POST['status']));
            $item_edit->set('category', escape_html($_POST['category']));
            $item_edit->set('sub_category', escape_html($_POST['sub_category']));
            $item_edit->set('featured', $featured);
            $item_edit->set('urgent', $urgent);
            $item_edit->set('highlight', $highlight);
            $item_edit->set('city', escape_html($_POST['city']));
            $item_edit->set('state', escape_html($_POST['state']));
            $item_edit->set('country', escape_html($_POST['country']));
            $item_edit->set('description', $description);
            $item_edit->set('created_at', $start_time);
            $item_edit->set('expire_date', $expire_timestamp);
            $item_edit->set('updated_at', $now);
            $item_edit->save();

            $status = "success";
            $message = $lang['SAVED_SUCCESS'];

            echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        } else {
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function transactionEdit()
{
    global $config, $lang;
    $errors = array();
    $response = array();

    if (isset($_POST['id'])) {

        if (isset($_POST['status'])) {

            if (escape_html($_POST['status']) == "success") {
                $transaction_id = escape_html($_POST['id']);
                transaction_success($transaction_id);
            } else {
                $transaction = ORM::for_table($config['db']['pre'] . 'transaction')->find_one(escape_html($_POST['id']));
                $transaction->status = escape_html($_POST['status']);
                $transaction->save();
            }
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];


        } else {
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editAdvertise()
{
    global $config, $lang;

    if (isset($_POST['id'])) {

        $status = isset($_POST['status']) ? '1' : '0';

        $update_adsense = ORM::for_table($config['db']['pre'] . 'adsense')->find_one(escape_html($_POST['id']));
        $update_adsense->set('provider_name', escape_html($_POST['provider_name']));
        $update_adsense->set('status', $status);
        $update_adsense->set('large_track_code', escape_html($_POST['large_track_code']));
        $update_adsense->set('tablet_track_code', escape_html($_POST['tablet_track_code']));
        $update_adsense->set('phone_track_code', escape_html($_POST['phone_track_code']));
        $update_adsense->save();

        $status = "success";
        $message = $lang['SAVED_SUCCESS'];

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function paymentEdit()
{
    global $config, $lang;

    if (isset($_POST['id'])) {

        $pdo = ORM::get_db();
        $query = "UPDATE `" . $config['db']['pre'] . "payments` SET
            `payment_title` = '" . escape_html($_POST['title']) . "',
            `payment_install` = '" . escape_html($_POST['install']) . "'
            WHERE `payment_id` = '" . escape_html($_POST['id']) . "' LIMIT 1 ";
        $query_result = $pdo->query($query);

        if(isset($_POST['paypal_sandbox_mode'])){
            update_option("paypal_sandbox_mode",isset($_POST['paypal_sandbox_mode'])? $_POST['paypal_sandbox_mode'] : "");
            update_option("paypal_payment_mode",isset($_POST['paypal_payment_mode'])? $_POST['paypal_payment_mode'] : "");
            update_option("paypal_api_client_id",isset($_POST['paypal_api_client_id'])? $_POST['paypal_api_client_id'] : "");
            update_option("paypal_api_secret",isset($_POST['paypal_api_secret'])? $_POST['paypal_api_secret'] : "");
        }

        if(isset($_POST['stripe_secret_key'])){
            update_option("stripe_payment_mode",$_POST['stripe_payment_mode']);
            update_option("stripe_publishable_key",$_POST['stripe_publishable_key']);
            update_option("stripe_secret_key",$_POST['stripe_secret_key']);
            update_option("stripe_webhook_secret",$_POST['stripe_webhook_secret']);
        }


        if(isset($_POST['paystack_public_key'])){
            update_option("paystack_public_key",$_POST['paystack_public_key']);
            update_option("paystack_secret_key",$_POST['paystack_secret_key']);
        }

        if(isset($_POST['payumoney_merchant_key'])){
            update_option("payumoney_sandbox_mode",$_POST['payumoney_sandbox_mode']);
            update_option("payumoney_merchant_key",$_POST['payumoney_merchant_key']);
            update_option("payumoney_merchant_salt",$_POST['payumoney_merchant_salt']);
            update_option("payumoney_merchant_id",$_POST['payumoney_merchant_id']);
        }

        if(isset($_POST['checkout_account_number'])){
            update_option("2checkout_sandbox_mode",$_POST['2checkout_sandbox_mode']);
            update_option("checkout_account_number",$_POST['checkout_account_number']);
            update_option("checkout_public_key",$_POST['checkout_public_key']);
            update_option("checkout_private_key",$_POST['checkout_private_key']);
        }

        if(isset($_POST['company_bank_info'])){
            update_option("company_bank_info",$_POST['company_bank_info']);
        }

        if(isset($_POST['company_cheque_info'])){
            update_option("company_cheque_info",$_POST['company_cheque_info']);
            update_option("cheque_payable_to",$_POST['cheque_payable_to']);
        }

        if(isset($_POST['skrill_merchant_id'])){
            update_option("skrill_merchant_id",$_POST['skrill_merchant_id']);
        }

        if(isset($_POST['nochex_merchant_id'])){
            update_option("nochex_merchant_id",$_POST['nochex_merchant_id']);
        }

        if(isset($_POST['CCAVENUE_MERCHANT_KEY'])){
            update_option("CCAVENUE_MERCHANT_KEY",$_POST['CCAVENUE_MERCHANT_KEY']);
            update_option("CCAVENUE_ACCESS_CODE",$_POST['CCAVENUE_ACCESS_CODE']);
            update_option("CCAVENUE_WORKING_KEY",$_POST['CCAVENUE_WORKING_KEY']);
        }

        if(isset($_POST['PAYTM_ENVIRONMENT'])){
            update_option("PAYTM_ENVIRONMENT",$_POST['PAYTM_ENVIRONMENT']);
            update_option("PAYTM_MERCHANT_KEY",$_POST['PAYTM_MERCHANT_KEY']);
            update_option("PAYTM_MERCHANT_MID",$_POST['PAYTM_MERCHANT_MID']);
            update_option("PAYTM_MERCHANT_WEBSITE",$_POST['PAYTM_MERCHANT_WEBSITE']);
        }
        if(isset($_POST['mollie_api_key'])){
            update_option("mollie_api_key",$_POST['mollie_api_key']);
        }

        if(isset($_POST['iyzico_api_key'])){
            update_option("iyzico_sandbox_mode",$_POST['iyzico_sandbox_mode']);
            update_option("iyzico_api_key",$_POST['iyzico_api_key']);
            update_option("iyzico_secret_key",$_POST['iyzico_secret_key']);
        }

        if(isset($_POST['midtrans_client_key'])){
            update_option("midtrans_sandbox_mode",$_POST['midtrans_sandbox_mode']);
            update_option("midtrans_client_key",$_POST['midtrans_client_key']);
            update_option("midtrans_server_key",$_POST['midtrans_server_key']);
        }

        if(isset($_POST['paytabs_profile_id'])){
            update_option("paytabs_profile_id",$_POST['paytabs_profile_id']);
            update_option("paytabs_secret_key",$_POST['paytabs_secret_key']);
        }

        if(isset($_POST['telr_store_id'])){
            update_option("telr_sandbox_mode",$_POST['telr_sandbox_mode']);
            update_option("telr_store_id",$_POST['telr_store_id']);
            update_option("telr_authkey",$_POST['telr_authkey']);
        }

        if(isset($_POST['razorpay_api_key'])){
            update_option("razorpay_api_key",$_POST['razorpay_api_key']);
            update_option("razorpay_secret_key",$_POST['razorpay_secret_key']);
        }

        if(isset($_POST['flutterwave_api_key'])){
            update_option("flutterwave_api_key",$_POST['flutterwave_api_key']);
            update_option("flutterwave_secret_key",$_POST['flutterwave_secret_key']);
        }
        $status = "success";
        $message = $lang['SAVED_SUCCESS'];

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function SaveCustomFieldsSettings()
{

    global $config, $lang, $link;
    $status = "";

    if (isset($_GET['quickad_custom_fields_per_service'])) {

        $cf_specific_fields = implode(",", escape_html($_GET['cf_specific_fields']));

        update_option("quickad_custom_fields_per_service", escape_html($_GET['quickad_custom_fields_per_service']));
        update_option("cf_specific_fields", $cf_specific_fields);
        $status = "success";
        $message = 'Custom Fields Setting updated Successfully';
    }


    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function SaveSettings()
{

    global $config, $lang, $link;
    $status = "";
    if (isset($_POST['logo_watermark'])) {
        $valid_formats = array("jpg", "jpeg", "png"); // Valid image formats
        if (isset($_FILES['banner']) && $_FILES['banner']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['banner']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/banner/"; //Image upload directory
                $bannername = stripslashes($_FILES['banner']['name']);
                $size = filesize($_FILES['banner']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($bannername);
                $ext = strtolower($ext);
                $banner_name = "bg" . '.' . $ext;
                $newBgname = $uploaddir . $banner_name;
                //Moving file to uploads folder
                if (file_exists($newBgname)) {
                    unlink($newBgname);
                }
                if (move_uploaded_file($_FILES['banner']['tmp_name'], $newBgname)) {

                    update_option("home_banner", $banner_name);
                    $status = "success";
                    $message = ' Banner updated Successfully ';

                } else {
                    $status = "error";
                    $message = 'Error in uploading Banner';
                }
            } else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if (isset($_FILES['favicon']) && $_FILES['favicon']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['favicon']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/logo/"; //Image upload directory
                $filename = stripslashes($_FILES['favicon']['name']);
                $size = filesize($_FILES['favicon']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($filename);
                $ext = strtolower($ext);
                $image_name = "favicon" . '.' . $ext;
                $newLogo = $uploaddir . $image_name;
                if (file_exists($newLogo)) {
                    unlink($newLogo);
                }
                //Moving file to uploads folder
                if (move_uploaded_file($_FILES['favicon']['tmp_name'], $newLogo)) {

                    update_option("site_favicon", $image_name);
                    $status = "success";
                    $message = ' Site Favicon icon updated Successfully ';
                } else {
                    $status = "error";
                    $message = 'Error in uploading Favicon';
                }
            } else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if (isset($_FILES['file']) && $_FILES['file']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/logo/"; //Image upload directory
                $filename = stripslashes($_FILES['file']['name']);
                $size = filesize($_FILES['file']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($filename);
                $ext = strtolower($ext);
                $image_name = $config['tpl_name'] . "_logo" . '.' . $ext;
                $newLogo = $uploaddir . $image_name;
                if (file_exists($newLogo)) {
                    unlink($newLogo);
                }
                //Moving file to uploads folder
                if (move_uploaded_file($_FILES['file']['tmp_name'], $newLogo)) {

                    update_option("site_logo", $image_name);
                    $status = "success";
                    $message = ' Site Logo updated Successfully ';
                } else {
                    $status = "error";
                    $message = 'Error in uploading Logo';
                }
            } else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if (isset($_FILES['footer_logo']) && $_FILES['footer_logo']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['footer_logo']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/logo/"; //Image upload directory
                $filename = stripslashes($_FILES['footer_logo']['name']);
                $size = filesize($_FILES['footer_logo']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($filename);
                $ext = strtolower($ext);
                $image_name = $config['tpl_name'] . "_footer_logo" . '.' . $ext;
                $newLogo = $uploaddir . $image_name;
                if (file_exists($newLogo)) {
                    unlink($newLogo);
                }
                //Moving file to uploads folder
                if (move_uploaded_file($_FILES['footer_logo']['tmp_name'], $newLogo)) {

                    update_option("site_logo_footer", $image_name);
                    $status = "success";
                    $message = ' Site Logo updated Successfully ';
                } else {
                    $status = "error";
                    $message = 'Error in uploading Logo';
                }
            } else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if (isset($_FILES['watermark']) && $_FILES['watermark']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['watermark']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/logo/"; //Image upload directory
                $filename = stripslashes($_FILES['watermark']['name']);
                $size = filesize($_FILES['watermark']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($filename);
                $ext = strtolower($ext);
                $mark_name = "watermark" . '.' . $ext;
                $watermark = $uploaddir . $mark_name;
                if (file_exists($watermark)) {
                    unlink($watermark);
                }
                //Moving file to uploads folder
                if (move_uploaded_file($_FILES['watermark']['tmp_name'], $watermark)) {
                    $status = "success";
                    $message = ' Watermark Logo updated Successfully ';
                } else {
                    $status = "error";
                    $message = 'Error in uploading Watermark';
                }
            } else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if (isset($_FILES['adminlogo']) && $_FILES['adminlogo']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['adminlogo']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/logo/"; //Image upload directory
                $filename = stripslashes($_FILES['adminlogo']['name']);
                $size = filesize($_FILES['adminlogo']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($filename);
                $ext = strtolower($ext);
                $adminlogo_name = "adminlogo" . '.' . $ext;
                $adminlogo = $uploaddir . $adminlogo_name;
                if (file_exists($adminlogo)) {
                    unlink($adminlogo);
                }
                //Moving file to uploads folder
                if (move_uploaded_file($_FILES['adminlogo']['tmp_name'], $adminlogo)) {
                    update_option("site_admin_logo", $adminlogo_name);
                    $status = "success";
                    $message = ' Adminlogo Logo updated Successfully ';
                } else {
                    $status = "error";
                    $message = 'Error in uploading adminlogo';
                }
            } else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if ($status == "") {
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }
    }

    if (isset($_POST['general_setting'])) {
        update_option("site_url", escape_html($_POST['site_url']));
        update_option("site_title", escape_html($_POST['site_title']));
        update_option("home_page", escape_html($_POST['home_page']));
        update_option("featured_fee", escape_html($_POST['featured_fee']));
        update_option("urgent_fee", escape_html($_POST['urgent_fee']));
        update_option("highlight_fee", escape_html($_POST['highlight_fee']));
        update_option("cron_exec_time", escape_html($_POST['cron_exec_time']));
        update_option("delete_expired", escape_html($_POST['delete_expired']));
        update_option("userlangsel", escape_html($_POST['userlangsel']));
        update_option("userthemesel", escape_html($_POST['userthemesel']));
        update_option("color_switcher", escape_html($_POST['color_switcher']));
        update_option("transfer_filter", escape_html($_POST['transfer_filter']));
        update_option("temp_php", escape_html($_POST['temp_php']));
        update_option("quickad_debug", escape_html($_POST['quickad_debug']));
        update_option("termcondition_link", escape_html($_POST['termcondition_link']));
        update_option("privacy_link", escape_html($_POST['privacy_link']));
        update_option("cookie_link", escape_html($_POST['cookie_link']));
        update_option("cookie_consent", escape_html($_POST['cookie_consent']));
        update_option("non_active_msg", escape_html($_POST['non_active_msg']));
        update_option("non_active_allow", escape_html($_POST['non_active_allow']));

        $status = "success";
        $message = 'General setting updated Successfully';
    }

    if (isset($_POST['blog_setting'])) {

        update_option("blog_enable", escape_html($_POST['blog_enable']));
        update_option("blog_banner", escape_html($_POST['blog_banner']));
        update_option("show_blog_home", escape_html($_POST['show_blog_home']));
        update_option("blog_comment_enable", escape_html($_POST['blog_comment_enable']));
        update_option("blog_comment_approval", escape_html($_POST['blog_comment_approval']));
        update_option("blog_comment_user", escape_html($_POST['blog_comment_user']));
        $status = "success";
        $message = 'Blog setting updated Successfully';
    }

    if (isset($_POST['testimonials_setting'])) {

        update_option("testimonials_enable", escape_html($_POST['testimonials_enable']));
        update_option("show_testimonials_blog", escape_html($_POST['show_testimonials_blog']));
        update_option("show_testimonials_home", escape_html($_POST['show_testimonials_home']));
        $status = "success";
        $message = 'Testimonials setting updated Successfully';
    }

    if (isset($_POST['live_location_track'])) {
        update_option("location_track_icon", escape_html($_POST['location_track_icon']));
        update_option("auto_detect_location", escape_html($_POST['auto_detect_location']));
        update_option("live_location_api", escape_html($_POST['live_location_api']));
        $status = "success";
        $message = 'Live Location setting updated Successfully';
    }

    if (isset($_POST['quick_map'])) {
        update_option("map_type", escape_html($_POST['map_type']));
        update_option("openstreet_access_token", escape_html($_POST['openstreet_access_token']));
        update_option("gmap_api_key", escape_html($_POST['gmap_api_key']));
        update_option("home_map_zoom", escape_html($_POST['home_map_zoom']));
        update_option("map_color", escape_html($_POST['map_color']));
        update_option("home_map_latitude", escape_html($_POST['home_map_latitude']));
        update_option("home_map_longitude", escape_html($_POST['home_map_longitude']));
        update_option("contact_latitude", escape_html($_POST['contact_latitude']));
        update_option("contact_longitude", escape_html($_POST['contact_longitude']));
        $status = "success";
        $message = 'Setting updated Successfully';
    }

    if (isset($_POST['app_setting'])) {
        update_option("app_name", escape_html($_POST['app_name']));
        update_option("app_version", escape_html($_POST['app_version']));
        update_option("detect_live_location", escape_html($_POST['detect_live_location']));
        update_option("firebase_server_key", escape_html($_POST['firebase_server_key']));
        update_option("facebook_interstitial", escape_html($_POST['facebook_interstitial']));
        update_option("google_interstitial", escape_html($_POST['google_interstitial']));
        update_option("google_banner", escape_html($_POST['google_banner']));
        update_option("premium_app", escape_html($_POST['premium_app']));
        $status = "success";
        $message = 'App setting updated Successfully';
    }

    if (isset($_POST['brodcast_push_notification'])) {
        $push_users_list = escape_html($_POST['push_users_list']);
        $notification_title = escape_html($_POST['notification_title']);
        $notification_message = escape_html($_POST['notification_message']);


        $notification_title = ($notification_title != null) ? $notification_title : $config['app_name'];

        if ($push_users_list == "0") {
            /*******For All Users*******/
            $result = ORM::for_table($config['db']['pre'] . 'firebase_device_token')
                ->select('token')
                ->find_many();
            if (isset($result)) {
                $token = array();
                foreach ($result as $info) {
                    $token[] = $info['token'];
                }
            } else {
                return;
            }
        } else if ($push_users_list == "1") {
            /*******For Registered Users*******/
            $result = ORM::for_table($config['db']['pre'] . 'firebase_device_token')
                ->select('token')
                ->where_not_equal('user_id', '0')
                ->find_many();
            if (isset($result)) {
                $token = array();
                foreach ($result as $info) {
                    $token[] = $info['token'];
                }
            } else {
                return;
            }
        } else {
            /*******For Unregistered Users*******/
            $result = ORM::for_table($config['db']['pre'] . 'firebase_device_token')
                ->select('token')
                ->where('user_id', '0')
                ->find_many();

            if (isset($result)) {
                $token = array();
                foreach ($result as $info) {
                    $token[] = $info['token'];
                }
            } else {
                return;
            }
        }

        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'registration_ids' => $token,
            'notification' => array(
                "body" => $notification_message,
                "title" => $notification_title,
                "icon" => "myicon"
            )
        );

        $fields = json_encode($fields);
        $headers = array(
            'Authorization: key=' . $config['firebase_server_key'],
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);

        $status = "success";
        $message = 'Notification Sent Successfully';
    }

    if (isset($_POST['international'])) {

        if (isset($_POST['currency'])) {
            $info = ORM::for_table($config['db']['pre'] . 'currencies')->find_one(escape_html($_POST['currency']));

            $currency_sign = $info['html_entity'];
            $currency_code = $info['code'];
            $currency_pos = $info['in_left'];
        }
        update_option("country_type", escape_html($_POST['country_type']));
        update_option("specific_country", escape_html($_POST['specific_country']));
        update_option("lang", escape_html($_POST['lang']));
        update_option("timezone", escape_html($_POST['timezone']));
        update_option("currency_sign", $currency_sign);
        update_option("currency_code", $currency_code);
        update_option("currency_pos", $currency_pos);
        $status = "success";
        $message = 'International setting updated Successfully';
    }


    if (isset($_POST['email_setting'])) {

        update_option("admin_email", escape_html($_POST['admin_email']));
        update_option("email_template", escape_html($_POST['email_template']));
        update_option("email_engine", escape_html($_POST['email_engine']));
        update_option("email_type", escape_html($_POST['email_type']));

        update_option("smtp_host", escape_html($_POST['smtp_host']));
        update_option("smtp_port", escape_html($_POST['smtp_port']));
        update_option("smtp_username", escape_html($_POST['smtp_username']));
        update_option("smtp_password", escape_html($_POST['smtp_password']));
        update_option("smtp_secure", escape_html($_POST['smtp_secure']));
        update_option("smtp_auth", escape_html($_POST['smtp_auth']));

        update_option("aws_host", escape_html($_POST['aws_host']));
        update_option("aws_access_key", escape_html($_POST['aws_access_key']));
        update_option("aws_secret_key", escape_html($_POST['aws_secret_key']));

        update_option("mandrill_user", escape_html($_POST['mandrill_user']));
        update_option("mandrill_key", escape_html($_POST['mandrill_key']));

        update_option("sendgrid_user", escape_html($_POST['sendgrid_user']));
        update_option("sendgrid_pass", escape_html($_POST['sendgrid_pass']));


        $status = "success";
        $message = 'Email setting updated Successfully';
    }

    if (isset($_POST['theme_setting'])) {
        update_option("contact_validation", escape_html($_POST['contact_validation']));
        update_option("listing_view", escape_html($_POST['listing_view']));
        update_option("theme_color", escape_html($_POST['theme_color']));
        update_option("meta_keywords", escape_html($_POST['meta_keywords']));
        update_option("meta_description", escape_html($_POST['meta_description']));
        update_option("contact_address", escape_html($_POST['contact_address']));
        update_option("contact_phone", escape_html($_POST['contact_phone']));
        update_option("contact_email", escape_html($_POST['contact_email']));
        update_option("footer_text", escape_html($_POST['footer_text']));
        update_option("copyright_text", escape_html($_POST['copyright_text']));
        update_option("facebook_link", escape_html($_POST['facebook_link']));
        update_option("twitter_link", escape_html($_POST['twitter_link']));
        update_option("googleplus_link", escape_html($_POST['googleplus_link']));
        update_option("youtube_link", escape_html($_POST['youtube_link']));
        update_option("external_code", $_POST['external_code']);
        $status = "success";
        $message = ' Theme Setting updated Successfully';
    }

    if (isset($_POST['frontend_submission'])) {
        update_option("post_without_login", escape_html($_POST['post_without_login']));
        update_option("post_auto_approve", escape_html($_POST['post_auto_approve']));
        update_option("post_desc_editor", escape_html($_POST['post_desc_editor']));
        update_option("post_address_mode", escape_html($_POST['post_address_mode']));
        update_option("post_tags_mode", escape_html($_POST['post_tags_mode']));
        update_option("post_watermark", escape_html($_POST['post_watermark']));
        update_option("max_image_upload", escape_html($_POST['max_image_upload']));
        update_option("post_premium_listing", escape_html($_POST['post_premium_listing']));

        $status = "success";
        $message = 'Frontend submission form setting updated Successfully';
    }

    if (isset($_POST['social_login_setting'])) {
        update_option("facebook_app_id", escape_html($_POST['facebook_app_id']));
        update_option("facebook_app_secret", escape_html($_POST['facebook_app_secret']));
        update_option("google_app_id", escape_html($_POST['google_app_id']));
        update_option("google_app_secret", escape_html($_POST['google_app_secret']));
        $status = "success";
        $message = ' Social Login setting updated Successfully';
    }

    if (isset($_POST['recaptcha_setting'])) {

        update_option("recaptcha_mode", escape_html($_POST['recaptcha_mode']));
        update_option("recaptcha_public_key", escape_html($_POST['recaptcha_public_key']));
        update_option("recaptcha_private_key", escape_html($_POST['recaptcha_private_key']));
        $status = "success";
        $message = 'reCAPTCHA setting updated Successfully';
    }

    if (isset($_POST['valid_purchase_setting'])) {

        // Set API Key
        $code = escape_html($_POST['purchase_key']);
        $buyer_email = (isset($_POST['buyer_email'])) ? escape_html($_POST['buyer_email']) : "";
        $installing_version = 'pro';

        $url = "https://bylancer.com/api/api.php?verify-purchase=" . $code . "&version=" . $installing_version . "&site_url=" . $config['site_url'] . "&email=" . $buyer_email."&author=bylancer-tech";
        // Open cURL channel
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //Set the user agent
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Decode returned JSON
        $output = json_decode(curl_exec($ch), true);
        // Close Channel
        curl_close($ch);

        if ($output['success']) {
            if (isset($config['quickad_secret_file']) && $config['quickad_secret_file'] != "") {
                $fileName = $config['quickad_secret_file'];
            } else {
                $fileName = get_random_string();
            }
            file_put_contents($fileName . '.php', $output['data']);
            $success = true;
            update_option("quickad_secret_file", $fileName);
            update_option("purchase_key", escape_html($_POST['purchase_key']));
            $status = "success";
            $message = 'Purchase code verified successfully';
        } else {
            $status = "error";
            $message = $output['error'];
        }
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function saveEmailTemplate()
{

    global $config, $lang, $link;

    if (isset($_POST['email_setting'])) {
        $email_template = escape_html($_POST['email_template']);
        update_option("email_template", $email_template);
        if ($email_template == 0) {
            update_option("email_message_signup_details", escape_html($_POST['email_message_editor_signup_details']));
            update_option("email_message_signup_confirm", escape_html($_POST['email_message_editor_signup_confirm']));
            update_option("email_message_forgot_pass", escape_html($_POST['email_message_editor_forgot_pass']));
            update_option("email_message_contact", escape_html($_POST['email_message_editor_contact']));
            update_option("email_message_feedback", escape_html($_POST['email_message_editor_feedback']));
            update_option("email_message_report", escape_html($_POST['email_message_editor_report']));

            update_option("email_message_ad_approve", escape_html($_POST['email_message_editor_ad_approve']));
            update_option("email_message_re_ad_approve", escape_html($_POST['email_message_editor_re_ad_approve']));
            update_option("email_message_contact_seller", escape_html($_POST['email_message_editor_contact_seller']));

            update_option("email_message_post_notification", escape_html($_POST['email_message_editor_post_notification']));
        } else {
            update_option("email_message_signup_details", escape_html($_POST['email_message_textarea_signup_details']));
            update_option("email_message_signup_confirm", escape_html($_POST['email_message_textarea_signup_confirm']));
            update_option("email_message_forgot_pass", escape_html($_POST['email_message_textarea_forgot_pass']));
            update_option("email_message_contact", escape_html($_POST['email_message_textarea_contact']));
            update_option("email_message_feedback", escape_html($_POST['email_message_textarea_feedback']));
            update_option("email_message_report", escape_html($_POST['email_message_textarea_report']));

            update_option("email_message_ad_approve", escape_html($_POST['email_message_textarea_ad_approve']));
            update_option("email_message_re_ad_approve", escape_html($_POST['email_message_textarea_re_ad_approve']));
            update_option("email_message_contact_seller", escape_html($_POST['email_message_textarea_contact_seller']));
            update_option("email_message_post_notification", escape_html($_POST['email_message_textarea_post_notification']));
        }
        update_option("email_sub_signup_details", escape_html($_POST['email_sub_signup_details']));
        update_option("email_sub_signup_confirm", escape_html($_POST['email_sub_signup_confirm']));
        update_option("email_sub_forgot_pass", escape_html($_POST['email_sub_forgot_pass']));
        update_option("email_sub_contact", escape_html($_POST['email_sub_contact']));
        update_option("email_sub_feedback", escape_html($_POST['email_sub_feedback']));
        update_option("email_sub_report", escape_html($_POST['email_sub_report']));

        update_option("email_sub_ad_approve", escape_html($_POST['email_sub_ad_approve']));
        update_option("email_sub_re_ad_approve", escape_html($_POST['email_sub_re_ad_approve']));
        update_option("email_sub_contact_seller", escape_html($_POST['email_sub_contact_seller']));

        update_option("email_sub_post_notification", escape_html($_POST['email_sub_post_notification']));

        $status = "success";
        $message = 'Email setting updated Successfully';
    } else {
        $status = "Error";
        $message = 'Problem in save setting.';
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function testEmailTemplate()
{

    global $config, $lang, $link;

    if (isset($_POST['test-email-notification'])) {
        $test_to_email = escape_html($_POST['test_to_email']);
        $test_to_name = escape_html($_POST['test_to_name']);

        if (isset($_POST['signup-details'])) {

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_signup_details'];
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('USER_FULLNAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_signup_details'];
            $page->SetParameter('USERNAME', "demo");
            $page->SetParameter('PASSWORD', "demo");
            $page->SetParameter('USER_ID', "1");
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('USER_FULLNAME', $test_to_name);
            $email_body = $page->CreatePageReturn($lang, $config, $link);

            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        if (isset($_POST['create-account'])) {

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_signup_confirm'];
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('USER_FULLNAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $confirmation_link = $link['SIGNUP'] . "?confirm=123456&user=1";
            $page = new HtmlTemplate();
            $page->html = $config['email_message_signup_confirm'];
            $page->SetParameter('CONFIRMATION_LINK', $confirmation_link);
            $page->SetParameter('USERNAME', "demo");
            $page->SetParameter('USER_ID', "1");
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('USER_FULLNAME', $test_to_name);
            $email_body = $page->CreatePageReturn($lang, $config, $link);

            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        if (isset($_POST['forgot-pass'])) {
            $page = new HtmlTemplate();
            $page->html = $config['email_sub_forgot_pass'];
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('USER_FULLNAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $forget_password_link = $config['site_url'] . "login?forgot=sd1213f1x1&r=21d1d2d12&e=12&t=1213231";
            $page = new HtmlTemplate();
            $page->html = $config['email_message_forgot_pass'];
            $page->SetParameter('FORGET_PASSWORD_LINK', $forget_password_link);
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('USER_FULLNAME', $test_to_name);
            $email_body = $page->CreatePageReturn($lang, $config, $link);

            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        if (isset($_POST['contact_us'])) {
            $page = new HtmlTemplate();
            $page->html = $config['email_sub_contact'];
            $page->SetParameter('CONTACT_SUBJECT', "Contact Email");
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('NAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_contact'];
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('NAME', $test_to_name);
            $page->SetParameter('CONTACT_SUBJECT', "Contact Email");
            $page->SetParameter('MESSAGE', "Test Message");
            $email_body = $page->CreatePageReturn($lang, $config, $link);

            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        if (isset($_POST['feedback'])) {
            $page = new HtmlTemplate();
            $page->html = $config['email_sub_feedback'];
            $page->SetParameter('FEEDBACK_SUBJECT', "Feedback Email");
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('NAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_feedback'];
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('NAME', $test_to_name);
            $page->SetParameter('PHONE', "1234567890");
            $page->SetParameter('FEEDBACK_SUBJECT', "Feedback Email");
            $page->SetParameter('MESSAGE', "Test Message");
            $email_body = $page->CreatePageReturn($lang, $config, $link);

            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        if (isset($_POST['report'])) {
            $page = new HtmlTemplate();
            $page->html = $config['email_sub_report'];
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('NAME', $test_to_name);
            $page->SetParameter('USERNAME', $test_to_name);
            $page->SetParameter('VIOLATION', $lang['ADVWEBSITE']);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_report'];
            $page->SetParameter('EMAIL', $test_to_email);
            $page->SetParameter('NAME', $test_to_name);
            $page->SetParameter('USERNAME', $test_to_name);
            $page->SetParameter('USERNAME2', "Violator Username");
            $page->SetParameter('VIOLATION', $lang['ADVWEBSITE']);
            $page->SetParameter('URL', $config['site_url'] . "ad/1");
            $page->SetParameter('DETAILS', "Violator Message details here");
            $email_body = $page->CreatePageReturn($lang, $config, $link);

            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        if (isset($_POST['ad_approve'])) {
            $item_title = "Advertise Title";
            $ad_link = $config['site_url'] . "ad/1";

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_ad_approve'];
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('SELLER_NAME', $test_to_name);
            $page->SetParameter('SELLER_EMAIL', $test_to_email);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_ad_approve'];;
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('SELLER_NAME', $test_to_name);
            $page->SetParameter('SELLER_EMAIL', $test_to_email);
            $email_body = $page->CreatePageReturn($lang, $config, $link);

            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        if (isset($_POST['re_ad_approve'])) {
            $item_title = "Advertise Title";
            $ad_link = $config['site_url'] . "ad/1";

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_re_ad_approve'];
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('SELLER_NAME', $test_to_name);
            $page->SetParameter('SELLER_EMAIL', $test_to_email);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_re_ad_approve'];;
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('SELLER_NAME', $test_to_name);
            $page->SetParameter('SELLER_EMAIL', $test_to_email);
            $email_body = $page->CreatePageReturn($lang, $config, $link);
            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        if (isset($_POST['contact_to_seller'])) {
            $item_title = "Advertise Title";
            $ad_link = $config['site_url'] . "ad/1";

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_contact_seller'];
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('SELLER_NAME', $test_to_name);
            $page->SetParameter('SELLER_EMAIL', $test_to_email);
            $page->SetParameter('SENDER_NAME', "Sender Name");
            $page->SetParameter('SENDER_EMAIL', "sender@gmail.com");
            $page->SetParameter('SENDER_PHONE', "1234567890");
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_contact_seller'];;
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('SELLER_NAME', $test_to_name);
            $page->SetParameter('SELLER_EMAIL', $test_to_email);
            $page->SetParameter('SENDER_NAME', "Sender Name");
            $page->SetParameter('SENDER_EMAIL', "sender@gmail.com");
            $page->SetParameter('SENDER_PHONE', "1234567890");
            $page->SetParameter('MESSAGE', "Test Message : I want to inquiry about your classified.");
            $email_body = $page->CreatePageReturn($lang, $config, $link);
            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        if (isset($_POST['ad_newsletter'])) {
            $item_title = "Advertise Title";
            $ad_link = $config['site_url'] . "ad/1";
            $ad_id = 1;

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_post_notification'];
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('ADID', $ad_id);
            $email_subject = $page->CreatePageReturn($lang, $config, $link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_post_notification'];;
            $page->SetParameter('ADTITLE', $item_title);
            $page->SetParameter('ADLINK', $ad_link);
            $page->SetParameter('ADID', $ad_id);
            $email_body = $page->CreatePageReturn($lang, $config, $link);

            email($test_to_email, $test_to_name, $email_subject, $email_body);
        }

        $status = "success";
        $message = 'Email Sent Successfully';
    } else {
        $status = "Error";
        $message = 'Problem in sent e-mail.';
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addTestimonial()
{
    global $lang, $config;

    $title = escape_html($_POST['name']);
    $designation = escape_html($_POST['designation']);
    $image = null;
    $description = escape_html($_POST['content']);
    $error = array();

    if (empty($title)) {
        $error[] = "Name is required.";
    }
    if (empty($designation)) {
        $error[] = "Designation is required.";
    }
    if (empty($description)) {
        $error[] = "Content is required.";
    }

    if (empty($error)) {
        if (!empty($_FILES['image'])) {
            $file = $_FILES['image'];
            // Valid formats
            $valid_formats = array("jpeg", "jpg", "png");
            $filename = $file['name'];
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            if (!empty($filename)) {
                //File extension check
                if (in_array($ext, $valid_formats)) {
                    $main_path = "../storage/testimonials/";
                    $filename = uniqid(time()) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $main_path . $filename)) {
                        $image = $filename;
                        resizeImage(100, $main_path . $filename, $main_path . $filename);
                    } else {
                        $error[] = 'Unexpected error, please try again.';
                    }
                } else {
                    $error[] = 'Only jpeg, jpg & png files allowed.';
                }
            }
        }
    }

    if (empty($error)) {
        $test = ORM::for_table($config['db']['pre'] . 'testimonials')->create();
        $test->name = $title;
        $test->designation = $designation;
        $test->image = $image;
        $test->content = $description;
        $test->save();

        $status = "success";
        $message = $lang['SAVED_SUCCESS'];

        echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
        die();
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }
    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($error, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function editTestimonial()
{
    global $lang, $config;

    $title = escape_html($_POST['name']);
    $designation = escape_html($_POST['designation']);
    $image = null;
    $description = escape_html($_POST['content']);
    $error = array();

    if (empty($title)) {
        $error[] = "Name is required.";
    }
    if (empty($designation)) {
        $error[] = "Designation is required.";
    }
    if (empty($description)) {
        $error[] = "Content is required.";
    }

    if (empty($error)) {
        if (!empty($_FILES['image'])) {
            $file = $_FILES['image'];
            // Valid formats
            $valid_formats = array("jpeg", "jpg", "png");
            $filename = $file['name'];
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            if (!empty($filename)) {
                //File extension check
                if (in_array($ext, $valid_formats)) {
                    $main_path = "../storage/testimonials/";
                    $filename = uniqid(time()) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $main_path . $filename)) {
                        $image = $filename;
                        resizeImage(100, $main_path . $filename, $main_path . $filename);

                        // remove old image
                        $info = ORM::for_table($config['db']['pre'] . 'testimonials')
                            ->select('image')
                            ->find_one(escape_html($_POST['id']));

                        if ($info['image'] != "default.png") {
                            if (file_exists($main_path . $info['image'])) {
                                unlink($main_path . $info['image']);
                            }
                        }
                    } else {
                        $error[] = 'Unexpected error, please try again.';
                    }
                } else {
                    $error[] = 'Only jpeg, jpg & png files allowed.';
                }
            }
        }
    }

    if (empty($error)) {
        $test = ORM::for_table($config['db']['pre'] . 'testimonials')->find_one(escape_html($_POST['id']));
        $test->name = $title;
        $test->designation = $designation;
        if ($image) {
            $test->image = $image;
        }
        $test->content = $description;
        $test->save();

        $status = "success";
        $message = $lang['SAVED_SUCCESS'];

        echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
        die();
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }
    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($error, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

?>