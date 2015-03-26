<?php

/**
 * A testcase for the WordPress filesystem API extension for the mock filesystem.
 *
 * @package WP_Filesystem_Mock
 * @since 0.1.0
 */

/**
 * Tests the shim between WordPress and the mock filesystem.
 *
 * @since 0.1.0
 */
class WP_Filesystem_Mock_Test extends PHPUnit_Framework_TestCase {

	/**
	 * The filesystem handler that shims the mock filesystem.
	 *
	 * @since 0.1.0
	 *
	 * @var WP_Filesystem_Mock
	 */
	protected $fs;

	/**
	 * The mock filesystem.
	 *
	 * @since 0.1.0
	 *
	 * @var WP_Mock_Filesystem
	 */
	protected $mock_fs;

	/**
	 * @since 0.1.0
	 */
	public function setUp() {

		parent::setUp();

		$this->fs = new WP_Filesystem_Mock( array() );
		$this->mock_fs = new WP_Mock_Filesystem();
		$this->fs->set_mock( $this->mock_fs );
	}

	/**
	 * Test getting a file's contents.
	 *
	 * @since 0.1.0
	 */
	public function test_get_contents() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'contents' => 'Hello World!' ) ) );
		$this->assertEquals( 'Hello World!', $this->fs->get_contents( '/test.txt' ) );
	}

	/**
	 * Test getting a nonexistent file's contents.
	 *
	 * @since 0.1.0
	 */
	public function test_get_nonexistent_file_contents() {

		$this->assertFalse( $this->fs->get_contents( '/test.txt' ) );
	}

	/**
	 * Test getting a directory's contents.
	 *
	 * @since 0.1.0
	 */
	public function test_get_dir_contents() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertFalse( $this->fs->get_contents( '/test' ) );
	}

	/**
	 * Test getting a file's contents as an array.
	 *
	 * @since 0.1.0
	 */
	public function test_get_contents_array() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'contents' => 'Hello World!' ) ) );
		$this->assertEquals( array( 'Hello World!' ), $this->fs->get_contents_array( '/test.txt' ) );
	}

	/**
	 * Test getting a nonexistent file's contents.
	 *
	 * @since 0.1.0
	 */
	public function test_get_nonexistent_file_contents_array() {

		$this->assertFalse( $this->fs->get_contents_array( '/test.txt' ) );
	}

	/**
	 * Test getting a directory's contents.
	 *
	 * @since 0.1.0
	 */
	public function test_get_dir_contents_array() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertFalse( $this->fs->get_contents_array( '/test' ) );
	}

	/**
	 * Test putting a file's contents.
	 *
	 * @since 0.1.0
	 */
	public function test_put_contents() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'contents' => 'Hello World!' ) ) );
		$this->assertEquals( 'Hello World!', $this->fs->get_contents( '/test.txt' ) );
		$this->assertEquals( 0644, $this->fs->getchmod( '/test.txt' ) );

		$this->assertTrue( $this->fs->put_contents( '/test.txt', 'Test.', 0600 ) );

		$this->assertEquals( 'Test.', $this->fs->get_contents( '/test.txt' ) );
		$this->assertEquals( 0600, $this->fs->getchmod( '/test.txt' ) );
	}

	/**
	 * Test that putting contents in a nonexistent file creates it.
	 *
	 * @since 0.1.0
	 */
	public function test_put_nonexistent_file_contents() {

		$this->assertTrue( $this->fs->put_contents( '/test.txt', 'Test.', 0600 ) );

		$this->assertEquals( 'Test.', $this->fs->get_contents( '/test.txt' ) );
		$this->assertEquals( 0600, $this->fs->getchmod( '/test.txt' ) );
	}

	/**
	 * Test that putting contents in a directory fails.
	 *
	 * @since 0.1.0
	 */
	public function test_put_dir_contents() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );

		$this->assertFalse( $this->fs->put_contents( '/test', 'Test.', 0600 ) );

		$this->assertEquals( 'dir', $this->mock_fs->get_file_attr( '/test', 'type' ) );
		$this->assertInstanceOf( 'stdClass', $this->mock_fs->get_file_attr( '/test', 'contents' ) );
		$this->assertEquals( 0755, $this->mock_fs->get_file_attr( '/test', 'mode' ) );
	}

	/**
	 * Test getting the current working directory.
	 *
	 * @since 0.1.0
	 */
	public function test_cwd() {

		$this->assertEquals( '/', $this->fs->cwd() );
	}

	/**
	 * Test setting the current working directory.
	 *
	 * @since 0.1.0
	 */
	public function test_chdir() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertTrue( $this->fs->chdir( '/test' ) );
		$this->assertEquals( '/test', $this->fs->cwd() );
	}

	/**
	 * Test setting the file's group.
	 *
	 * @since 0.1.0
	 */
	public function test_chgrp() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertEquals( '', $this->fs->group( '/test' ) );

		$this->assertTrue( $this->fs->chgrp( '/test', 'test' ) );

		$this->assertEquals( 'test', $this->fs->group( '/test' ) );
	}

	/**
	 * Test setting the file's group recursively.
	 *
	 * @since 0.1.0
	 */
	public function test_chgrp_recursive() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertEquals( '', $this->fs->group( '/test' ) );
		$this->assertEquals( '', $this->fs->group( '/' ) );

		$this->assertTrue( $this->fs->chgrp( '/', 'test', true ) );

		$this->assertEquals( 'test', $this->fs->group( '/test' ) );
		$this->assertEquals( 'test', $this->fs->group( '/' ) );
	}

	/**
	 * Test setting the file's mode.
	 *
	 * @since 0.1.0
	 */
	public function test_chmod() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertEquals( 0755, $this->fs->getchmod( '/test' ) );

		$this->assertTrue( $this->fs->chmod( '/test', 0751 ) );

		$this->assertEquals( 0751, $this->fs->getchmod( '/test' ) );
	}

	/**
	 * Test setting the file's mode recursively.
	 *
	 * @since 0.1.0
	 */
	public function test_chmod_recursive() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertEquals( 0755, $this->fs->getchmod( '/test' ) );
		$this->assertEquals( 0755, $this->fs->getchmod( '/' ) );

		$this->assertTrue( $this->fs->chmod( '/', 0751, true ) );

		$this->assertEquals( 0751, $this->fs->getchmod( '/test' ) );
		$this->assertEquals( 0751, $this->fs->getchmod( '/' ) );
	}

	/**
	 * Test setting the file's owner.
	 *
	 * @since 0.1.0
	 */
	public function test_chown() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertEquals( '', $this->fs->owner( '/test' ) );

		$this->assertTrue( $this->fs->chown( '/test', 'test' ) );

		$this->assertEquals( 'test', $this->fs->owner( '/test' ) );
	}

	/**
	 * Test setting the file's owner recursively.
	 *
	 * @since 0.1.0
	 */
	public function test_chown_recursive() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertEquals( '', $this->fs->owner( '/test' ) );
		$this->assertEquals( '', $this->fs->owner( '/' ) );

		$this->assertTrue( $this->fs->chown( '/', 'test', true ) );

		$this->assertEquals( 'test', $this->fs->owner( '/test' ) );
		$this->assertEquals( 'test', $this->fs->owner( '/' ) );
	}

	/**
	 * Test copying a file.
	 *
	 * @since 0.1.0
	 */
	public function test_copy() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );

		$this->assertTrue( $this->fs->copy( '/test.txt', '/a.txt' ) );

		$this->assertTrue( $this->fs->exists( '/test.txt' ) );
		$this->assertTrue( $this->fs->exists( '/a.txt' ) );
	}

	/**
	 * Test copying a nonexistent file.
	 *
	 * @since 0.1.0
	 */
	public function test_copy_nonexistent_file() {

		$this->assertFalse( $this->fs->copy( '/test.txt', '/a.txt' ) );

		$this->assertFalse( $this->fs->exists( '/test.txt' ) );
		$this->assertFalse( $this->fs->exists( '/a.txt' ) );
	}

	/**
	 * Test copying a file to a nonexistent directory.
	 *
	 * @since 0.1.0
	 */
	public function test_copy_file_to_nonexistent_dir() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );

		$this->assertFalse( $this->fs->copy( '/test.txt', '/a/test.txt' ) );

		$this->assertTrue( $this->fs->exists( '/test.txt' ) );
		$this->assertFalse( $this->fs->exists( '/a/test.txt' ) );
		$this->assertFalse( $this->fs->exists( '/a' ) );
	}

	/**
	 * Test that the file isn't overwritten if it already exists.
	 *
	 * @since 0.1.0
	 */
	public function test_copy_file_not_overwritten() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'contents' => 'test' ) ) );
		$this->assertTrue( $this->mock_fs->add_file( '/a.txt', array( 'contents' => 'abc' ) ) );

		$this->assertFalse( $this->fs->copy( '/test.txt', '/a.txt' ) );

		$this->assertEquals( 'test', $this->fs->get_contents( '/test.txt' ) );
		$this->assertEquals( 'abc', $this->fs->get_contents( '/a.txt' ) );
	}

	/**
	 * Test that the file is overwritten if overwriting is on.
	 *
	 * @since 0.1.0
	 */
	public function test_copy_file_overwrite_on() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'contents' => 'test' ) ) );
		$this->assertTrue( $this->mock_fs->add_file( '/a.txt', array( 'contents' => 'abc' ) ) );

		$this->assertTrue( $this->fs->copy( '/test.txt', '/a.txt', true ) );

		$this->assertEquals( 'test', $this->fs->get_contents( '/test.txt' ) );
		$this->assertEquals( 'test', $this->fs->get_contents( '/a.txt' ) );
	}

	/**
	 * Test setting file mode when copying a file.
	 *
	 * @since 0.1.0
	 */
	public function test_copy_set_mode() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );
		$this->assertEquals( 0644, $this->fs->getchmod( '/test.txt' ) );

		$this->assertTrue( $this->fs->copy( '/test.txt', '/a.txt', false, 0666 ) );

		$this->assertEquals( 0644, $this->fs->getchmod( '/test.txt' ) );
		$this->assertEquals( 0666, $this->fs->getchmod( '/a.txt' ) );
	}

	/**
	 * Test moving a file.
	 *
	 * @since 0.1.0
	 */
	public function test_move() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );

		$this->assertTrue( $this->fs->move( '/test.txt', '/a.txt' ) );

		$this->assertFalse( $this->fs->exists( '/test.txt' ) );
		$this->assertTrue( $this->fs->exists( '/a.txt' ) );
	}

	/**
	 * Test moving a nonexistent file.
	 *
	 * @since 0.1.0
	 */
	public function test_move_nonexistent_file() {

		$this->assertFalse( $this->fs->move( '/test.txt', '/a.txt' ) );

		$this->assertFalse( $this->fs->exists( '/test.txt' ) );
		$this->assertFalse( $this->fs->exists( '/a.txt' ) );
	}

	/**
	 * Test moving a file to a nonexistent directory.
	 *
	 * @since 0.1.0
	 */
	public function test_move_file_to_nonexistent_dir() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );

		$this->assertFalse( $this->fs->move( '/test.txt', '/a/test.txt' ) );

		$this->assertTrue( $this->fs->exists( '/test.txt' ) );
		$this->assertFalse( $this->fs->exists( '/a/test.txt' ) );
		$this->assertFalse( $this->fs->exists( '/a' ) );
	}

	/**
	 * Test that the file isn't overwritten if it already exists.
	 *
	 * @since 0.1.0
	 */
	public function test_move_file_not_overwritten() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'contents' => 'test' ) ) );
		$this->assertTrue( $this->mock_fs->add_file( '/a.txt', array( 'contents' => 'abc' ) ) );

		$this->assertFalse( $this->fs->move( '/test.txt', '/a.txt' ) );

		$this->assertEquals( 'test', $this->fs->get_contents( '/test.txt' ) );
		$this->assertEquals( 'abc', $this->fs->get_contents( '/a.txt' ) );
	}

	/**
	 * Test that the file is overwritten if overwriting is on.
	 *
	 * @since 0.1.0
	 */
	public function test_move_file_overwrite_on() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'contents' => 'test' ) ) );
		$this->assertTrue( $this->mock_fs->add_file( '/a.txt', array( 'contents' => 'abc' ) ) );

		$this->assertTrue( $this->fs->move( '/test.txt', '/a.txt', true ) );

		$this->assertFalse( $this->fs->exists( '/test.txt' ) );
		$this->assertEquals( 'test', $this->fs->get_contents( '/a.txt' ) );
	}

	/**
	 * Test deleting a file.
	 *
	 * @since 0.1.0
	 */
	public function test_delete() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );

		$this->assertTrue( $this->fs->delete( '/test.txt' ) );

		$this->assertFalse( $this->fs->exists( '/test.txt' ) );
	}

	/**
	 * Test deleting a nonexistent file.
	 *
	 * @since 0.1.0
	 */
	public function test_delete_nonexistent_file() {

		$this->assertFalse( $this->fs->delete( '/test.txt' ) );

		$this->assertFalse( $this->fs->exists( '/test.txt' ) );
	}

	/**
	 * Test deleting a directory recursively.
	 *
	 * @since 0.1.0
	 */
	public function test_delete_recursive() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertTrue( $this->mock_fs->add_file( '/test/a.txt' ) );

		$this->assertTrue( $this->fs->delete( '/test', true ) );

		$this->assertFalse( $this->fs->exists( '/test' ) );
		$this->assertFalse( $this->fs->exists( '/test/a.txt' ) );
	}

	/**
	 * Test deleting a file limited to files.
	 *
	 * @since 0.1.0
	 */
	public function test_delete_f() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );

		$this->assertFalse( $this->fs->delete( '/test', false, 'f' ) );

		$this->assertTrue( $this->fs->exists( '/test' ) );
	}

	/**
	 * Test that exists() returns false for a nonexistent file.
	 *
	 * @since 0.1.0
	 */
	public function test_nonexistent_file_exists() {

		$this->assertFalse( $this->fs->exists( '/test' ) );
	}

	/**
	 * Test checking if a file is a file.
	 *
	 * @since 0.1.0
	 */
	public function test_file_is_file() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );

		$this->assertTrue( $this->fs->is_file( '/test.txt' ) );
	}

	/**
	 * Test checking if a directory is a file.
	 *
	 * @since 0.1.0
	 */
	public function test_dir_is_file() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );

		$this->assertFalse( $this->fs->is_file( '/test' ) );
	}

	/**
	 * Test checking if a directory is a directory.
	 *
	 * @since 0.1.0
	 */
	public function test_dir_is_dir() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );

		$this->assertTrue( $this->fs->is_dir( '/test' ) );
	}

	/**
	 * Test checking if a file is a directory.
	 *
	 * @since 0.1.0
	 */
	public function test_file_is_dir() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );

		$this->assertFalse( $this->fs->is_dir( '/test.txt' ) );
	}

	/**
	 * Test checking if a file is readable.
	 *
	 * @since 0.1.0
	 */
	public function test_is_readable() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );

		$this->assertTrue( $this->fs->is_readable( '/test.txt' ) );
	}

	/**
	 * Test checking if a nonexistent file is readable.
	 *
	 * @since 0.1.0
	 */
	public function test_is_nonexistent_file_readable() {

		$this->assertFalse( $this->fs->is_readable( '/test.txt' ) );
	}

	/**
	 * Test checking if an unreadable file is readable.
	 *
	 * @since 0.1.0
	 */
	public function test_is_unreadable_file_readable() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'mode' => 0000 ) ) );

		$this->assertFalse( $this->fs->is_readable( '/test.txt' ) );
	}

	/**
	 * Test checking if a file is writable.
	 *
	 * @since 0.1.0
	 */
	public function test_is_writable() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt' ) );

		$this->assertTrue( $this->fs->is_writable( '/test.txt' ) );
	}

	/**
	 * Test checking if a nonexistent file is writable.
	 *
	 * @since 0.1.0
	 */
	public function test_is_nonexistent_file_writable() {

		$this->assertFalse( $this->fs->is_writable( '/test.txt' ) );
	}

	/**
	 * Test checking if an un-writable file is writable.
	 *
	 * @since 0.1.0
	 */
	public function test_is_unwritable_file_writable() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'mode' => 0300 ) ) );

		$this->assertFalse( $this->fs->is_writable( '/test.txt' ) );
	}

	/**
	 * Test getting a file's creation time.
	 *
	 * @since 0.1.0
	 */
	public function test_atime() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'atime' => 54546 ) ) );

		$this->assertEquals( 54546, $this->fs->atime( '/test.txt' ) );
	}

	/**
	 * Test getting a file's modification time.
	 *
	 * @since 0.1.0
	 */
	public function test_mtime() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'mtime' => 54546 ) ) );

		$this->assertEquals( 54546, $this->fs->mtime( '/test.txt' ) );
	}

	/**
	 * Test getting a file's size.
	 *
	 * @since 0.1.0
	 */
	public function test_size() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'contents' => 'Hello World!' ) ) );

		$this->assertEquals( 12, $this->fs->size( '/test.txt' ) );
	}

	/**
	 * Test touching a file.
	 *
	 * @since 0.1.0
	 */
	public function test_touch() {

		$this->assertTrue( $this->mock_fs->add_file( '/test.txt', array( 'atime' => 505909, 'time' => 8889899 ) ) );

		$this->assertTrue( $this->fs->touch( '/test.txt' ) );

		$time = time();

		$this->assertGreaterThanOrEqual( $time - 1, $this->fs->atime( '/test.txt' ) );
		$this->assertLessThanOrEqual( $time, $this->fs->atime( '/test.txt' ) );

		$this->assertGreaterThanOrEqual( $time - 1, $this->fs->mtime( '/test.txt' ) );
		$this->assertLessThanOrEqual( $time, $this->fs->mtime( '/test.txt' ) );
	}

	/**
	 * Test that touching a file creates it if it doesn't already exist.
	 *
	 * @since 0.1.0
	 */
	public function test_touch_created_file() {

		$this->assertTrue( $this->fs->touch( '/test.txt' ) );

		$this->assertTrue( $this->fs->exists( '/test.txt' ) );

		$time = time();

		$this->assertGreaterThanOrEqual( $time - 1, $this->fs->atime( '/test.txt' ) );
		$this->assertLessThanOrEqual( $time, $this->fs->atime( '/test.txt' ) );

		$this->assertGreaterThanOrEqual( $time - 1, $this->fs->mtime( '/test.txt' ) );
		$this->assertLessThanOrEqual( $time, $this->fs->mtime( '/test.txt' ) );
	}

	/**
	 * Test that touching a file with a specified time.
	 *
	 * @since 0.1.0
	 */
	public function test_touch_time() {

		$this->assertTrue( $this->fs->touch( '/test.txt', 3534, 90970 ) );

		$this->assertTrue( $this->fs->exists( '/test.txt' ) );

		$this->assertEquals( 3534, $this->fs->mtime( '/test.txt' ) );
		$this->assertEquals( 90970, $this->fs->atime( '/test.txt' ) );
	}

	/**
	 * Test creating a directory.
	 *
	 * @since 0.1.0
	 */
	public function test_mkdir() {

		$this->assertTrue( $this->fs->mkdir( '/test' ) );

		$this->assertTrue( $this->fs->exists( '/test' ) );
		$this->assertTrue( $this->fs->is_dir( '/test' ) );

		$this->assertEquals( FS_CHMOD_DIR, $this->fs->getchmod( '/test' ) );
	}

	/**
	 * Test creating a directory with specific permissions.
	 *
	 * @since 0.1.0
	 */
	public function test_mkdir_mode() {

		$this->assertTrue( $this->fs->mkdir( '/test', 0777, 'test-user', 'test-group' ) );

		$this->assertTrue( $this->fs->exists( '/test' ) );
		$this->assertTrue( $this->fs->is_dir( '/test' ) );

		$this->assertEquals( 0777, $this->fs->getchmod( '/test' ) );
		$this->assertEquals( 'test-group', $this->fs->group( '/test' ) );
		$this->assertEquals( 'test-user', $this->fs->owner( '/test' ) );
	}

	/**
	 * Test deleting a directory.
	 *
	 * @since 0.1.0
	 */
	public function test_rmdir() {

		$this->assertTrue( $this->fs->mkdir( '/test' ) );

		$this->assertTrue( $this->fs->exists( '/test' ) );

		$this->assertTrue( $this->fs->rmdir( '/test' ) );

		$this->assertFalse( $this->fs->exists( '/test' ) );
	}

	/**
	 * Test deleting a nonexistent directory.
	 *
	 * @since 0.1.0
	 */
	public function test_rm_nonexistent_dir() {

		$this->assertFalse( $this->fs->delete( '/test' ) );

		$this->assertFalse( $this->fs->exists( '/test' ) );
	}

	/**
	 * Test deleting a directory recursively.
	 *
	 * @since 0.1.0
	 */
	public function test_rmdir_recursive() {

		$this->assertTrue( $this->mock_fs->add_file( '/test', array( 'type' => 'dir' ) ) );
		$this->assertTrue( $this->mock_fs->add_file( '/test/a.txt' ) );

		$this->assertTrue( $this->fs->rmdir( '/test', true ) );

		$this->assertFalse( $this->fs->exists( '/test' ) );
		$this->assertFalse( $this->fs->exists( '/test/a.txt' ) );
	}

	/**
	 * Test getting a list of files.
	 *
	 * @since 0.1.0
	 */
	public function test_dirlist() {

		// See https://github.com/sebastianbergmann/phpunit/issues/454#issuecomment-32171137
		try {
			$this->fs->dirlist( '/test' );
			throw new Exception( 'Failed asserting that an exception was thrown.' );
		} catch ( Exception $e ) {
			$this->assertEquals( 'WP_Filesystem_Mock::dirlist is not implemented yet.', $e->getMessage() );
		}
	}
}

// EOF
