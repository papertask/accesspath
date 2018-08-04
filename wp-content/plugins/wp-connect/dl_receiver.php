<?php
include "../../../wp-config.php";
@session_start();
$redirect_to = wp_connect_get_cookie('redirect_to');
if (!empty($redirect_to)) {
	$fuhao = (strpos($redirect_to, '?') > 0) ? '&' : '?';
	header('location:' . $redirect_to . $fuhao . $_SERVER['QUERY_STRING']);
	wp_connect_clear_cookie('redirect_to');
} elseif (isset($_GET['code']) || isset($_GET['oauth_token'])) {
	header('location:' . plugins_url('wp-connect/login.php?') . $_SERVER['QUERY_STRING']);
} 

?>