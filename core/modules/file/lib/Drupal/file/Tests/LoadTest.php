<?php

/**
 * @file
 * Definition of Drupal\file\Tests\LoadTest.
 */

namespace Drupal\file\Tests;

/**
 * Tests the file_load() function.
 */
class LoadTest extends FileManagedTestBase {
  public static function getInfo() {
    return array(
      'name' => 'File loading',
      'description' => 'Tests the file_load() function.',
      'group' => 'File API',
    );
  }

  /**
   * Try to load a non-existent file by fid.
   */
  function testLoadMissingFid() {
    $this->assertFalse(file_load(-1), 'Try to load an invalid fid fails.');
    $this->assertFileHooksCalled(array());
  }

  /**
   * Try to load a non-existent file by URI.
   */
  function testLoadMissingFilepath() {
    $files = entity_load_multiple_by_properties('file', array('uri' => 'foobar://misc/druplicon.png'));
    $this->assertFalse(reset($files), "Try to load a file that doesn't exist in the database fails.");
    $this->assertFileHooksCalled(array());
  }

  /**
   * Try to load a non-existent file by status.
   */
  function testLoadInvalidStatus() {
    $files = entity_load_multiple_by_properties('file', array('status' => -99));
    $this->assertFalse(reset($files), 'Trying to load a file with an invalid status fails.');
    $this->assertFileHooksCalled(array());
  }

  /**
   * Load a single file and ensure that the correct values are returned.
   */
  function testSingleValues() {
    // Create a new file entity from scratch so we know the values.
    $file = $this->createFile('druplicon.txt', NULL, 'public');

    $by_fid_file = file_load($file->id());
    $this->assertFileHookCalled('load');
    $this->assertTrue(is_object($by_fid_file), 'file_load() returned an object.');
    $this->assertEqual($by_fid_file->id(), $file->id(), 'Loading by fid got the same fid.', 'File');
    $this->assertEqual($by_fid_file->getFileUri(), $file->getFileUri(), 'Loading by fid got the correct filepath.', 'File');
    $this->assertEqual($by_fid_file->getFilename(), $file->getFilename(), 'Loading by fid got the correct filename.', 'File');
    $this->assertEqual($by_fid_file->getMimeType(), $file->getMimeType(), 'Loading by fid got the correct MIME type.', 'File');
    $this->assertEqual($by_fid_file->isPermanent(), $file->isPermanent(), 'Loading by fid got the correct status.', 'File');
    $this->assertTrue($by_fid_file->file_test['loaded'], 'file_test_file_load() was able to modify the file during load.');
  }

  /**
   * This will test loading file data from the database.
   */
  function testMultiple() {
    // Create a new file entity.
    $file = $this->createFile('druplicon.txt', NULL, 'public');

    // Load by path.
    file_test_reset();
    $by_path_files = entity_load_multiple_by_properties('file', array('uri' => $file->getFileUri()));
    $this->assertFileHookCalled('load');
    $this->assertEqual(1, count($by_path_files), 'file_load_multiple() returned an array of the correct size.');
    $by_path_file = reset($by_path_files);
    $this->assertTrue($by_path_file->file_test['loaded'], 'file_test_file_load() was able to modify the file during load.');
    $this->assertEqual($by_path_file->id(), $file->id(), 'Loading by filepath got the correct fid.', 'File');

    // Load by fid.
    file_test_reset();
    $by_fid_files = file_load_multiple(array($file->id()));
    $this->assertFileHooksCalled(array());
    $this->assertEqual(1, count($by_fid_files), 'file_load_multiple() returned an array of the correct size.');
    $by_fid_file = reset($by_fid_files);
    $this->assertTrue($by_fid_file->file_test['loaded'], 'file_test_file_load() was able to modify the file during load.');
    $this->assertEqual($by_fid_file->getFileUri(), $file->getFileUri(), 'Loading by fid got the correct filepath.', 'File');
  }
}
