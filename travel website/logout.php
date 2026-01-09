<?php

/**
 * Logout page - Destroys user session
 */

// Include authentication functions
require_once __DIR__ . '/includes/auth.php';

// Logout user
logout_user();

// Redirect to home page
header('Location: home.php');
exit();
