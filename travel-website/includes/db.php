<?php

/**
 * Database Connection File
 * Provides database connection functionality for all pages
 */

// Database connection settings
$db_host = 'localhost';
$db_name = 'kuznikat';
$db_user_name = 'kuznikat';
$db_user_pass = 'webove aplikace';

/**
 * Get database connection (lazy loading - singleton pattern)
 * @return PDO|null Database connection or null on failure
 */
function get_db_connection()
{
    global $db_host, $db_name, $db_user_name, $db_user_pass;

    static $connection = null;

    // Return existing connection if available
    if ($connection !== null) {
        return $connection;
    }

    // Check if PDO is available
    if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
        error_log("PDO MySQL extension is not available on this server.");
        return null;
    }

    try {
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 10
        ];

        $connection = new PDO($dsn, $db_user_name, $db_user_pass, $options);
        return $connection;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    } catch (Exception $e) {
        error_log("General error in db.php: " . $e->getMessage());
        return null;
    } catch (Error $e) {
        error_log("Fatal error in db.php: " . $e->getMessage());
        return null;
    }
}

/**
 * Get database connection error message
 * @return string Error message or empty string if no error
 */
function get_db_error()
{
    global $db_host, $db_name, $db_user_name, $db_user_pass;

    // Check if PDO is available
    if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
        return "PDO MySQL extension is not available on this server.";
    }

    // Try to connect to get the actual error
    try {
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ];

        $test_conn = new PDO($dsn, $db_user_name, $db_user_pass, $options);
        return ''; // Connection successful
    } catch (PDOException $e) {
        return "Database connection failed: " . htmlspecialchars($e->getMessage());
    } catch (Exception $e) {
        return "An error occurred: " . htmlspecialchars($e->getMessage());
    } catch (Error $e) {
        return "A system error occurred: " . htmlspecialchars($e->getMessage());
    }
}
