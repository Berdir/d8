<?php

/**
 * @file
 * Definition of Drupal\file\Tests\FileFieldWidgetTest.
 */

namespace Drupal\file\Tests;

/**
 * Tests file field widget.
 */
class FileFieldWidgetTest extends FileFieldTestBase {
  public static function getInfo() {
    return array(
      'name' => 'File field widget test',
      'description' => 'Tests the file field widget, single and multi-valued, with and without AJAX, with public and private files.',
      'group' => 'File',
    );
  }

  /**
   * Tests upload and remove buttons for a single-valued File field.
   */
  function testSingleValuedWidget() {
    // Use 'page' instead of 'article', so that the 'article' image field does
    // not conflict with this test. If in the future the 'page' type gets its
    // own default file or image field, this test can be made more robust by
    // using a custom node type.
    $type_name = 'page';
    $field_name = strtolower($this->randomName());
    $this->createFileField($field_name, $type_name);
    $field = field_info_field($field_name);
    $instance = field_info_instance('node', $field_name, $type_name);

    $test_file = $this->getTestFile('text');

    foreach (array('nojs', 'js') as $type) {
      // Create a new node with the uploaded file and ensure it got uploaded
      // successfully.
      // @todo This only tests a 'nojs' submission, because drupalPostAJAX()
      //   does not yet support file uploads.
      $nid = $this->uploadNodeFile($test_file, $field_name, $type_name);
      $node = node_load($nid, NULL, TRUE);
      $node_file = file_load($node->{$field_name}[LANGUAGE_NOT_SPECIFIED][0]['fid']);
      $this->assertFileExists($node_file, t('New file saved to disk on node creation.'));

      // Ensure the file can be downloaded.
      $this->drupalGet(file_create_url($node_file->uri));
      $this->assertResponse(200, t('Confirmed that the generated URL is correct by downloading the shipped file.'));

      // Ensure the edit page has a remove button instead of an upload button.
      $this->drupalGet("node/$nid/edit");
      $this->assertNoFieldByXPath('//input[@type="submit"]', t('Upload'), t('Node with file does not display the "Upload" button.'));
      $this->assertFieldByXpath('//input[@type="submit"]', t('Remove'), t('Node with file displays the "Remove" button.'));

      // "Click" the remove button (emulating either a nojs or js submission).
      switch ($type) {
        case 'nojs':
          $this->drupalPost(NULL, array(), t('Remove'));
          break;
        case 'js':
          $button = $this->xpath('//input[@type="submit" and @value="' . t('Remove') . '"]');
          $this->drupalPostAJAX(NULL, array(), array((string) $button[0]['name'] => (string) $button[0]['value']));
          break;
      }

      // Ensure the page now has an upload button instead of a remove button.
      $this->assertNoFieldByXPath('//input[@type="submit"]', t('Remove'), t('After clicking the "Remove" button, it is no longer displayed.'));
      $this->assertFieldByXpath('//input[@type="submit"]', t('Upload'), t('After clicking the "Remove" button, the "Upload" button is displayed.'));

      // Save the node and ensure it does not have the file.
      $this->drupalPost(NULL, array(), t('Save'));
      $node = node_load($nid, NULL, TRUE);
      $this->assertTrue(empty($node->{$field_name}[LANGUAGE_NOT_SPECIFIED][0]['fid']), t('File was successfully removed from the node.'));
    }
  }

  /**
   * Tests upload and remove buttons for multiple multi-valued File fields.
   */
  function testMultiValuedWidget() {
    // Use 'page' instead of 'article', so that the 'article' image field does
    // not conflict with this test. If in the future the 'page' type gets its
    // own default file or image field, this test can be made more robust by
    // using a custom node type.
    $type_name = 'page';
    $field_name = strtolower($this->randomName());
    $field_name2 = strtolower($this->randomName());
    $this->createFileField($field_name, $type_name, array('cardinality' => 3));
    $this->createFileField($field_name2, $type_name, array('cardinality' => 3));

    $field = field_info_field($field_name);
    $instance = field_info_instance('node', $field_name, $type_name);

    $field2 = field_info_field($field_name2);
    $instance2 = field_info_instance('node', $field_name2, $type_name);

    $test_file = $this->getTestFile('text');

    foreach (array('nojs', 'js') as $type) {
      // Visit the node creation form, and upload 3 files for each field. Since
      // the field has cardinality of 3, ensure the "Upload" button is displayed
      // until after the 3rd file, and after that, isn't displayed. Because
      // SimpleTest triggers the last button with a given name, so upload to the
      // second field first.
      // @todo This is only testing a non-Ajax upload, because drupalPostAJAX()
      //   does not yet emulate jQuery's file upload.
      //
      $this->drupalGet("node/add/$type_name");
      foreach (array($field_name2, $field_name) as $each_field_name) {
        for ($delta = 0; $delta < 3; $delta++) {
          $edit = array('files[' . $each_field_name . '_' . LANGUAGE_NOT_SPECIFIED . '_' . $delta . ']' => drupal_realpath($test_file->uri));
          // If the Upload button doesn't exist, drupalPost() will automatically
          // fail with an assertion message.
          $this->drupalPost(NULL, $edit, t('Upload'));
        }
      }
      $this->assertNoFieldByXpath('//input[@type="submit"]', t('Upload'), t('After uploading 3 files for each field, the "Upload" button is no longer displayed.'));

      $num_expected_remove_buttons = 6;

      foreach (array($field_name, $field_name2) as $current_field_name) {
        // How many uploaded files for the current field are remaining.
        $remaining = 3;
        // Test clicking each "Remove" button. For extra robustness, test them out
        // of sequential order. They are 0-indexed, and get renumbered after each
        // iteration, so array(1, 1, 0) means:
        // - First remove the 2nd file.
        // - Then remove what is then the 2nd file (was originally the 3rd file).
        // - Then remove the first file.
        foreach (array(1,1,0) as $delta) {
          // Ensure we have the expected number of Remove buttons, and that they
          // are numbered sequentially.
          $buttons = $this->xpath('//input[@type="submit" and @value="Remove"]');
          $this->assertTrue(is_array($buttons) && count($buttons) === $num_expected_remove_buttons, t('There are %n "Remove" buttons displayed (JSMode=%type).', array('%n' => $num_expected_remove_buttons, '%type' => $type)));
          foreach ($buttons as $i => $button) {
            $key = $i >= $remaining ? $i - $remaining : $i;
            $check_field_name = $field_name2;
            if ($current_field_name == $field_name && $i < $remaining) {
              $check_field_name = $field_name;
            }

            $this->assertIdentical((string) $button['name'], $check_field_name . '_' . LANGUAGE_NOT_SPECIFIED . '_' . $key. '_remove_button');
          }

          // "Click" the remove button (emulating either a nojs or js submission).
          $button_name = $current_field_name . '_' . LANGUAGE_NOT_SPECIFIED . '_' . $delta . '_remove_button';
          switch ($type) {
            case 'nojs':
              // drupalPost() takes a $submit parameter that is the value of the
              // button whose click we want to emulate. Since we have multiple
              // buttons with the value "Remove", and want to control which one we
              // use, we change the value of the other ones to something else.
              // Since non-clicked buttons aren't included in the submitted POST
              // data, and since drupalPost() will result in $this being updated
              // with a newly rebuilt form, this doesn't cause problems.
              foreach ($buttons as $button) {
                if ($button['name'] != $button_name) {
                  $button['value'] = 'DUMMY';
                }
              }
              $this->drupalPost(NULL, array(), t('Remove'));
              break;
            case 'js':
              // drupalPostAJAX() lets us target the button precisely, so we don't
              // require the workaround used above for nojs.
              $this->drupalPostAJAX(NULL, array(), array($button_name => t('Remove')));
              break;
          }
          $num_expected_remove_buttons--;
          $remaining--;

          // Ensure an "Upload" button for the current field is displayed with the
          // correct name.
          $upload_button_name = $current_field_name . '_' . LANGUAGE_NOT_SPECIFIED . '_' . $remaining . '_upload_button';
          $buttons = $this->xpath('//input[@type="submit" and @value="Upload" and @name=:name]', array(':name' => $upload_button_name));
          $this->assertTrue(is_array($buttons) && count($buttons) == 1, t('The upload button is displayed with the correct name (JSMode=%type).', array('%type' => $type)));

          // Ensure only at most one button per field is displayed.
          $buttons = $this->xpath('//input[@type="submit" and @value="Upload"]');
          $expected = $current_field_name == $field_name ? 1 : 2;
          $this->assertTrue(is_array($buttons) && count($buttons) == $expected, t('After removing a file, only one "Upload" button for each possible field is displayed (JSMode=%type).', array('%type' => $type)));
        }
      }

      // Ensure the page now has no Remove buttons.
      $this->assertNoFieldByXPath('//input[@type="submit"]', t('Remove'), t('After removing all files, there is no "Remove" button displayed (JSMode=%type).', array('%type' => $type)));

      // Save the node and ensure it does not have any files.
      $this->drupalPost(NULL, array('title' => $this->randomName()), t('Save'));
      $matches = array();
      preg_match('/node\/([0-9]+)/', $this->getUrl(), $matches);
      $nid = $matches[1];
      $node = node_load($nid, NULL, TRUE);
      $this->assertTrue(empty($node->{$field_name}[LANGUAGE_NOT_SPECIFIED][0]['fid']), t('Node was successfully saved without any files.'));
    }
  }

  /**
   * Tests a file field with a "Private files" upload destination setting.
   */
  function testPrivateFileSetting() {
    // Use 'page' instead of 'article', so that the 'article' image field does
    // not conflict with this test. If in the future the 'page' type gets its
    // own default file or image field, this test can be made more robust by
    // using a custom node type.
    $type_name = 'page';
    $field_name = strtolower($this->randomName());
    $this->createFileField($field_name, $type_name);
    $field = field_info_field($field_name);
    $instance = field_info_instance('node', $field_name, $type_name);

    $test_file = $this->getTestFile('text');

    // Change the field setting to make its files private, and upload a file.
    $edit = array('field[settings][uri_scheme]' => 'private');
    $this->drupalPost("admin/structure/types/manage/$type_name/fields/$field_name", $edit, t('Save settings'));
    $nid = $this->uploadNodeFile($test_file, $field_name, $type_name);
    $node = node_load($nid, NULL, TRUE);
    $node_file = file_load($node->{$field_name}[LANGUAGE_NOT_SPECIFIED][0]['fid']);
    $this->assertFileExists($node_file, t('New file saved to disk on node creation.'));

    // Ensure the private file is available to the user who uploaded it.
    $this->drupalGet(file_create_url($node_file->uri));
    $this->assertResponse(200, t('Confirmed that the generated URL is correct by downloading the shipped file.'));

    // Ensure we can't change 'uri_scheme' field settings while there are some
    // entities with uploaded files.
    $this->drupalGet("admin/structure/types/manage/$type_name/fields/$field_name");
    $this->assertFieldByXpath('//input[@id="edit-field-settings-uri-scheme-public" and @disabled="disabled"]', 'public', t('Upload destination setting disabled.'));

    // Delete node and confirm that setting could be changed.
    node_delete($nid);
    $this->drupalGet("admin/structure/types/manage/$type_name/fields/$field_name");
    $this->assertFieldByXpath('//input[@id="edit-field-settings-uri-scheme-public" and not(@disabled)]', 'public', t('Upload destination setting enabled.'));
  }

  /**
   * Tests that download restrictions on private files work on comments.
   */
  function testPrivateFileComment() {
    $user = $this->drupalCreateUser(array('access comments'));

    // Remove access comments permission from anon user.
    $edit = array(
      DRUPAL_ANONYMOUS_RID . '[access comments]' => FALSE,
    );
    $this->drupalPost('admin/people/permissions', $edit, t('Save permissions'));

    // Create a new field.
    $edit = array(
      'fields[_add_new_field][label]' => $label = $this->randomName(),
      'fields[_add_new_field][field_name]' => $name = strtolower($this->randomName()),
      'fields[_add_new_field][type]' => 'file',
      'fields[_add_new_field][widget_type]' => 'file_generic',
    );
    $this->drupalPost('admin/structure/types/manage/article/comment/fields', $edit, t('Save'));
    $edit = array('field[settings][uri_scheme]' => 'private');
    $this->drupalPost(NULL, $edit, t('Save field settings'));
    $this->drupalPost(NULL, array(), t('Save settings'));

    // Create node.
    $text_file = $this->getTestFile('text');
    $edit = array(
      'title' => $this->randomName(),
    );
    $this->drupalPost('node/add/article', $edit, t('Save'));
    $node = $this->drupalGetNodeByTitle($edit['title']);

    // Add a comment with a file.
    $text_file = $this->getTestFile('text');
    $edit = array(
      'files[field_' . $name . '_' . LANGUAGE_NOT_SPECIFIED . '_' . 0 . ']' => drupal_realpath($text_file->uri),
      'comment_body[' . LANGUAGE_NOT_SPECIFIED . '][0][value]' => $comment_body = $this->randomName(),
    );
    $this->drupalPost(NULL, $edit, t('Save'));

    // Get the comment ID.
    preg_match('/comment-([0-9]+)/', $this->getUrl(), $matches);
    $cid = $matches[1];

    // Log in as normal user.
    $this->drupalLogin($user);

    $comment = comment_load($cid);
    $comment_file = file_load($comment->{'field_' . $name}[LANGUAGE_NOT_SPECIFIED][0]['fid']);
    $this->assertFileExists($comment_file, t('New file saved to disk on node creation.'));
    // Test authenticated file download.
    $url = file_create_url($comment_file->uri);
    $this->assertNotEqual($url, NULL, t('Confirmed that the URL is valid'));
    $this->drupalGet(file_create_url($comment_file->uri));
    $this->assertResponse(200, t('Confirmed that the generated URL is correct by downloading the shipped file.'));

    // Test anonymous file download.
    $this->drupalLogout();
    $this->drupalGet(file_create_url($comment_file->uri));
    $this->assertResponse(403, t('Confirmed that access is denied for the file without the needed permission.'));

    // Unpublishes node.
    $this->drupalLogin($this->admin_user);
    $edit = array(
      'status' => FALSE,
    );
    $this->drupalPost('node/' . $node->nid . '/edit', $edit, t('Save'));

    // Ensures normal user can no longer download the file.
    $this->drupalLogin($user);
    $this->drupalGet(file_create_url($comment_file->uri));
    $this->assertResponse(403, t('Confirmed that access is denied for the file without the needed permission.'));
  }

}
