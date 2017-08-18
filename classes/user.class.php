<?php

/**
 * Created by PhpStorm.
 * User: thomasthaens
 * Date: 12/07/17
 * Time: 15:47
 */


class User{

    private $id;
    private $firstname;
    private $lastname;
    private $username;
    private $email;
    private $password;


    // Getters en setters
    //------------------------------------------------------- Id
    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    //------------------------------------------------------- Email
    public function getEmail(){
        return $this->email;
    }

    public function setEmail($email){
        $this->email = $email;
    }

    //--------------------------------------------------------- Firstname
    public function getFirstname(){
        return $this->firstname;
    }

    public function setFirstname($firstname){
        $this->firstname = $firstname;
    }

    //--------------------------------------------------------- Lastname
    public function getLastname(){
        return $this->lastname;
    }

    public function setLastname($lastname){
        $this->lastname = $lastname;
    }

    //--------------------------------------------------------- Username
    public function getUsername(){
        return $this->username;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    //--------------------------------------------------------- Password
    public function getPassword(){
        return $this->password;
    }

    public function setPassword($password){
        $this->password = $password;
    }




    // Functions
    //--------------------------------------------------------- Save Users

    public function Save(){
        session_start();
        $conn = Db::getInstance();

        $statement = $conn->prepare("INSERT INTO users (firstname, lastname, username, email, password) VALUES (:firstname, :lastname, :username, :email, :password);");
        $statement->bindValue(":firstname", $this->getFirstname());
        $statement->bindValue(":lastname", $this->getLastname());
        $statement->bindValue(":username", $this->getUsername());
        $statement->bindValue(":email", $this->getEmail());
        $statement->bindValue(":password", $this->getPassword());

        $statement->execute();
        $_SESSION['id'] = $conn->lastInsertId();
    }


    //--------------------------------------------------------- Save Admins

    public function SaveAdmin(){

        $admin = "1";

        $conn = Db::getInstance();

        $statement = $conn->prepare("INSERT INTO users (username, email, password, admin) VALUES (:username, :email, :password, :admin);");
        $statement->bindValue(":username", $this->getUsername());
        $statement->bindValue(":email", $this->getEmail());
        $statement->bindValue(":password", $this->getPassword());
        $statement->bindValue(":admin", $admin);

        $statement->execute();
        $_SESSION['id'] = $conn->lastInsertId();
    }


    //--------------------------------------------------------- Register Users

    public function Register(){
        if(!empty($_POST)  &&$_POST['firstname']!=''  &&$_POST['lastname']!=''  &&$_POST['username']!=''  &&$_POST['email']!='' ){
            $conn = Db::getInstance();
            $stmt = $conn->prepare('SELECT COUNT(email) AS EmailCount FROM users WHERE email = :email');
            $stmt->execute(array('email' => $_POST['email']));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['EmailCount'] == 0) {

                $conn = Db::getInstance();
                $stmt = $conn->prepare('SELECT COUNT(username) AS UsernameCount FROM users WHERE username = :username');
                $stmt->execute(array('username' => $_POST['username']));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['UsernameCount'] == 0) {

                    if(strlen($_POST["password"]) >= '6'){

                        $email = $_POST['email'];
                        $firstname = $_POST['firstname'];
                        $lastname = $_POST['lastname'];
                        $username = $_POST['username'];
                        $options = [
                            'cost'=>14,
                        ];
                        $password = password_hash($_POST["password"],PASSWORD_DEFAULT,$options);

                        $user = new User();
                        $user->setEmail($email);
                        $user->setFirstname($firstname);
                        $user->setLastname($lastname);
                        $user->setUsername($username);
                        $user->setPassword($password);
                        $user->Save();

                        session_start();
                        $_SESSION["user"] = $username;
                        header("Location: timeline.php");
                    }

                    else {
                        throw new Exception("Password should be at least 6 characters long");
                    }
                }

                else {
                    throw new Exception("Username already exists");
                }
            }

            else {
                throw new Exception("Email already exists");            }
        }

        else{
            throw new Exception("Please fill in the empty fields");
        }
    }






    //--------------------------------------------------------- Login User

    public function Login(){
        // Form completed?
        if(!empty($_POST)){

            $conn = Db::getInstance();
            $email = $_POST['email'];
            $password = $_POST['password'];

            $var = $conn->prepare("SELECT * FROM users WHERE email = :email;");
            $var->bindParam(':email', $email);
            $var->execute();
            $res = $var->fetch();

            if(password_verify($password, $res['password'])){
                // OK
                session_start();
                $_SESSION['user'] = $res['username'];
                $_SESSION['id'] = $res['id'];
                header("Location: timeline.php");
            }

            // IS EMAIL GEVULD?
            else if(empty($email)){
                throw new Exception("Please fill in your e-mail");
            }

            // IS PASSWORD GEVULD?
            else if(empty($password)){
                throw new Exception("Please fill in your password");
            }

            else {
                throw new Exception("Your e-mail and password do not match.");

            }

        }

    }



    //--------------------------------------------------------- Check If Admin

    public function CheckAdmin(){

        $conn = Db::getInstance();

        $var = $conn->prepare("SELECT admin FROM users WHERE username = :username;");
        $var->bindParam(':username', $_SESSION['user']);
        $var->execute();
        $res = $var->fetch();

        if(empty($res['admin'])){

            return false;
        }
        else{

            return true;
        }
    }






    //--------------------------------------------------------- Register Admin

    public function RegisterAdmin(){
        if(!empty($_POST) &&$_POST['username']!=''  &&$_POST['email']!='' &&$_POST['password']!=''){
            $conn = Db::getInstance();
            $stmt = $conn->prepare('SELECT COUNT(email) AS EmailCount FROM users WHERE email = :email');
            $stmt->execute(array('email' => $_POST['email']));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['EmailCount'] == 0) {

                $conn = Db::getInstance();
                $stmt = $conn->prepare('SELECT COUNT(username) AS UsernameCount FROM users WHERE username = :username');
                $stmt->execute(array('username' => $_POST['username']));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['UsernameCount'] == 0) {

                    if(strlen($_POST["password"]) >= '6'){

                        $email = $_POST['email'];
                        $username = $_POST['username'];
                        $options = [
                            'cost'=>14,
                        ];
                        $password = password_hash($_POST["password"],PASSWORD_DEFAULT,$options);

                        $user = new User();
                        $user->setEmail($email);
                        $user->setUsername($username);
                        $user->setPassword($password);
                        $user->SaveAdmin();

                        header("Location: timeline.php");
                    }

                    else {
                        throw new Exception("Password should be at least 6 characters long");
                    }
                }

                else {
                    throw new Exception("Username already exists");
                }
            }

            else {
                throw new Exception("Email already exists");            }
        }

        else{
            throw new Exception("Please fill in the empty fields");
        }
    }
















}