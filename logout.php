<?php
session_start();
require_once 'layout/config.php';
require_once 'layout/functions.php';

(new Auth())->logout();
