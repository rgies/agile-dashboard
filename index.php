<?php
/**
 * Redirector
 *
 * Redirects to directory web for non vhost environments
 */
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1'))
)
    header('location: ' . dirname($_SERVER['PHP_SELF']) . '/web');
else
    header('location: ' . dirname($_SERVER['PHP_SELF']) . '/web/app_dev.php');
