# WP Filesystem Mock

Provides a class that can be used as a mock filesystem, and also a shim for the 
WordPress filesystem API that uses it. This is useful in unit tests that include
simple filesystem operations.

## Requirements

- **PHP**: 5.2.17+
- **WordPress**: 3.9+

It will probably work with earlier versions of WordPress as well, but that is all 
that I have tested.

Note that WordPress is actually not required, and you can use the filesystem mocker
without it (you just won't be able to use the extension of WordPress's filesystem
API).

## Installation

You may install with composer:

```bash
composer require --dev jdgrimes/wp-filesystem-mock:~0.1
```

## Usage

If you aren't using auto-loading, the first thing you need to do is load the thing:

```php
		/**
		 * WordPress's base filesystem API class.
		 *
		 * We need to make sure this is loaded before we can load the 
		 */
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );

		/**
		 * The filesystem API shim that uses mock filesystems.
		 */
		require_once( MY_TESTS_DIR . '/../../vendor/jdgrimes/wp-filesystem-mock/src/wp-filesystem-mock.php' );

		/**
		 * The mock filesystem class.
		 */
		require_once( MY_TESTS_DIR . '/../../vendor/jdgrimes/wp-filesystem-mock/src/wp-mock-filesystem.php' );

```

To use the mock filesystem in your tests, just add this code (e.g., in your PHPUnit
testcase's `setUp()` method):

```php
		// Creating a new mock filesystem.
		// We assign it to a member property so we can access it later.
		$this->mock_fs = new WP_Mock_Filesystem;
		
		// Create the /wp-content directory.
		// This part is optional, and you'll do more or less setup here depending on
		// what you are testing.
		$this->mock_fs->mkdir_p( WP_CONTENT_DIR );

		// Tell the WordPress filesystem API shim to use this mock filesystem.
		WP_Filesystem_Mock::set_mock( $this->mock_fs );
		
		// Tell the shim to start overriding whatever other filesystem access method
		// is in use.
		WP_Filesystem_Mock::start();
```

For a full view of what the it can do, check the source.
