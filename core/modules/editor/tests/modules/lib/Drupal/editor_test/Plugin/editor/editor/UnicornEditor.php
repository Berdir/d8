<?php

/**
 * @file
 * Contains \Drupal\editor_test\Plugin\editor\editor\UnicornEditor.
 */

namespace Drupal\editor_test\Plugin\editor\editor;

use Drupal\editor\Plugin\EditorBase;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\editor\Plugin\Core\Entity\Editor;

/**
 * Defines a Unicorn-powered text editor for Drupal.
 *
 * @Plugin(
 *   id = "unicorn",
 *   label = @Translation("Unicorn Editor"),
 *   module = "editor_test"
 * )
 */
class UnicornEditor extends EditorBase {

  /**
   * Implements \Drupal\editor\Plugin\EditorInterface::getDefaultSettings().
   */
  function getDefaultSettings() {
    return array('ponies too' => TRUE);
  }

  /**
   * Implements \Drupal\editor\Plugin\EditorInterface::settingsForm().
   */
  function settingsForm(array $form, array &$form_state, Editor $editor) {
    $form['foo'] = array('#type' => 'textfield', '#default_value' => 'bar');
    return $form;
  }

  /**
   * Implements \Drupal\editor\Plugin\EditorInterface::getJSSettings().
   */
  function getJSSettings(Editor $editor) {
    $settings = array();
    if ($editor->settings['ponies too']) {
      $settings['ponyModeEnabled'] = TRUE;
    }
    return $settings;
  }

  /**
   * Implements \Drupal\editor\Plugin\EditorInterface::getLibraries().
   */
  public function getLibraries(Editor $editor) {
    return array(
      array('edit_test', 'unicorn'),
    );
  }

}
