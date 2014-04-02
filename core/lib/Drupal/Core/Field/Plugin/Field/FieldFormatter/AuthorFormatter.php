<?php

/**
 * @file
 * Contains \Drupal\Core\Field\Plugin\Field\FieldFormatter\AuthorFormatter.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'author' formatter.
 *
 * @FieldFormatter(
 *   id = "author",
 *   label = @Translation("Author"),
 *   description = @Translation("Display the referenced author user entity."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class AuthorFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $elements = array();

    foreach ($items as $delta => $item) {
      /** @var $referenced_user \Drupal\user\UserInterface */
      if ($referenced_user = $item->entity) {
        $elements[$delta] = array(
          '#theme' => 'username',
          '#account' => $referenced_user,
          '#link_options' => array('attributes' => array('rel' => 'author')),
        );
      }
    }

    return $elements;
  }

}
