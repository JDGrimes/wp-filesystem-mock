<?php

/**
 * A class to extend the WordPress filesystem API to use the mock filesystem.
 *
 * @package WP_Filesystem_Mock
 * @since 0.1.0
 */

/**
 * Shim between WordPress filesystem API and the mock filesystem.
 *
 * Extends the WordPress filesystem API, converting it to use the mock filesystem
 * instead.
 *
 * @since 0.1.0
 */
class WP_Filesystem_Mock extends WP_Filesystem_Base {

	//
	// Static.
	//

	/**
	 * The mock filesystem.
	 *
	 * @since 0.1.0
	 *
	 * @var WP_Mock_Filesystem
	 */
	protected static $mock;

	/**
	 * Set the mock filesystem to use.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Mock_Filesystem $mock The mock filesystem object.
	 */
	public static function set_mock( $mock ) {
		self::$mock = $mock;
	}

	/**
	 * Start WordPress using the mock filesystem.
	 *
	 * @since 0.1.0
	 */
	public static function start() {
		add_filter( 'filesystem_method', array( __CLASS__, 'filesystem_method' ) );
	}

	/**
	 * Stop WordPress using the mock filesystem.
	 *
	 * @since 0.1.0
	 */
	public static function stop() {
		remove_filter( 'filesystem_method', array( __CLASS__, 'filesystem_method' ) );
	}

	/**
	 * Get the filesystem method.
	 *
	 * @since 0.1.0
	 *
	 * @WordPress\filter filesystem_method Managed by self::start() and self::stop().
	 */
	public static function filesystem_method() {
		return 'mock';
	}

	//
	// Non-static.
	//

	/**
	 * @since 0.1.0
	 */
	public function __construct( $arg ) {

		$this->method = 'mock';
		$this->errors = new WP_Error();

		if ( ! isset( self::$mock ) ) {
			self::$mock = new WP_Mock_Filesystem();
		}
	}

	/**
	 * @since 0.1.0
	 */
	public function get_contents( $file ) {
		return self::$mock->get_file_attr( $file, 'contents', 'file' );
	}

	/**
	 * @since 0.1.0
	 */
	public function get_contents_array( $file ) {

		$contents = $this->get_contents( $file, 'contents', 'file' );

		if ( false === $contents ) {
			return false;
		}

		return explode( "\n", $contents );
	}

	/**
	 * @since 0.1.0
	 */
	public function put_contents( $file, $contents, $mode = false ) {

		if ( $this->exists( $file ) ) {

			if ( ! $this->is_file( $file ) ) {
				return false;
			}

		} else {

			self::$mock->add_file( $file );
		}

		self::$mock->set_file_attr( $file, 'contents', $contents );
		self::$mock->set_file_attr( $file, 'mode', $mode );

		return true;
	}

	/**
	 * @since 0.1.0
	 */
	public function cwd() {
		return self::$mock->get_cwd();
	}

	/**
	 * @since 0.1.0
	 */
	public function chdir( $dir ) {
		return self::$mock->set_cwd( $dir );
	}

	/**
	 * @since 0.1.0
	 */
	public function chgrp( $file, $group, $recursive = false ) {

		return self::$mock->set_file_attr(
			$file
			, 'group'
			, $group
			, $recursive
		);
	}

	/**
	 * @since 0.1.0
	 */
	public function chmod( $file, $mode = false, $recursive = false ) {

		if ( ! $mode ) {
			if ( $this->is_file( $file ) ) {
				$mode = FS_CHMOD_FILE;
			} elseif ( $this->is_dir( $file ) ) {
				$mode = FS_CHMOD_DIR;
			} else {
				return false;
			}
		}

		return self::$mock->set_file_attr(
			$file
			, 'mode'
			, $mode
			, $recursive
		);
	}

	/**
	 * @since 0.1.0
	 */
	public function chown( $file, $owner, $recursive = false ) {

		return self::$mock->set_file_attr(
			$file
			, 'owner'
			, $owner
			, $recursive
		);
	}

	/**
	 * @since 0.1.0
	 */
	public function owner( $file ) {
		return self::$mock->get_file_attr( $file, 'owner' );
	}

	/**
	 * @since 0.1.0
	 */
	public function getchmod( $file ) {
		return self::$mock->get_file_attr( $file, 'mode' );
	}

	/**
	 * @since 0.1.0
	 */
	public function group( $file ) {
		return self::$mock->get_file_attr( $file, 'group' );
	}

	/**
	 * @since 0.1.0
	 */
	public function copy( $source, $destination, $overwrite = false, $mode = false ) {

		if ( ! $overwrite && $this->exists( $destination ) ) {
			return false;
		}

		$result = self::$mock->copy( $source, $destination );

		if ( $mode ) {
			$this->chmod( $destination, $mode );
		}

		return $result;
	}

	/**
	 * @since 0.1.0
	 */
	public function move( $source, $destination, $overwrite = false ) {

		if ( ! $overwrite && $this->exists( $destination ) ) {
			return false;
		}

		return self::$mock->move( $source, $destination );
	}

	/**
	 * @since 0.1.0
	 */
	public function delete( $file, $recursive = false, $type = false ) {

		if ( 'f' === $type ) {

			if ( ! $this->is_file( $file ) ) {
				return false;
			}

		} elseif ( false === $recursive ) {

			$contents = self::$mock->get_file_attr( $file, 'contents' );

			if ( ! empty( $contents ) && is_object( $contents ) && array() !== (array) $contents ) {
				return false;
			}
		}

		return self::$mock->delete( $file );
	}

	/**
	 * @since 0.1.0
	 */
	public function exists( $file ) {
		return self::$mock->exists( $file );
	}

	/**
	 * @since 0.1.0
	 */
	public function is_file( $file ) {
		return ( 'file' === self::$mock->get_file_attr( $file, 'type' ) );
	}

	/**
	 * @since 0.1.0
	 */
	public function is_dir( $path ) {
		return ( 'dir' === self::$mock->get_file_attr( $path, 'type' ) );
	}

	/**
	 * @since 0.1.0
	 */
	public function is_readable( $file ) {
		// TODO check group.
		return (bool) ( 0200 & self::$mock->get_file_attr( $file, 'mode' ) );
	}

	/**
	 * @since 0.1.0
	 */
	public function is_writable( $file ) {
		// TODO check group.
		return (bool) ( 0400 & self::$mock->get_file_attr( $file, 'mode' ) );
	}

	/**
	 * @since 0.1.0
	 */
	public function atime( $file ) {
		return self::$mock->get_file_attr( $file, 'atime' );
	}

	/**
	 * @since 0.1.0
	 */
	public function mtime( $file ) {
		return self::$mock->get_file_attr( $file, 'mtime' );
	}

	/**
	 * @since 0.1.0
	 */
	public function size( $file ) {
		return self::$mock->get_file_attr( $file, 'size' );
	}

	/**
	 * @since 0.1.0
	 */
	public function touch( $file, $time = 0, $atime = 0 ) {

		if ( false === $this->exists( $file ) && false === self::$mock->add_file( $file ) ) {
			return false;
		}

		if ( 0 === $time ) {
			$time = time();
		}

		self::$mock->set_file_attr( $file, 'mtime', $time );


		if ( 0 === $atime ) {
			$atime = time();
		}

		self::$mock->set_file_attr( $file, 'atime', $atime );

		return true;
	}

	/**
	 * @since 0.1.0
	 */
	public function mkdir( $path, $chmod = false, $chown = false, $chgrp = false ) {

		if ( ! $chmod ) {
			$chmod = FS_CHMOD_DIR;
		}

		$args = array( 'type' => 'dir', 'mode' => $chmod );

		if ( $chown ) {
			$args['owner'] = $chown;
		}

		if ( $chgrp ) {
			$args['group'] = $chgrp;
		}

		return self::$mock->add_file( $path, $args );
	}

	/**
	 * @since 0.1.0
	 */
	public function rmdir( $path, $recursive = false ) {
		return $this->delete( $path, $recursive );
	}

	/**
	 * @since 0.1.0
	 */
	public function dirlist( $path, $include_hidden = true, $recursive = false ) {
		throw new Exception( __METHOD__ . ' is not implemented yet.' );
	}
}

// EOF
