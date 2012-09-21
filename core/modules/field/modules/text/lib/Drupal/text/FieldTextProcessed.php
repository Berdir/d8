<?php

/**
 * @file
 * Definition of Drupal\text\FieldTextProcessed.
 */

namespace Drupal\text;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\ReadOnlyException;
use Drupal\Core\TypedData\Type\String;
use InvalidArgumentException;

/**
 * A computed property for processing text with a format.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - text source: The text property containing the to be processed text.
 */
class FieldTextProcessed extends String {

  /**
   * The text property.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface
   */
  protected $text;

  /**
   * The text format property.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface
   */
  protected $format;

  /**
   * Implements TypedDataInterface::__construct().
   */
  public function __construct(array $definition) {
    $this->definition = $definition;

    if (!isset($definition['settings']['text source'])) {
      throw new InvalidArgumentException("The definition's 'source' key has to specify the name of the text property to be processed.");
    }
  }

  /**
   * Implements TypedDataInterface::setContext().
   */
  public function setContext(array $context) {
    $this->text = $context['parent']->get($this->definition['settings']['text source']);
    $this->format = $context['parent']->get('format');
  }

  /**
   * Implements TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {

    if (!isset($this->text)) {
      throw new InvalidArgumentException('Computed properties require context for computation.');
    }

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
   * Implements TypedDataInterface::setValue().
   */
  public function setValue($value) {
    if (isset($value)) {
      throw new ReadOnlyException('Unable to set a computed property.');
    }
  }
}
