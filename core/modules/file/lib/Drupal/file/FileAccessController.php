<?php

/**
 * @file
 * Contains \Drupal\file\FileAccessController.
 */

namespace Drupal\file;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access controller for the file entity type.
 */
class FileAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    // Find out which (if any) fields of this type contain the file.
    $references = file_get_file_references($entity, NULL, FIELD_LOAD_CURRENT, $field_type);

    // Stop processing if there are no references in order to avoid returning
    // headers for files controlled by other modules. Make an exception for
    // temporary files where the host entity has not yet been saved (for example,
    // an image preview on a node/add form) in which case, allow download by the
    // file's owner.
    if (empty($references) && ($entity->isPermanent() || $entity->getOwner()->id() != $account->id())) {
      return ;
    }

    // Default to allow access.
    $denied = FALSE;
    // Loop through all references of this file. If a reference explicitly allows
    // access to the field to which this file belongs, no further checks are done
    // and download access is granted. If a reference denies access, eventually
    // existing additional references are checked. If all references were checked
    // and no reference denied access, access is granted as well. If at least one
    // reference denied access, access is denied.
    foreach ($references as $field_name => $field_references) {
      foreach ($field_references as $entity_type => $entities) {
        foreach ($entities as $entity) {
          $field = field_info_field($field_name);
          // Check view access for the field and entity the field belongs to. Deny
          // access if either of those returns FALSE.
          if (!$entity->access('view') || !field_access('view', $field, $entity_type, $entity)) {
            $denied = TRUE;
            continue;
          }

          // Otherwise, we have access to the field and the file can be
          // downloaded. We do not need further checks, break out of the loop.
          $denied = FALSE;
          break 3;
        }
      }
    }
    return !$denied;
  }

}
