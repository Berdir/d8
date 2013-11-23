<?php

/**
 * @file
 * Contains \Drupal\editor\Plugin\EditorBase.
 */

namespace Drupal\editor\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\editor\Entity\Editor;
use Drupal\editor\Plugin\EditorPluginInterface;

/**
 * Defines a base class from which other modules providing editors may extend.
 *
 * This class provides default implementations of the EditorPluginInterface so
 * that classes extending this one do not need to implement every method.
 *
 * Plugins extending this class need to define a plugin definition array through
 * annotation. These definition arrays may be altered through
 * hook_editor_info_alter(). The definition includes the following keys:
 *
 * - id: The unique, system-wide identifier of the text editor. Typically named
 *   the same as the editor library.
 * - label: The human-readable name of the text editor, translated.
 * - supports_inline_editing: Whether the editor supports the inline editing
 *   provided by the Edit module.
 *
 * A complete sample plugin definition should be defined as in this example:
 *
 * @code
 * @Editor(
 *   id = "myeditor",
 *   label = @Translation("My Editor"),
 *   supports_inline_editing = FALSE
 * )
 * @endcode
 */
abstract class EditorBase extends PluginBase implements EditorPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getDefaultSettings() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state, Editor $editor) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormValidate(array $form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormSubmit(array $form, array &$form_state) {
  }

}
