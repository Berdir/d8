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
   * @var string|null
   */
  protected $processed = NULL;

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
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    if ($this->processed !== NULL) {
      return $this->processed;
    }
    $text = $this->parent->{($this->definition['settings']['text source'])};

    $field = $this->parent->getParent();
    $entity = $field->getParent();

    if ($field->getFieldDefinition()->getFieldSetting('text_processing') && $this->parent->format) {
      // @todo: The entity language might not be the correct language to use,
      //   fix in https://drupal.org/node/2061331.
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
    $this->processed = $value;
    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

}
