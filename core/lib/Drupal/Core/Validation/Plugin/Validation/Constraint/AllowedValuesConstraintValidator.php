<?php

/**
 * @file
 * Contains \Drupal\Core\Validation\Plugin\Validation\Constraint\AllowedValuesConstraintValidator.
 */

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\TypedData\AllowedValuesInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;

/**
 * Validates the AllowedValues constraint.
 */
class AllowedValuesConstraintValidator extends ChoiceValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $typed_data = $this->context->getMetadata()->getTypedData();

    if ($typed_data instanceof AllowedValuesInterface) {
      $account = \Drupal::currentUser();
      $allowed_values = $typed_data->getSettableValues($account);
      $constraint->choices = $allowed_values;

      // If the data is complex, we have to validate its main property.
      if ($typed_data instanceof ComplexDataInterface) {
        $name = $typed_data->getMainPropertyName();
        if (!isset($name)) {
          throw new \LogicException('Cannot validate allowed values for complex data without a main property.');
        }
        $value = $typed_data->get($name)->getValue();
      }
    }

    // Although parent::validate() includes this check for $value, we add
    // this check here to make sure $value is checked before $constraint.
    if ($value === null) {
      return TRUE;
    }
    return parent::validate($value, $constraint);
  }
}
