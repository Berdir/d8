<?php

/**
 * @file
 * Contains \Drupal\hal\Tests\NormalizeTest.
 */

namespace Drupal\hal\Tests;

/**
 * Test the HAL normalizer.
 */
class NormalizeTest extends NormalizerTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Normalize Test',
      'description' => 'Test that entities can be normalized in HAL.',
      'group' => 'HAL',
    );
  }

  /**
   * Tests the normalize function.
   */
  public function testNormalize() {
    $target_entity_de = entity_create('entity_test', (array('langcode' => 'de', 'entity_ref' => NULL)));
    $target_entity_de->save();
    $target_entity_en = entity_create('entity_test', (array('langcode' => 'en', 'entity_ref' => NULL)));
    $target_entity_en->save();

    // Create a German entity.
    $values = array(
      'langcode' => 'de',
      'name' => $this->randomName(),
      'user_id' => 1,
      'test_text' => array(
        'value' => $this->randomName(),
        'format' => 'full_html',
      ),
      'entity_ref' => array(
        'target_id' => $target_entity_de->id(),
      ),
    );
    // Array of translated values.
    $translation_values = array(
      'name' => $this->randomName(),
      'entity_ref' => array(
        'target_id' => $target_entity_en->id(),
      )
    );

    $entity = entity_create('entity_test', $values);
    $entity->save();
    // Add an English value for name and entity reference properties.
    $entity->getTranslation('en')->set('name', array(0 => array('value' => $translation_values['name'])));
    $entity->getTranslation('en')->set('entity_ref', array(0 => $translation_values['entity_ref']));
    $entity->save();

    $type_uri = url('rest/type/entity_test/entity_test', array('absolute' => TRUE));
    $relation_uri = url('rest/relation/entity_test/entity_test/entity_ref', array('absolute' => TRUE));

    $expected_array = array(
      '_links' => array(
        'curies' => array(
          array(
            'href' => '/relations',
            'name' => 'site',
            'templated' => true,
          ),
        ),
        'self' => array(
          'href' => $this->getEntityUri($entity),
        ),
        'type' => array(
          'href' => $type_uri,
        ),
        $relation_uri => array(
          array(
            'href' => $this->getEntityUri($target_entity_de),
            'lang' => 'de',
          ),
          array(
            'href' => $this->getEntityUri($target_entity_en),
            'lang' => 'en',
          ),
        ),
      ),
      '_embedded' => array(
        $relation_uri => array(
          array(
            '_links' => array(
              'self' => array(
                'href' => $this->getEntityUri($target_entity_de),
              ),
              'type' => array(
                'href' => $type_uri,
              ),
            ),
            'uuid' => array(
              array(
                'value' => $target_entity_de->uuid(),
              ),
            ),
            'lang' => 'de',
          ),
          array(
            '_links' => array(
              'self' => array(
                'href' => $this->getEntityUri($target_entity_en),
              ),
              'type' => array(
                'href' => $type_uri,
              ),
            ),
            'uuid' => array(
              array(
                'value' => $target_entity_en->uuid(),
              ),
            ),
            'lang' => 'en',
          ),
        ),
      ),
      'uuid' => array(
        array(
          'value' => $entity->uuid(),
        ),
      ),
      'langcode' => array(
        array(
          'value' => 'de',
        ),
      ),
      'name' => array(
        array(
          'value' => $values['name'],
          'lang' => 'de',
        ),
        array(
          'value' => $translation_values['name'],
          'lang' => 'en',
        ),
      ),
      'test_text' => array(
        array(
          'value' => $values['test_text']['value'],
          'format' => $values['test_text']['format'],
        ),
      ),
    );

    $normalized = $this->serializer->normalize($entity, $this->format);
    $this->assertEqual($normalized['_links']['self'], $expected_array['_links']['self'], 'self link placed correctly.');
    // @todo Test curies.
    // @todo Test type.
    $this->assertFalse(isset($normalized['id']), 'Internal id is not exposed.');
    $this->assertEqual($normalized['uuid'], $expected_array['uuid'], 'Non-translatable fields is normalized.');
    $this->assertEqual($normalized['name'], $expected_array['name'], 'Translatable field with multiple language values is normalized.');
    $this->assertEqual($normalized['test_text'], $expected_array['test_text'], 'Field with properties is normalized.');
    $this->assertEqual($normalized['_embedded'][$relation_uri], $expected_array['_embedded'][$relation_uri], 'Entity reference field is normalized.');
    $this->assertEqual($normalized['_links'][$relation_uri], $expected_array['_links'][$relation_uri], 'Links are added for entity reference field.');
  }

  /**
   * Constructs the entity URI.
   *
   * @param $entity
   *   The entity.
   *
   * @return string
   *   The entity URI.
   */
  protected function getEntityUri($entity) {
    $entity_uri_info = $entity->uri();
    return url($entity_uri_info['path'], array('absolute' => TRUE));
  }

}
