<?php
namespace components\objects;

require_once __DIR__."/../dbconnector.php";

class User
{
    public $id;
    public $username;
    public $password;
    public $email;
    public $is_admin;

    static function check_credentials($username, $password)
    {
        return false;
    }

    static function check_combination($id,$username){
        $sql = "SELECT * FROM users WHERE username = '$username' AND id = $id LIMIT 1";
        $conn = connect();
        $result = $conn->query($sql);
        return $result->num_rows > 0;
    }

    static function getUserById($id)
    {
        try {
            $conn = connect();

            $sql = "SELECT * FROM users WHERE id = $id LIMIT 1";
            $result = $conn->query($sql);

            if (!$result || $result->num_rows === 0) {
                return null;
            }

            $row = $result->fetch_assoc();

            $user = new User();
            $user->id = $row['id'];
            $user->username = $row['username'];
            $user->password = $row['password'];
            //$user->email = $row['email'];
            $user->is_admin = isset($row['is_admin']) ? (bool)$row['is_admin'] : false;

            return $user;
        } catch (\Throwable $e) {
            return null;
        }
    }

}