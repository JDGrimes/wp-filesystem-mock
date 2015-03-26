<?php

/**
 * PHPUnit bootstrap.
 *
 * @package WP_Mock_Filesystem
 * @since 0.1.0
 */

if ( ! getenv( 'WP_TESTS_DIR' ) ) {
	exit( 'WP_TESTS_DIR is not set.' . PHP_EOL );
}

/**
 * The mock filesystem.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/../../../src/wp-mock-filesystem.php' );

/**
 * The base filesystem class.
 *
 * @since 0.1.0
 */
require_once( getenv( 'WP_TESTS_DIR' ) . '/../../src/wp-admin/includes/class-wp-filesystem-base.php' );

/**
 * The WordPress error API class.
 *
 * This is a dependency of WP_Filesystem_Mock::__construct().
 *
 * @since 0.1.0
 */
require_once( getenv( 'WP_TESTS_DIR' ) . '/../../src/wp-includes/class-wp-error.php' );

/**
 * The WP filesystem class implementing the mock filesystem.
 *
 * @since 0.1.0
 */
require_once( dirname( __FILE__ ) . '/../../../src/wp-filesystem-mock.php' );

if ( ! defined( 'FS_CHMOD_DIR' ) ) {

	/**
	 * @since 0.1.0
	 */
	define( 'FS_CHMOD_DIR', 0755 );
}

if ( ! defined( 'FS_CHMOD_FILE' ) ) {

	/**
	 * @since 0.1.0
	 */
	define( 'FS_CHMOD_FILE', 0644 );
}

// EOF
