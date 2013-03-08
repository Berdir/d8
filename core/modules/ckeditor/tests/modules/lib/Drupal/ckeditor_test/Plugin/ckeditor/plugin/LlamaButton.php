<?php

/**
 * @file
 * Contains \Drupal\ckeditor_test\Plugin\ckeditor\plugin\LlamaButton.
 */

namespace Drupal\ckeditor_test\Plugin\ckeditor\plugin;

use Drupal\ckeditor\CKEditorPluginButtonsInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines a "LlamaButton" plugin, with a toolbar builder-enabled "llama" feature.
 *
 * @Plugin(
 *   id = "llama_button",
 *   label = @Translation("Llama Button"),
 *   module = "ckeditor_test"
 * )
 */
class LlamaButton extends Llama implements CKEditorPluginButtonsInterface {

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginButtonsInterface::getButtons().
   */
  function getButtons() {
    return array(
      'Llama' => array(
        'label' => t('Insert Lllama'),
      ),
    );
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getFile().
   */
  function getFile() {
    return drupal_get_path('module', 'ckeditor_test') . '/js/llama_button.js';
  }

}
