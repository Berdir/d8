<?php

/**
 * @file
 * Contains \Drupal\file\Plugin\Validation\Constraint\FileValidationConstraintValidator.
 */

namespace Drupal\file\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks if the current user has access to newly referenced entities.
 */
class FileValidationConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    // Get the file to execute validators.
    $file = $value->get('entity')->getTarget()->getValue();
    // Get the validators.
    $validators = $value->getUploadValidators();
    // Checks that a file meets the criteria specified by the validators.
    if ($errors = file_validate($file, $validators)) {
      $this->context->addViolation($constraint->message);
    }
  }

}
