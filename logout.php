<?php
require_once 'includes/session.php';

logoutUser();
header("Location: index.php");
exit();
?>