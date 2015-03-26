<?php

/**
 * Class representing a mock filesystem.
 *
 * @package WP_Filesystem_Mock
 * @since 0.1.0
 */

/**
 * Represents a mock filesystem.
 *
 * This class represents a mock filesystem and provides all of the tools needed to
 * manipulate it.
 *
 * This class has no dependencies. It is actually WordPress agnostic.
 *
 * @since 0.1.0
 */
class WP_Mock_Filesystem {

	/**
	 * The mock filesystem.
	 *
	 * @since 0.1.0
	 *
	 * @var object
	 */
	protected $root;

	/**
	 * The current working directory.
	 *
	 * @since 0.1.0
	 *
	 * @var object
	 */
	protected $cwd;

	//
	// Protected Methods.
	//

	/**
	 * Normalize a path.
	 *
	 * @since 0.1.0
	 *
	 * @param string $path The path to normalize.
	 *
	 * @return string The normalized path.
	 */
	protected function normalize_path( $path ) {

		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '|/+|','/', $path );

		return rtrim( $path, '/' );
	}

	/**
	 * Parse a file path into the format needed.
	 *
	 * @since 0.1.0
	 *
	 * @param string $path A file path.
	 *
	 * @return array
	 */
	protected function parse_path( $path ) {

		return explode( '/', $this->normalize_path( $path ) );
	}

	/**
	 * Get the data for a file.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file The file to retrieve.
	 *
	 * @return object|false The file data, or false if it does't exist.
	 */
	protected function get_file( $file ) {

		$path_parts = $this->parse_path( $file );

		if ( '' === $path_parts[0] ) {
			$file = $this->root;
			unset( $path_parts[0] );
		} else {
			$file = $this->cwd;
		}

		foreach ( $path_parts as $part ) {

			if ( '' === $part ) {
				continue;
			}

			if ( 'dir' !== $file->type || false === isset( $file->contents->$part ) ) {
				return false;
			}

			$file = $file->contents->$part;
		}

		return $file;
	}

	/**
	 * Get the default attributes for a file.
	 *
	 * @since 0.1.0
	 *
	 * @param string $atts
	 *
	 * @return object The attributes.
	 */
	protected function get_default_atts( $atts ) {

		if ( false === isset( $atts['type'] ) ) {
			$atts['type'] = 'file';
		}

		if ( 'file' === $atts['type'] ) {
			$defaults = array( 'contents' => '', 'mode' => 0644, 'size' => 0, );
		} elseif ( 'dir' === $atts['type'] ) {
			$defaults = array( 'contents' => new stdClass(), 'mode' => 0755 );
		} else {
			$defaults = array();
		}

		$atts = array_merge(
			array(
				'atime' => time(),
				'mtime' => time(),
				'owner' => '',
				'group' => '',
			)
			, $defaults
			, $atts
		);

		if ( ! empty( $atts['contents'] ) && 'file' === $atts['type'] ) {
			$atts['size'] = mb_strlen( $atts['contents'], '8bit' );
		}

		return (object) $atts;
	}

	//
	// Public Methods.
	//

	/**
	 * @since 0.1.0
	 */
	public function __construct() {

		$this->root = $this->get_default_atts( array( 'type' => 'dir' ) );
		$this->cwd = '/';
	}

	/**
	 * Create a file or directory.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file The path of the file/directory to create.
	 * @param array  $atts The attributes of the file/directory.
	 *
	 * @return bool True if the file was added, false otherwise.
	 */
	public function add_file( $file, array $atts = array() ) {

		$parent = $this->get_file( dirname( $file ) );
		$filename = basename( $file );

		if ( false === $parent || true === isset( $parent->contents->$filename ) ) {
			return false;
		}

		$parent->contents->$filename = $this->get_default_atts( $atts );

		return true;
	}

	/**
	 * Create a deep directory.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file The path of the file/directory to create.
	 * @param array  $atts The attributes of the file/directory.
	 *
	 * @return bool True if the file was added, false otherwise.
	 */
	public function mkdir_p( $file, array $atts = array() ) {

		$dir_levels = $this->parse_path( $file );
		$path = '';

		$atts['type'] = 'dir';

		foreach ( $dir_levels as $level ) {

			$path .= '/' . $level;

			if ( $this->exists( $path ) ) {
				continue;
			}

			if ( ! $this->add_file( $path, $atts ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if a file or directory exists.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file The path to the file or directory to check for.
	 *
	 * @return bool True if the file exists, false otherwise.
	 */
	public function exists( $file ) {
		return (bool) $this->get_file( $file );
	}

	/**
	 * Get a piece of information about a file.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file The path to the file/directory.
	 * @param string $attr The file attribute to get.
	 * @param string $type The type of file this is expected to be.
	 *
	 * @return mixed The value of this attribute, or false on failure.
	 */
	public function get_file_attr( $file, $attr, $type = null ) {

		$file_atts = $this->get_file( $file );

		if ( false === $file_atts || false === isset( $file_atts->$attr ) ) {
			return false;
		}

		if ( isset( $type ) && $type !== $file_atts->type ) {
			return false;
		}

		return $file_atts->$attr;
	}

	/**
	 * Set the value of file attribute.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file      The path of the file/directory.
	 * @param string $attr      The attribute to set.
	 * @param mixed  $value     The new value for this attribute.
	 * @param bool   $recursive Whether to set the value recursively.
	 *
	 * @return bool True if the attribute value was set, false otherwise.
	 */
	public function set_file_attr( $file, $attr, $value, $recursive = false ) {

		$file_atts = $this->get_file( $file );

		if ( false === $file_atts ) {
			return false;
		}

		if ( 'contents' === $attr ) {
			if ( 'file' === $file_atts->type ) {
				$file_atts->size = mb_strlen( $value, '8bit' );
			} else {
				return false;
			}
		}

		if ( true === $recursive ) {
			$this->set_file_attr_recursive( $file_atts, $attr, $value );
		}

		$file_atts->$attr = $value;

		return true;
	}

	/**
	 * Set the value of file attribute recursively.
	 *
	 * @since 0.1.0
	 *
	 * @param object $file  The file data.
	 * @param string $attr  The attribute to set.
	 * @param mixed  $value The new value for this attribute.
	 *
	 * @return bool True if the attribute value was set, false otherwise.
	 */
	protected function set_file_attr_recursive( $file, $attr, $value ) {

		if ( 'dir' === $file->type ) {

			foreach ( $file->contents as $sub => $atts ) {
				$this->set_file_attr_recursive( $atts, $attr, $value );
			}
		}

		$file->$attr = $value;
	}

	/**
	 * Get the current working directory.
	 *
	 * @since 0.1.0
	 *
	 * @return string The full path of the current working directory.
	 */
	public function get_cwd() {
		return $this->cwd;
	}

	/**
	 * Set the current working directory.
	 *
	 * @since 0.1.0
	 *
	 * @param string $cwd The path to the new working directory.
	 *
	 * @return bool True if the current working directory was set, false otherwise.
	 */
	public function set_cwd( $cwd ) {

		$file = $this->get_file( $cwd );

		if ( false === $file || 'dir' !== $file->type ) {
			return false;
		}

		$this->cwd = $this->normalize_path( $cwd );

		if ( empty( $this->cwd ) ) {
			$this->cwd = '/';
		}

		return true;
	}

	/**
	 * Copy a file or directory.
	 *
	 * The destination will be overwritten if it already exists.
	 *
	 * @since 0.1.0
	 *
	 * @param string $source      The source path.
	 * @param string $destination The destination path.
	 *
	 * @return bool True if the file was copied, false otherwise.
	 */
	public function copy( $source, $destination ) {

		$source = $this->get_file( $source );

		if ( false === $source ) {
			return false;
		}

		$destination_parent = $this->get_file( dirname( $destination ) );
		$filename = basename( $destination );

		if ( false === $destination_parent ) {
			return false;
		}

		$destination_parent->contents->$filename = clone $source;

		return true;
	}

	/**
	 * Move a file or directory.
	 *
	 * @since 0.1.0
	 *
	 * @param string $source      The source path.
	 * @param string $destination The destination path.
	 *
	 * @return bool True if the file was moved, false otherwise.
	 */
	public function move( $source, $destination ) {

		if ( false === $this->copy( $source, $destination ) ) {
			return false;
		}

		$this->delete( $source );

		return true;
	}

	/**
	 * Delete a file or directory.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file The path of the file/directory to delete.
	 *
	 * @return bool True if the file was deleted, false otherwise.
	 */
	public function delete( $file ) {

		$parent = $this->get_file( dirname( $file ) );

		$filename = basename( $file );

		if (
			false === $parent
			|| 'dir' !== $parent->type
			|| false === isset( $parent->contents->$filename )
		) {
			return false;
		}

		unset( $parent->contents->$filename );

		return true;
	}
}

// EOF
