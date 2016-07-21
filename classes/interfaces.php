<?php

namespace Grav\Plugin;

/*
    remoteUserBackend
        Interface to fetch or generate on the go an aboutMeUser object
*/
interface remoteUserBackend
{
    public function __construct($config = []);
    public function getUser($identifier, $config = []);
}

/*
    remoteUserDB
        Interface to act as a backend to fetch info about all active Grav authors
*/
interface remoteUserDB extends remoteUserBackend
{
    public function getUsers($config = []);
    public function addUser($identifier = '', $config = []);
}

?>
