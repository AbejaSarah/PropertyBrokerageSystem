<?php

if(checkloggedin())
{
    header("Location: ".$config['site_url']."dashboard");
    exit;
}


if(isset($_POST['forgot']))
{
    $_GET['forgot'] = escape_html($_POST['forgot']);
}
if(isset($_POST['r']))
{
    $_GET['r'] = escape_html($_POST['r']);
}
if(isset($_POST['e']))
{
    $_GET['e'] = escape_html($_POST['e']);
}
if(isset($_POST['t']))
{
    $_GET['t'] = escape_html($_POST['t']);
}
if(isset($_POST['type']))
{
    $_GET['type'] = escape_html($_POST['type']);
}

if(isset($_GET['ref']))
{
    $_GET['ref'] = htmlentities(escape_html($_GET['ref']));
}
if(isset($_POST['ref']))
{
    $_POST['ref'] = htmlentities(escape_html($_POST['ref']));
}

if(isset($_GET['r']))
{
    $_GET['r'] = htmlentities(escape_html($_GET['r']));
}
if(isset($_POST['r']))
{
    $_POST['r'] = htmlentities(escape_html($_POST['r']));
}

if(isset($_GET['t']))
{
    $_GET['t'] = htmlentities(escape_html($_GET['t']));
}
if(isset($_POST['r']))
{
    $_POST['t'] = htmlentities(escape_html($_POST['t']));
}

if(isset($_GET['e']))
{
    $_GET['e'] = htmlentities(escape_html($_GET['e']));
}
if(isset($_POST['r']))
{
    $_POST['e'] = htmlentities(escape_html($_POST['e']));
}

// Check if they are using a forgot password link
if(isset($_GET['forgot']))
{
    if(!isset($_GET['start']))
    {
        $check_forgot1 = ORM::for_table($config['db']['pre'].'user')
            ->select_many('id', 'forgot', 'username')
            ->where('email', escape_html($_GET['e']))
            ->find_one();

        if($_GET['forgot'] == $check_forgot1->forgot)
        {
            if($_GET['forgot'] == md5(escape_html($_GET['t']).'_:_'.escape_html($_GET['r']).'_:_'.escape_html($_GET['e'])))
            {
                // Check that the link hasn't timed out (30 minutes old)
                if($_GET['t'] > (time()-108000))
                {
                    $forgot_error = '';

                    if(isset($_POST['password']))
                    {
                        if( (strlen($_POST['password']) < 4) OR (strlen($_POST['password']) > 16) )
                        {
                            $forgot_error = $lang['PASSCHAR'];
                        }
                        else
                        {
                            if($_POST['password'] == $_POST['password2'])
                            {
                                $password = $_POST["password"];
                                $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);

                                $forgot_update = ORM::for_table($config['db']['pre'].'user')->find_one($check_forgot1->id);
                                $forgot_update->set('forgot', '');
                                $forgot_update->set('password_hash', $pass_hash);
                                $forgot_update->save();

                                message($lang['SUCCESS'],$lang['PASSCHANGED'], 'login');
                                exit;
                            }
                            else
                            {
                                $forgot_error = $lang['PASSNOMATCH'];
                            }
                        }
                    }

                    $page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/forgot.tpl");
                    $page->SetParameter ('FIELD_FORGOT',escape_html($_GET['forgot']));
                    $page->SetParameter ('FIELD_R',escape_html($_GET['r']));
                    $page->SetParameter ('FIELD_E',escape_html($_GET['e']));
                    $page->SetParameter ('FIELD_T',escape_html($_GET['t']));
                    $page->SetParameter ('USERNAME',$check_forgot1->username);
                    $page->SetParameter ('FORGOT_ERROR',$forgot_error);
                    if(isset($_SESSION['user']['id']))
                    {
                        $page->SetParameter ('LOGGEDIN', 1);
                    }
                    else
                    {
                        $page->SetParameter ('LOGGEDIN', 0);
                    }
                    $page->SetParameter ('OVERALL_HEADER', create_header($lang['LOGIN']));
                    $page->SetParameter ('OVERALL_FOOTER', create_footer());
                    $page->CreatePageEcho();
                    exit;
                }
                else
                {
                    $login_error = $lang['FORGOTEXP'];
                }
            }
            else
            {
                $login_error = $lang['FORGOTINV'];
            }
        }
        else
        {
            $login_error = $lang['FORGOTINV'];
        }
    }

    $page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/login.tpl");
    $page->SetParameter ('ERROR',$login_error);
    if(isset($_SESSION['user']['id']))
    {
        $page->SetParameter ('LOGGEDIN', 1);
    }
    else
    {
        $page->SetParameter ('LOGGEDIN', 0);
    }
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['LOGIN']));
    $page->SetParameter ('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
    exit;
}

// Check if they are trying to retrieve their email
if(isset($_POST['email']))
{
    // Lookup the email address
    $email_info1 = check_account_exists(escape_html($_POST['email']));

    // Check if the email address exists
    if($email_info1 != 0)
    {
        $email_userid = get_user_id_by_email(escape_html($_POST['email']));
        // Send the email
        send_forgot_email(escape_html($_POST['email']),$email_userid);

        $page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/forgot_form.tpl");
        $page->SetParameter ('SUCCESS',$lang['CHECKEMAILFORGOT']);
        $page->SetParameter ('LOGIN_ERROR','');
        if(isset($_SESSION['user']['id']))
        {
            $page->SetParameter ('LOGGEDIN', 1);
        }
        else
        {
            $page->SetParameter ('LOGGEDIN', 0);
        }
        $page->SetParameter ('OVERALL_HEADER', create_header($lang['LOGIN']));
        $page->SetParameter ('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
        exit;
    }
    else
    {
        $page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/forgot_form.tpl");
        $page->SetParameter ('LOGIN_ERROR',$lang['EMAILNOTEXIST']);
        $page->SetParameter ('SUCCESS',"");
        if(isset($_SESSION['user']['id']))
        {
            $page->SetParameter ('LOGGEDIN', 1);
        }
        else
        {
            $page->SetParameter ('LOGGEDIN', 0);
        }
        $page->SetParameter ('OVERALL_HEADER', create_header($lang['LOGIN']));
        $page->SetParameter ('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
        exit;
    }
}

if(isset($_GET['fstart']))
{
    $page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/forgot_form.tpl");
    $page->SetParameter ('LOGIN_ERROR','');
    $page->SetParameter ('SUCCESS','');
    if(isset($_SESSION['user']['id']))
    {
        $page->SetParameter ('LOGGEDIN', 1);
    }
    else
    {
        $page->SetParameter ('LOGGEDIN', 0);
    }
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['LOGIN']));
    $page->SetParameter ('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();

    exit;
}



if(!isset($_POST['submit']))
{
    if(!isset($_GET['ref']))
    {
        $_GET['ref'] = 'dashboard';
    }


    $page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/login.tpl");
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['LOGIN']));
    $page->SetParameter ('REF', escape_html($_GET['ref']));
    $page->SetParameter ('ERROR', '');
    $page->SetParameter ('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
}
else
{
    $loggedin = userlogin(escape_html($_POST['username']), $_POST['password']);

    if(!is_array($loggedin))
    {
        $page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/login.tpl");
        $page->SetParameter ('OVERALL_HEADER', create_header($lang['LOGIN']));
        $page->SetParameter ('ERROR', $lang['USERNOTFOUND']);
        $page->SetParameter ('REF', $_POST['ref']);
        $page->SetParameter ('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
    }
    elseif($loggedin['status'] == 2)
    {
        $page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/login.tpl");
        $page->SetParameter ('OVERALL_HEADER', create_header($lang['LOGIN']));
        $page->SetParameter ('ERROR', $lang['ACCOUNTBAN']);
        $page->SetParameter ('REF', $_POST['ref']);
        $page->SetParameter ('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
    }
    else
    {
        create_user_session($loggedin['id'],$loggedin['username'],$loggedin['password']);
        update_lastactive();

        if(!isset($_GET['ref']))
        {
            $_GET['ref'] = $link['DASHBOARD'];
        }

        header("Location: " . escape_html($_GET['ref']));
    }
}
?>