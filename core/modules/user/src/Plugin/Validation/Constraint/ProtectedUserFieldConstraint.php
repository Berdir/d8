<?php

/**
 * @file
 * Contains \Drupal\user\Plugin\Validation\Constraint\ProtectedUserFieldConstraint.
 */

namespace Drupal\user\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Check if plain text password was provided for protected user field.
 *
 * @Plugin(
 *   id = "ProtectedUserFieldConstraint",
 *   label = @Translation("Password required for protected field change", context = "Validation")
 * )
 */
class ProtectedUserFieldConstraint extends Constraint {

  /**
   * Violation message.
   *
   * @var string
   */
  public $message = "Your current password is missing or incorrect; it's required to change the !name.";

}
