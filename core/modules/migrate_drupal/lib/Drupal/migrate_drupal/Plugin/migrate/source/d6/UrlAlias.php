<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\UrlAlias.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

/**
 * Drupal 6 url aliases source from database.
 *
 * @PluginID("drupal6_url_alias")
 */
class UrlAlias extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('url_alias', 'ua')
      ->fields('ua', array('pid', 'src', 'dst', 'language'));
    $query->orderBy('pid');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'pid' => t('The numeric identifier of the path alias.'),
      'src' => t('The internal path.'),
      'dst' => t('The user set path alias.'),
      'language' => t('The language code of the url alias.'),
    );
  }

}
