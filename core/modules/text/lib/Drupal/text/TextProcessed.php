<?php

/**
 * @file
 * Definition of Drupal\text\TextProcessed.
 */

namespace Drupal\text;

use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedData;
use Drupal\Core\TypedData\ReadOnlyException;
use InvalidArgumentException;

/**
 * A computed property for processing text with a format.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - text source: The text property containing the to be processed text.
 */
class TextProcessed extends TypedData {

  /**
   * Cached processed text.
   *
   * @var string|false
   */
  protected $processed = FALSE;

  /**
   * Overrides TypedData::__construct().
   */
  public function __construct(array $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);

    if (!isset($definition['settings']['text source'])) {
      throw new InvalidArgumentException("The definition's 'source' key has to specify the name of the text property to be processed.");
    }
  }

  /**
   * Overrides TypedData::setContext().
   */
  public function setContext($name = NULL, TypedDataInterface $parent = NULL) {
    parent::setContext($name, $parent);
    if (isset($parent)) {
      $this->text = $parent->{($this->definition['settings']['text source'])};
      $this->format = $parent->format;
    }
  }

  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    if ($this->processed !== FALSE) {
      return $this->processed;
    }

    if (empty($this->definition['settings']['text source'])) {
      throw new InvalidArgumentException('Computed properties require context for computation.');
    }
    $text = $this->parent->{($this->definition['settings']['text source'])};

    $field = $this->parent->getParent();
    $entity = $field->getParent();
    $instance = field_info_instance($entity->entityType(), $field->getName(), $entity->bundle());

    if (!empty($instance['settings']['text_processing']) && $this->parent->format) {
      $this->processed = check_markup($text, $this->parent->format, $entity->language()->id);
    }
    else {
      // Escape all HTML and retain newlines.
      // @see \Drupal\text\Plugin\field\formatter\TextPlainFormatter
      $this->processed = nl2br(check_plain($text));
    }
    return $this->processed;
  }

  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::setValue().
   */
  public function setValue($value, $notify = TRUE) {
    if (isset($value)) {
      $this->processed = $value;
    }
  }

}
