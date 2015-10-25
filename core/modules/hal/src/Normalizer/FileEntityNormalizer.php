<?php

/**
 * @file
 * Contains \Drupal\hal\Normalizer\FileEntityNormalizer.
 */

namespace Drupal\hal\Normalizer;

use Drupal\Component\Utility\SafeMarkup;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Converts the Drupal entity object structure to a HAL array structure.
 */
class FileEntityNormalizer extends ContentEntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\file\FileInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = array()) {
    $data = parent::normalize($entity, $format, $context);

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    // Avoid 'data' being treated as a field.
    $file_data = $data['data'][0]['value'];
    unset($data['data']);

    $entity = parent::denormalize($data, $class, $format, $context);

    // Decode and save to file if it's a new file.
    if (!isset($context['request_method']) || $context['request_method'] != 'patch') {
      $file_contents = base64_decode($file_data);
      $dirname = drupal_dirname($entity->getFileUri());
      file_prepare_directory($dirname, FILE_CREATE_DIRECTORY);
      if ($uri = file_unmanaged_save_data($file_contents, $entity->getFileUri())) {
        $entity->setFileUri($uri);
      }
      else {
        throw new RuntimeException(SafeMarkup::format('Failed to write @filename.', array('@filename' => $entity->getFilename())));
      }
    }

    return $entity;
  }

}
