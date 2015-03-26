<?php

/**
 * A testcase for the mock filesystem.
 *
 * @package WP_Filesystem_Mock
 * @since 0.1.0
 */

/**
 * Tests for the mock filesystem.
 *
 * @since 0.1.0
 */
class WP_Mock_FileSystem_Test extends PHPUnit_Framework_TestCase {

	/**
	 * The mock used in the tests.
	 *
	 * @since 0.1.0
	 *
	 * @var WP_Mock_Filesystem
	 */
	protected $mock;

	/**
	 * @since 0.1.0
	 */
	public function setUp() {
		$this->mock = new WP_Mock_Filesystem();
	}

	/**
	 * Test adding a file.
	 *
	 * @since 0.1.0
	 */
	public function test_add_file() {

		$this->assertTrue( $this->mock->add_file( '/test.txt' ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'file', $this->mock->get_file_attr( '/test.txt', 'type' ) );
		$this->assertEquals( '', $this->mock->get_file_attr( '/test.txt', 'contents' ) );
		$this->assertEquals( 0, $this->mock->get_file_attr( '/test.txt', 'size' ) );
		$this->assertEquals( 0644, $this->mock->get_file_attr( '/test.txt', 'mode' ) );
	}

	/**
	 * Test adding a directory.
	 *
	 * @since 0.1.0
	 */
	public function test_add_directory() {

		$this->assertTrue( $this->mock->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertTrue( $this->mock->exists( '/test' ) );
		$this->assertEquals( 'dir', $this->mock->get_file_attr( '/test', 'type' ) );
		$this->assertInstanceOf( 'stdClass', $this->mock->get_file_attr( '/test', 'contents' ) );
		$this->assertEquals( 0755, $this->mock->get_file_attr( '/test', 'mode' ) );
	}

	/**
	 * Test adding a directory with a trailing slash.
	 *
	 * @since 0.1.0
	 */
	public function test_add_directory_with_trailing_slash() {

		$this->assertTrue( $this->mock->add_file( '/test/', array( 'type' => 'dir' ) ) );
		$this->assertTrue( $this->mock->exists( '/test' ) );
		$this->assertEquals( 'dir', $this->mock->get_file_attr( '/test', 'type' ) );
	}

	/**
	 * Test adding a file in a nonexistent directory has no effect..
	 *
	 * @since 0.1.0
	 */
	public function test_add_file_in_nonexistent_directory() {

		$this->assertFalse( $this->mock->add_file( '/a/test.txt' ) );
		$this->assertFalse( $this->mock->exists( '/a/test.txt' ) );
		$this->assertFalse( $this->mock->exists( '/a' ) );
	}

	/**
	 * Test adding a directory.
	 *
	 * @since 0.1.0
	 */
	public function test_add_directory_in_nonexistent_directory() {

		$this->assertFalse( $this->mock->add_file( '/a/test' ) );
		$this->assertFalse( $this->mock->exists( '/a/test' ) );
		$this->assertFalse( $this->mock->exists( '/a' ) );
	}

	/**
	 * Test adding a file that already exists.
	 *
	 * @since 0.1.0
	 */
	public function test_add_existing_file() {

		$this->assertTrue( $this->mock->add_file( '/test.txt' ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );

		$this->assertFalse( $this->mock->add_file( '/test.txt' ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
	}

	/**
	 * Test adding a directory that already exists.
	 *
	 * @since 0.1.0
	 */
	public function test_add_existing_directory() {

		$this->assertTrue( $this->mock->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertTrue( $this->mock->exists( '/test' ) );

		$this->assertFalse( $this->mock->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertTrue( $this->mock->exists( '/test' ) );
	}

	/**
	 * Test adding a deep directory.
	 *
	 * @since 0.1.0
	 */
	public function test_mkdir_p() {

		$this->assertTrue( $this->mock->mkdir_p( '/a/b/c' ) );
		$this->assertTrue( $this->mock->exists( '/a/b/c' ) );
		$this->assertEquals( 'dir', $this->mock->get_file_attr( '/a', 'type' ) );
		$this->assertEquals( 'dir', $this->mock->get_file_attr( '/a/b', 'type' ) );
		$this->assertEquals( 'dir', $this->mock->get_file_attr( '/a/b/c', 'type' ) );
	}

	/**
	 * Test adding a deep directory where some levels already exist.
	 *
	 * @since 0.1.0
	 */
	public function test_mkdir_p_some_exist() {

		$this->assertTrue( $this->mock->add_file( '/a', array( 'type' => 'dir' ) ) );

		$this->assertTrue( $this->mock->mkdir_p( '/a/b/c' ) );
		$this->assertTrue( $this->mock->exists( '/a/b/c' ) );
		$this->assertEquals( 'dir', $this->mock->get_file_attr( '/a', 'type' ) );
		$this->assertEquals( 'dir', $this->mock->get_file_attr( '/a/b', 'type' ) );
		$this->assertEquals( 'dir', $this->mock->get_file_attr( '/a/b/c', 'type' ) );
	}

	/**
	 * Test adding a directory that already exists.
	 *
	 * @since 0.1.0
	 */
	public function test_exists_when_not_exists() {

		$this->assertFalse( $this->mock->exists( '/test.txt' ) );
		$this->assertFalse( $this->mock->exists( '/test' ) );
	}

	/**
	 * Test that the root directory always exists.
	 *
	 * @since 0.1.0
	 */
	public function test_root_directory_always_exists() {

		$this->assertTrue( $this->mock->exists( '/' ) );
	}

	/**
	 * Test getting a file attribute for a specific file type.
	 *
	 * @since 0.1.0
	 */
	public function test_get_file_attr_file_type() {

		$this->assertTrue( $this->mock->add_file( '/test.txt', array( 'contents' => 'testing' ) ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents', 'file' ) );
		$this->assertFalse( $this->mock->get_file_attr( '/test.txt', 'contents', 'dir' ) );
	}

	/**
	 * Test setting a file attribute.
	 *
	 * @since 0.1.0
	 */
	public function test_set_file_attr() {

		$this->assertTrue( $this->mock->add_file( '/test.txt' ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 0, $this->mock->get_file_attr( '/test.txt', 'size' ) );
		$this->assertTrue( $this->mock->set_file_attr( '/test.txt', 'size', 1024 ) );
		$this->assertEquals( 1024, $this->mock->get_file_attr( '/test.txt', 'size' ) );
	}

	/**
	 * Test that setting the content attribute on a file updates the size.
	 *
	 * @since 0.1.0
	 */
	public function test_set_content_file_attr() {

		$this->assertTrue( $this->mock->add_file( '/test.txt' ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 0, $this->mock->get_file_attr( '/test.txt', 'size' ) );
		$this->assertTrue( $this->mock->set_file_attr( '/test.txt', 'contents', 'test' ) );
		$this->assertEquals( 4, $this->mock->get_file_attr( '/test.txt', 'size' ) );
	}

	/**
	 * Test that setting the content attribute on a directory fails.
	 *
	 * @since 0.1.0
	 */
	public function test_set_content_file_attr_on_directory() {

		$this->assertTrue( $this->mock->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertTrue( $this->mock->exists( '/test' ) );
		$this->assertEquals( false, $this->mock->get_file_attr( '/test', 'size' ) );
		$this->assertFalse( $this->mock->set_file_attr( '/test', 'contents', 'test' ) );
		$this->assertInstanceOf( 'stdClass', $this->mock->get_file_attr( '/test', 'contents' ) );
		$this->assertEquals( false, $this->mock->get_file_attr( '/test', 'size' ) );
	}

	/**
	 * Test setting a file attribute recursively.
	 *
	 * @since 0.1.0
	 */
	public function test_set_file_attr_recursive() {

		$this->assertTrue( $this->mock->add_file( '/test.txt' ) );

		$this->assertEquals( '', $this->mock->get_file_attr( '/test.txt', 'group' ) );
		$this->assertEquals( '', $this->mock->get_file_attr( '/', 'group' ) );

		$this->assertTrue( $this->mock->set_file_attr( '/', 'group', 'test', true ) );

		$this->assertEquals( 'test', $this->mock->get_file_attr( '/test.txt', 'group' ) );
		$this->assertEquals( 'test', $this->mock->get_file_attr( '/', 'group' ) );
	}

	/**
	 * Test getting the current working directory.
	 *
	 * @since 0.1.0
	 */
	public function test_get_cwd() {

		$this->assertEquals( '/', $this->mock->get_cwd() );
	}

	/**
	 * Test setting the current working directory.
	 *
	 * @since 0.1.0
	 */
	public function test_set_cwd() {

		$this->assertTrue( $this->mock->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertTrue( $this->mock->set_cwd( '/test' ) );
		$this->assertEquals( '/test', $this->mock->get_cwd() );
	}

	/**
	 * Test setting the current working directory to a nonexistent directory.
	 *
	 * @since 0.1.0
	 */
	public function test_set_nonexistent_cwd() {

		$this->assertFalse( $this->mock->set_cwd( '/test' ) );
		$this->assertEquals( '/', $this->mock->get_cwd() );
	}

	/**
	 * Test setting the current working directory to a file.
	 *
	 * @since 0.1.0
	 */
	public function test_set_cwd_file() {

		$this->assertTrue( $this->mock->add_file( '/test.txt' ) );
		$this->assertFalse( $this->mock->set_cwd( '/test.txt' ) );
		$this->assertEquals( '/', $this->mock->get_cwd() );
	}

	/**
	 * Test copying a file.
	 *
	 * @since 0.1.0
	 */
	public function test_copy_file() {

		$this->assertTrue( $this->mock->add_file( '/test.txt', array( 'contents' => 'testing' ) ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );

		$this->assertTrue( $this->mock->copy( '/test.txt', '/a.txt' ) );

		$this->assertTrue( $this->mock->exists( '/a.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/a.txt', 'contents' ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );
	}

	/**
	 * Test copying a nonexistent file.
	 *
	 * @since 0.1.0
	 */
	public function test_copy_nonexistent_file() {

		$this->assertFalse( $this->mock->copy( '/test.txt', '/a.txt' ) );
		$this->assertFalse( $this->mock->exists( '/a.txt' ) );
		$this->assertFalse( $this->mock->exists( '/test.txt' ) );
	}

	/**
	 * Test copying a file to a nonexistent directory.
	 *
	 * @since 0.1.0
	 */
	public function test_copy_file_to_nonexistent_dir() {

		$this->assertTrue( $this->mock->add_file( '/test.txt', array( 'contents' => 'testing' ) ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );

		$this->assertFalse( $this->mock->copy( '/test.txt', '/a/b.txt' ) );

		$this->assertFalse( $this->mock->exists( '/a/b.txt' ) );

		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );
	}

	/**
	 * Test copying a file to a existing destination file.
	 *
	 * @since 0.1.0
	 */
	public function test_copy_file_to_existing_destination_file() {

		$this->assertTrue( $this->mock->add_file( '/test.txt', array( 'contents' => 'testing' ) ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );

		$this->assertTrue( $this->mock->add_file( '/a.txt', array( 'contents' => 'abc' ) ) );
		$this->assertTrue( $this->mock->exists( '/a.txt' ) );
		$this->assertEquals( 'abc', $this->mock->get_file_attr( '/a.txt', 'contents' ) );

		$this->assertTrue( $this->mock->copy( '/test.txt', '/a.txt' ) );

		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );

		$this->assertTrue( $this->mock->exists( '/a.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/a.txt', 'contents' ) );
	}

	/**
	 * Test moving a file.
	 *
	 * @since 0.1.0
	 */
	public function test_move_file() {

		$this->assertTrue( $this->mock->add_file( '/test.txt', array( 'contents' => 'testing' ) ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );

		$this->assertTrue( $this->mock->move( '/test.txt', '/a.txt' ) );

		$this->assertFalse( $this->mock->exists( '/test.txt' ) );

		$this->assertTrue( $this->mock->exists( '/a.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/a.txt', 'contents' ) );
	}

	/**
	 * Test moving a file to a nonexistent directory.
	 *
	 * @since 0.1.0
	 */
	public function test_move_file_to_nonexistent_dir() {

		$this->assertTrue( $this->mock->add_file( '/test.txt', array( 'contents' => 'testing' ) ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );

		$this->assertFalse( $this->mock->move( '/test.txt', '/a/b.txt' ) );

		$this->assertFalse( $this->mock->exists( '/a/b.txt' ) );

		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );
	}

	/**
	 * Test moving a file to a existing destination file.
	 *
	 * @since 0.1.0
	 */
	public function test_move_file_to_existing_destination_file() {

		$this->assertTrue( $this->mock->add_file( '/test.txt', array( 'contents' => 'testing' ) ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/test.txt', 'contents' ) );

		$this->assertTrue( $this->mock->add_file( '/a.txt', array( 'contents' => 'abc' ) ) );
		$this->assertTrue( $this->mock->exists( '/a.txt' ) );
		$this->assertEquals( 'abc', $this->mock->get_file_attr( '/a.txt', 'contents' ) );

		$this->assertTrue( $this->mock->move( '/test.txt', '/a.txt' ) );

		$this->assertFalse( $this->mock->exists( '/test.txt' ) );

		$this->assertTrue( $this->mock->exists( '/a.txt' ) );
		$this->assertEquals( 'testing', $this->mock->get_file_attr( '/a.txt', 'contents' ) );
	}

	/**
	 * Test deleting a file.
	 *
	 * @since 0.1.0
	 */
	public function test_delete_file() {

		$this->assertTrue( $this->mock->add_file( '/test.txt' ) );
		$this->assertTrue( $this->mock->exists( '/test.txt' ) );

		$this->assertTrue( $this->mock->delete( '/test.txt' ) );

		$this->assertFalse( $this->mock->exists( '/test.txt' ) );
	}

	/**
	 * Test deleting a nonexistent file.
	 *
	 * @since 0.1.0
	 */
	public function test_delete_nonexistent_file() {

		$this->assertFalse( $this->mock->delete( '/test.txt' ) );

		$this->assertFalse( $this->mock->exists( '/test.txt' ) );
	}
}

// EOF
