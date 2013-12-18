<?php
include_once('../pdo/features.php');
include_once('dbconnection.php');

function CheckRights($username, $feature)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT $feature FROM features WHERE username = :username AND $feature=1";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $access = $stmt->fetchColumn(0);
        if ($access) {
            $valid = true;
            return $valid;
        } else {
            $valid = false;
            return $valid;
        }
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

function checkToken($username, $token)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM userssession WHERE username =:username AND token =:token";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_INT);
        $stmt->bindValue("token", $token, PDO::PARAM_INT);
        $stmt->execute();
        $valid = $stmt->fetchColumn();
        return $valid;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}