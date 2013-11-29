<?php
/**
 * Created by IntelliJ IDEA.
 * User: kbatchelor
 * Date: 11/15/13
 * Time: 4:51 PM
 * To change this template use File | Settings | File Templates.
 */

class Features
{

    public $username;
    public $createcard;
    public $voteforcard;
    public $loadcards;
    public $rightsedit;

    function __construct($id = null, $username = null, $createcard = null, $voteforcard = null, $loadcards = null, $rightsedit=null)
    {
        $this->id = $id;
        $this->username = $username;
        $this->createcard = $createcard;
        $this->voteforcard = $voteforcard;
        $this->loadcards = $loadcards;
        $this->rightsedit = $rightsedit;
    }

    function getJSON()
    {
        return '{"Feature": ' . json_encode($this) . '}';
    }

}