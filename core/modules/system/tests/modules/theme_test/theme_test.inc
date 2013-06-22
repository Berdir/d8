<?php

/**
 * Returns HTML for the 'theme_test' theme hook used by tests.
 */
function theme_theme_test($variables) {
  return 'Theme hook implementor=theme_theme_test(). Foo=' . $variables['foo'];
}

/**
 * Preprocesses variables for theme_theme_test().
 */
function template_preprocess_theme_test(&$variables) {
  $variables['foo'] = 'template_preprocess_theme_test';
}

/**
 * Returns HTML for the 'theme_test_preprocess' theme hook used by tests.
 */
function theme_theme_test_preprocess($variables) {
  return 'Theme hook implementor=theme_theme_test_preprocess(). Foo=' . $variables['foo'];
}

/**
 * Implements hook_preprocess_HOOK() for theme_theme_test_preprocess().
 *
 * Despite not having a corresponding theme function for this suggestion, the
 * specific preprocess function should still be used.
 */
function template_preprocess_theme_test_preprocess(&$variables) {
  $variables['foo'] = 'template_preprocess_theme_test_preprocess';
}


