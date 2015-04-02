<?php

/**
 * @file
 * Contains \Drupal\views_custom_cache_tag\Plugin\views\cache\CustomTag.
 */

namespace Drupal\views_custom_cache_tag\Plugin\views\cache;

use Drupal\views\Plugin\views\cache\CachePluginBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;

/**
 * Simple caching of query results for Views displays.
 *
 * @ingroup views_cache_plugins
 *
 * @ViewsCache(
 *   id = "custom_tag",
 *   title = @Translation("Custom Tag based"),
 *   help = @Translation("Tag based caching of data. Caches will persist until any related cache tags are invalidated.")
 * )
 */
class CustomTag extends CachePluginBase {

  /**
   * Overrides Drupal\views\Plugin\Plugin::$usesOptions.
   */
  protected $usesOptions = TRUE;

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('Custom Tag');
  }

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options = parent::defineOptions();
    $options['custom_tag'] = array('default' => '');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['custom_tag'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Custom tag list'),
      '#description' => $this->t('Custom tag list, separated by new lines. Caching based on custom cache tag must be manually cleared using custom code.'),
      '#default_value' => $this->options['custom_tag'],
    );

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $id = $this->view->storage->getCacheTags();
    $custom_tags = preg_split('/\r\n|[\r\n]/', $this->options['custom_tag']);
    $custom_tags = array_map('trim', $custom_tags);
    $custom_tags =  array_map(function ($tag){ return $this->view->getStyle()->tokenizeValue($tag, 0);}, $custom_tags);
    return Cache::mergeTags($custom_tags, $id);
  }

  /**
   * {@inheritdoc}
   */
  public function cacheExpire($type) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function cacheGet($type) {
    $result = parent::cacheGet($type);

    // This can be used to debug/test the views cache result.
    if ($type == 'results' && !$result && \Drupal::state()->get('views_custom_cache_tag.execute_debug', FALSE)) {
      drupal_set_message('Executing view ' . $this->view->storage->id() . ':' . $this->view->current_display . ':' . implode(',', $this->view->args) . ' (' . implode(',', $this->view->getCacheTags()) . ')');
    }
  }

}
