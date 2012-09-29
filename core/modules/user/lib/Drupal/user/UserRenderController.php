<?php
namespace Drupal\user;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRenderController;

class UserRenderController extends EntityRenderController {

  public function buildContent(array &$entities = array(), $view_mode = 'full', $langcode = NULL) {
    $return = array();
    if (empty($entities)) {
      return $return;
    }

    parent::buildContent($entities, $view_mode, $langcode);
    foreach ($entities as $key => $entity) {
      $this->prepareView($entity, $entity->content['#view_mode'], $langcode);
      $return[$key] = $entity->content;
    }
    return $return;
  }

  protected function getBuildDefaults(EntityInterface $entity, $view_mode, $langcode) {
    $return = parent::getBuildDefaults($entity, $view_mode, $langcode);

    // @todo rename "theme_user_profile" to "theme_user", 'account' to 'user'.
    $return['#theme'] = 'user_profile';
    $return['#account'] = $return['#user'];

    return $return;
  }
}
