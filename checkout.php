<?php
session_start();
include('databasecon.php');
$db = new DatabaseCon();
$val=$_SESSION['uid'];
