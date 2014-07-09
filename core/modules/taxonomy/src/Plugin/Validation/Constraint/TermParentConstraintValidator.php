<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Plugin\Validation\Constraint\TermParentConstraintValidator.
 */

namespace Drupal\taxonomy\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the TermParent constraint.
 */
class TermParentConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if ($items) {
      $parent_term_id = $items->first()->value;
      // If a non-0 parent term id is specified, ensure it corresponds to a real
      // term in the same vocabulary.
      if ($parent_term_id && !\Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(array('tid' => $parent_term_id, 'vid' => $items->getEntity()->vid->value))) {
        $this->context->addViolation($constraint->message, array('%id' => $parent_term_id));
      }
    }
  }
}
