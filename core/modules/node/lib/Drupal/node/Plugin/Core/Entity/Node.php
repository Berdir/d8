<?php

/**
 * @file
 * Definition of Drupal\node\Plugin\Core\Entity\Node.
 */

namespace Drupal\node\Plugin\Core\Entity;

use Drupal\Core\Entity\EntityBCDecorator;

/**
 * Extends the EntityBCDecorator for nodes.
 *
 * @Plugin(
 *   id = "node",
 *   label = @Translation("Content"),
 *   bundle_label = @Translation("Content type"),
 *   module = "node",
 *   controller_class = "Drupal\node\NodeStorageController",
 *   render_controller_class = "Drupal\node\NodeRenderController",
 *   form_controller_class = {
 *     "default" = "Drupal\node\NodeFormController"
 *   },
 *   translation_controller_class = "Drupal\node\NodeTranslationController",
 *   base_table = "node",
 *   revision_table = "node_revision",
 *   uri_callback = "node_uri",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "nid",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   bundle_keys = {
 *     "bundle" = "type"
 *   },
 *   permission_granularity = "bundle"
 * )
 * We extend the EntityBCDecorator only to allow BC-nodes to be passed to
 * type-hinted functions.
 */
class Node extends EntityBCDecorator {

}
