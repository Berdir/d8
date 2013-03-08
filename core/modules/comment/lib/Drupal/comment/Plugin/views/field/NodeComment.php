<?php

/**
 * @file
 * Definition of Drupal\comment\Plugin\views\field\NodeComment.
 */

namespace Drupal\comment\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Component\Annotation\Plugin;

/**
 * Display node comment status.
 *
 * @ingroup views_field_handlers
 *
 * @Plugin(
 *   id = "node_comment",
 *   module = "comment"
 * )
 */
class NodeComment extends FieldPluginBase {

  function render($values) {
    $value = $this->get_value($values);
    switch ($value) {
      case COMMENT_HIDDEN:
      default:
        return t('Hidden');
      case COMMENT_CLOSED:
        return t('Closed');
      case COMMENT_OPEN:
        return t('Open');
    }
  }

}
