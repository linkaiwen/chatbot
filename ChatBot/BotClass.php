<?php
require_once 'UserClass.php';

class Bot {
    public $user;
    function __construct(){
        $this->user = new User();
    }

}


