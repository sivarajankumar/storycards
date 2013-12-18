<?php
/**
 * Created by IntelliJ IDEA.
 * User: kbatchelor
 * Date: 10/16/13
 * Time: 4:02 PM
 */
include_once("dbconnection.php");
include_once("../pdo/user.php");
include_once("../pdo/session.php");

if ($_REQUEST['compassword']) {
    register($_REQUEST['username'], $_REQUEST['password'], $_REQUEST['compassword']);

} else if ($_REQUEST['logout']) {
    logout($_REQUEST['logout'], $_REQUEST['token']);

} else {
    userLogin($_REQUEST['username'], $_REQUEST['password']);
}

function register($username = null, $password = null, $compassword = null)
{
    $salt = "Zo4rU5Z1YyKJAASY0PT6EUg7BBYdlEhPaNLuxAwU8lqu1ElzHv0Ri7EM6irpx5w";
    if ($password == $compassword) {

        try {
            $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO users(username, password,type) VALUES(:username, :password,'10')";
            $stmt = $con->prepare($sql);
            $stmt->bindValue("username", $username, PDO::PARAM_STR);
            $stmt->bindValue("password", hash("sha256", $password . $salt), PDO::PARAM_STR);
            $stmt->execute();

            $sql = "INSERT INTO features(username, voteforcard,loadcards) VALUES(:username, '1','1')";
            $stmt = $con->prepare($sql);
            $stmt->bindValue("username", $username, PDO::PARAM_STR);
            $stmt->execute();

            echo "[{\"type\":\"success\",\"msg\":\"Registration successful proceed to login.\"}]";
        } catch (PDOException $e) {
            echo "[{\"type\":\"error\",\"msg\":\"This user name is in use.\"}]";
        }
    } else {
        echo "[{\"type\":\"error\",\"msg\":\"Your passwords do not match.\"}]";
    }
}

function loadUser($username)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM users WHERE username =:username";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_BOTH);
        $user = new User($row[0], $row[1], $row[2], $row[3], $row[4], $row[5]);
        return $user;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

function userLogin($username = null, $password = null)
{
    try {
        $salt = "Zo4rU5Z1YyKJAASY0PT6EUg7BBYdlEhPaNLuxAwU8lqu1ElzHv0Ri7EM6irpx5w";
        $pw=hash("sha256", $password . $salt);
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM users WHERE username = :username AND password = :password LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->bindValue("password",$pw , PDO::PARAM_STR);
        $stmt->execute();
        $valid = $stmt->fetchColumn();
        if ($valid) {
            startLogin($username);
        } else {
            echo "[{\"type\":\"error\",\"msg\":\"Incorrect username or password.\"}]";
        }
    } catch (PDOException $e) {
        echo "{Error:{That user name is already in use.}}";
    }
}

function startLogin($username)
{
    try{
    if (checkSession($username)) {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date = new DateTime();
        $token = hash("sha256", $date->format('Y-m-d H:i:s'));
        $sql = "UPDATE userssession SET token = '$token' WHERE username = :username";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $session = loadSession($username);
        echo $session->getJSON();
    } else {
        // create a new token
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date = new DateTime();
        $token = hash("sha256", $date->format('Y-m-d H:i:s'));
        $sql = "INSERT INTO userssession(username,token)  VALUES (:username,'$token')";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $session = loadSession($username);
        echo $session->getJSON();
    }
    } catch(PDOException $e){
        $e->getMessage();
    }
}

function loadSession($username)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql1 = "SELECT * FROM features WHERE username =:username";
        $stmt1 = $con->prepare($sql1);
        $stmt1->bindValue("username", $username, PDO::PARAM_INT);
        $stmt1->execute();
        $row1 = $stmt1->fetch(PDO::FETCH_BOTH);
        $sql = "SELECT * FROM userssession WHERE username =:username";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_BOTH);
        $session = new Session($row[0], $row[1], $row[2], $row[3], $row1['createcard'],$row1['voteforcard'],$row1['loadcards']);
        return $session;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

function checkSession($username)
{
    $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM userssession WHERE username =:username";
    $stmt = $con->prepare($sql);
    $stmt->bindValue("username", $username, PDO::PARAM_INT);
    $stmt->execute();
    $valid = $stmt->fetchColumn();
    return $valid;
}

function logout($username, $token)
{
    $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "DELETE FROM userssession WHERE username =:username AND token =:token";
    $stmt = $con->prepare($sql);
    $stmt->bindValue("username", $username, PDO::PARAM_INT);
    $stmt->bindValue("token", $token, PDO::PARAM_INT);
    $stmt->execute();
}

