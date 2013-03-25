<?php

/**
 * @file
 * Definition of Drupal\file\Tests\RemoteFileSaveUploadTest.
 */

namespace Drupal\file\Tests;

/**
 * Tests the file_save_upload() function on remote filesystems.
 */
class RemoteFileSaveUploadTest extends SaveUploadTest {

  public static function getInfo() {
    $info = parent::getInfo();
    $info['group'] = 'File API (remote)';
    return $info;
  }

  function setUp() {
    parent::setUp();
    config('system.file')->set('default_scheme', 'dummy-remote')->save();
  }
}
