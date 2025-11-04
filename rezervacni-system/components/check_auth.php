<?php

use components\objects\User;
require_once __DIR__."/utils/links.php";
require_once __DIR__."/objects/User.php";

function check_cookies()
{
    return (isset($_COOKIE['username']) && isset($_COOKIE['user_id']));
}
function check_user_cred($username, $user_id)
{
    return User::check_combination($user_id,$username);
}
function check_auth_user()
{
    if(check_cookies() && check_user_cred($_COOKIE["username"], $_COOKIE["user_id"])){
        return true;
    }
    header("Location: ".createLink("/login.php"));
    exit();
}

function check_auth_admin()
{
    if(check_auth_user()){
        $user_id = $_COOKIE["user_id"];
        $user = User::getUserById($user_id);
        if($user->is_admin){
            return true;
        }
    }
    header("Location: ".createLink("/index.php"));
    exit();
}