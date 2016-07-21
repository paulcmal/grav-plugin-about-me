<?php

namespace Grav\Plugin;

interface remoteUserBackend
{
    public function __construct($config = []);
    public function getUser($identifier, $config = []);
}

interface remoteUserDB extends remoteUserBackend
{
    public function getUsers($config = []);
}

?>
