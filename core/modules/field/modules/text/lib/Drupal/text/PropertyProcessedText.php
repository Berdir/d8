<?php

/**
 * @file
 * Definition of Drupal\text\PropertyProcessedText.
 */

namespace Drupal\text;
use Drupal\Core\TypedData\DataWrapperInterface;
use Drupal\Core\TypedData\DataReadOnlyException;
use InvalidArgumentException;

/**
 * The string property type.
 */
class PropertyProcessedText extends \Drupal\Core\TypedData\Type\String {

  /**
   * The text property.
   *
   * @var \Drupal\Core\TypedData\DataWrapperInterface
   */
  protected $text;

  /**
   * The text format property.
   *
   * @var \Drupal\Core\TypedData\DataWrapperInterface
   */
  protected $format;

  /**
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    $this->definition = $definition;

    if (!isset($context['parent'])) {
      throw new InvalidArgumentException('Computed properties require context for computation.');
    }
    if (!isset($definition['source'])) {
      throw new InvalidArgumentException("The definition's 'source' key has to specify the name of the text property to be processed.");
    }

    $this->text = $context['parent']->get($definition['source']);
    $this->format = $context['parent']->get('format');

  }

  /**
   * Implements DataWrapperInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    // @todo: Determine a way to get the field $instance here.
    // Either implement per-bundle property definition overrides or pass on
    // entity-context (entity type, bundle, property name). For now, we assume
    // text processing is enabled if a format is given.
    if ($this->format->value) {
      return check_markup($this->text->value, $this->format->value, $langcode);
    }
    else {
      // If no format is available, still make sure to sanitize the text.
      return check_plain($this->text->value);
    }
  }

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($value) {
    if (isset($value)) {
      throw new DataReadOnlyException('Unable to set a computed property.');
    }
  }
}
