<?php
/**
 * Bootstrap file for the integration test suite.
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

// Load WordPress.
require $_tests_dir . '/includes/bootstrap.php';
