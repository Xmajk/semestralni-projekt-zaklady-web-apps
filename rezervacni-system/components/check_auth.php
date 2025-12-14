<?php

use components\objects\User;
require_once __DIR__."/utils/links.php";
require_once __DIR__."/objects/User.php";

/**
 * Checks for the existence of authentication cookies.
 *
 * Verifies if the 'username' and 'user_id' cookies are set in the current request.
 *
 * @return bool True if both cookies exist, false otherwise.
 */
function check_cookies()
{
    return (isset($_COOKIE['username']) && isset($_COOKIE['user_id']));
}

/**
 * Validates the user credentials against the database.
 *
 * This function uses the User class to verify that the provided username
 * matches the provided user ID in the system records.
 *
 * @param string $username The username to verify.
 * @param int|string $user_id The user ID to verify.
 * @return bool True if the combination is valid, false otherwise.
 * @throws Exception If the database query fails.
 */
function check_user_cred($username, $user_id)
{
    return User::check_combination($user_id,$username);
}

/**
 * Enforces that a user is currently logged in.
 *
 * This function checks for valid cookies and verifies the credentials.
 * If authentication fails, the user is immediately redirected to the login page
 * and script execution is terminated.
 *
 * @return bool|void Returns true if authenticated; otherwise redirects and exits.
 */
function check_auth_user()
{
    try{
        if(check_cookies() && check_user_cred($_COOKIE["username"], $_COOKIE["user_id"])){
            return true;
        }
        header("Location: ".createLink("/login.php"));
        exit();
    }catch (Exception $e){
        redirectToDatabaseError();
    }
}

/**
 * Enforces that the current user has administrator privileges.
 *
 * First ensures the user is logged in via {@see check_auth_user()}.
 * Then retrieves the user profile to check the admin status.
 * If the user is not an admin, they are redirected to the home page.
 *
 * @return bool|void Returns true if the user is an admin; otherwise redirects and exits.
 */
function check_auth_admin()
{
    if(check_auth_user()){
        $user_id = $_COOKIE["user_id"];
        $user = null;
        try{
            $user = User::getUserById($user_id);
        }catch (Exception $e){
            redirectToDatabaseError();
        }
        if($user == null){redirectToDatabaseError();}
        if($user->is_admin){
            return true;
        }
    }
    header("Location: ".createLink("/index.php"));
    exit();
}