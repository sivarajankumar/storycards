<?php
/**
 * Created by IntelliJ IDEA.
 * User: kbatchelor
 * Date: 9/27/13
 * Time: 4:46 PM
 */
include_once('../pdo/card.php');
include_once('../pdo/features.php');
include_once('dbconnection.php');
include_once('checkrights.php');
// access logic

if (checkToken($_REQUEST['username'], $_REQUEST['token'])) {
    if ($_REQUEST['name']) {
        $feature = "createcard";
        if (CheckRights($_REQUEST['username'], $feature)) {
            createCard($_REQUEST['name'], $_REQUEST['description'], $_REQUEST['username'], '10',$_REQUEST['owner']);
        } else {
//            echo "{\"error\":\"You do not have access to this feature.\"}";
            echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You do not have access to this feature.\"}]}";
        }

    } else if ($_REQUEST['loadmycard']) {
        loadMyCards($_REQUEST['loadmycard']);

    } else if ($_REQUEST['vote']) {
        $feature = "voteforcard";
        if (CheckRights($_REQUEST['username'], $feature)) {
            voteForCard($_REQUEST['thiscard'], $_REQUEST['username']);
        } else {
            echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You do not have access to this feature.\"}]}";
        }
    } else if ($_REQUEST['unvote']) {
        $feature = "voteforcard";
        if (CheckRights($_REQUEST['username'], $feature)) {
            unVoteForCard($_REQUEST['thiscard'], $_REQUEST['username']);
        } else {
            echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You do not have access to this feature.\"}]}";
        }
    } else if ($_REQUEST['mark']) {
        $feature = "voteforcard";
        if (CheckRights($_REQUEST['username'], $feature)) {
            markCard($_REQUEST['thiscard'], $_REQUEST['username']);
        } else {
            echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You do not have access to this feature.\"}]}";
        }

    } else if ($_REQUEST['delete']) {
        $feature = "createcard";
        if (CheckRights($_REQUEST['username'], $feature)) {
            deleteCard($_REQUEST['delete']);
        } else {
            echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You do not have access to this feature.\"}]}";
        }
    } else if ($_REQUEST['edit']) {
        $feature = "createcard";
        if (CheckRights($_REQUEST['username'], $feature)) {
            editCard($_REQUEST['id'], $_REQUEST['cardname'], $_REQUEST['description']);
        } else {
            echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You do not have access to this feature.\"}]}";
        }
    } else {
        $feature = "loadcards";
        if (CheckRights($_REQUEST['username'], $feature)) {
            loadCards();
        } else {
            echo "{\"error\": [{ \"type\": \"error\", \"msg\":\"You do not have access to this feature.\"}]}";
        }
    }
} else {
    echo "[{\"error\":\"You are not logged in. Log in to view this data.\"}]";
}

// functions

function createCardOld($name = null, $description = null, $username, $statusId)
{
    if ($name) {
        $query = "INSERT INTO card (name, description, username, statusId, votes) VALUE ('" . $name . "','" . $description . "','" . $username . "','" . $statusId . "','1')";
        $con = getConnection();
        if ($con->query($query) === TRUE) {
            $card = loadCardCreated($con);
            echo "{\"cards\":" . json_encode($card) . "}";
        } else {
            echo "[{\"error\":\"failed to create card.\"}]";

        }
    }
}

function createCard($name, $description, $username, $statusId, $owner)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "INSERT INTO card (name, description, username, statusId, votes,owner) VALUES (:name,:description,:username,:statusid,'0',:owner)";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("name", $name, PDO::PARAM_STR);
        $stmt->bindValue("description", $description, PDO::PARAM_STR);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->bindValue("statusid", $statusId, PDO::PARAM_STR);
        $stmt->bindValue("owner", $owner, PDO::PARAM_STR);
        $stmt->execute();
        $card = loadCardCreated($con);
        echo "{\"cards\":" . json_encode($card) . "}";
    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }
}

function loadCardCreated($con)
{
    try {
        $query = "SELECT * FROM card WHERE id = " . $con->lastInsertId();
        $stmt = $con->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_BOTH);
        $card = new Card($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
        return $card;
    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }
}

function voteForCard($id, $username)
{
    if (checkVotes($username)) {
        try {
            $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "SELECT votes FROM card WHERE id =:id";
            $stmt = $con->prepare($sql);
            $stmt->bindValue("id", $id, PDO::PARAM_STR);
            $stmt->execute();
            $votes = $stmt->fetchColumn(0);
            $votes = $votes + 1;
            $sql = "UPDATE card SET votes = :votes WHERE id = :id";
            $stmt = $con->prepare($sql);
            $stmt->bindValue("id", $id, PDO::PARAM_STR);
            $stmt->bindValue("votes", $votes, PDO::PARAM_STR);
            $stmt->execute();
            addUserVote($id,$username);
            echo "{\"votes\":$votes}";
        } catch (PDOException $e) {
            echo json_encode($e->getMessage());
        }
    } else {
        echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You are out of votes.\"}]}";
    }
}



function unVoteForCard($id, $username)
{
    if (checkUnVotes($id,$username)) {
        try {
            $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "SELECT votes FROM card WHERE id =:id";
            $stmt = $con->prepare($sql);
            $stmt->bindValue("id", $id, PDO::PARAM_STR);
            $stmt->execute();
            $votes = $stmt->fetchColumn(0);
            $votes = $votes - 1;
            $sql = "UPDATE card SET votes = :votes WHERE id = :id";
            $stmt = $con->prepare($sql);
            $stmt->bindValue("id", $id, PDO::PARAM_STR);
            $stmt->bindValue("votes", $votes, PDO::PARAM_STR);
            $stmt->execute();
            removeUserVote($id,$username);
            echo "{\"votes\":$votes}";
        } catch (PDOException $e) {
            echo json_encode($e->getMessage());
        }
    } else {
        echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You have removed all votes.\"}]}";
    }
}

function checkVotes($username)
{
    try {

        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT COUNT(id) AS totalvotes FROM uservotes WHERE username =:username";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_INT);
        $stmt->execute();
        $valid = $stmt->fetchColumn();
//        $valid < 3
//        echo $valid;
        if ($valid < 3 ) {
            $valid = true;
            return $valid;
        } else {
            $valid = false;
            return $valid;
        }
    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }
}

function checkUnVotes($id,$username)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT COUNT(id) AS totalvotes FROM uservotes WHERE username =:username AND cardid = :id";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->bindValue("id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $valid = $stmt->fetchColumn();
        if ($valid > 0 ) {
            $valid = true;
            return $valid;
        } else {
            $valid = false;
            return $valid;
        }
    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }
}

function addUserVote($id,$username)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "INSERT INTO uservotes (username, cardid,votes) VALUES(:username,:id,'1')";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->bindValue("id", $id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }
}

function removeUserVote($id,$username)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM uservotes where username = :username AND cardid =:id LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->bindValue("id", $id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }
}

function loadCards()
{
    $query = "SELECT * FROM card WHERE id > 0";
    $con = getConnection();
    $cards = array();
    if ($result = $con->query($query)) {
        while ($row = $result->fetch_row()) {
            $card = new Card($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
            $cards[] = $card;
        }
        echo "{\"cards\":" . json_encode($cards) . "}";
        $result->close();
    }
}

function loadMyCards($loadmycard)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM card WHERE owner = :loadmycard";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("loadmycard", $loadmycard, PDO::PARAM_STR);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_BOTH)) {
            $card = new Card($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
            $cards[] = $card;
        }
        if ($stmt->rowCount()) {
            echo "{\"cards\":" . json_encode($cards) . "}";

        } else {
            echo "{\"error\": [{ \"type\": \"alert\", \"msg\":\"You have no cards assigned to you.\"}]}";
        }
    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }
}

function deleteCard($id)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM card WHERE id = :id";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("id", $id, PDO::PARAM_INT);
        $stmt->execute();

    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }

    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM uservotes WHERE cardid = :id";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("id", $id, PDO::PARAM_INT);
        $stmt->execute();

    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }

}

function editCard($id, $name, $description)
{
    try {
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "UPDATE card SET name = :name, description = :description WHERE id = :id";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("id", $id, PDO::PARAM_STR);
        $stmt->bindValue("name", $name, PDO::PARAM_STR);
        $stmt->bindValue("description", $description, PDO::PARAM_STR);
        $stmt->execute();
        echo "{\"error\": [{ \"type\": \"success\", \"msg\":\"Your changes have been saved.\"}]}";
    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }
}


function markCard($id,$username)
{
    try {
        // add a check for another mark on this card.
        $con = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "INSERT INTO userWatch (username, cardid) VALUES(:username,:id)";
        $stmt = $con->prepare($sql);
        $stmt->bindValue("username", $username, PDO::PARAM_STR);
        $stmt->bindValue("id", $id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        echo json_encode($e->getMessage());
    }
}
