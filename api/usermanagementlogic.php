<?php

include_once('dbconnection.php');
include_once('checkrights.php');

if (checkToken($_REQUEST['username'], $_REQUEST['token'])) {

    if ($_REQUEST['rightname']) {
        $feature = "rightsedit";
        if (CheckRights($_REQUEST['username'], $feature)) {
            grantRight($_REQUEST['thisuser'], $_REQUEST['rightname'], $_REQUEST['value']);
        } else {
            echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You do not have access to this feature.\"}]}";
        }

    } else {
        $feature = "rightsedit";
        if (CheckRights($_REQUEST['username'], $feature)) {
            loadFeatures();
        } else {
            echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You do not have access to this feature.\"}]}";
        }
    }

} else {
    echo "[{\"error\":\"You are not logged in. Log in to view this data.\"}]";
}


function loadFeatures()
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM features";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_BOTH)) {
            $features = new Features($row[0], $row[1], $row[2], $row[3], $row[4], $row[5]);
            $userrights[] = $features;
        }
        if ($stmt->rowCount()) {
            echo "{\"userrights\":" . json_encode($userrights) . "}";
//            echo json_encode($userrights);
        } else {
            echo "[{\"error\":\"No users.\"}]";
        }
    } catch (PDOException $e) {
        $e->getMessage();
    }
}

function grantRight($username, $rightname, $value)
{
    try {
        echo $value;
        echo $username;
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "UPDATE features SET $rightname = :value WHERE username = :username";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->bindValue("value", $value, PDO::PARAM_STR);
        $stmt->execute();
        $sql = "SELECT $rightname AS 'rightname' FROM features WHERE username = :username";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_BOTH);
        $array = array('username' => $username, 'value' => $row['rightname']);
//        echo "{\"users\":" . json_encode($array) . "}";
        echo json_encode($array);
    } catch (PDOException $e) {
        $e->getMessage();
    }
}