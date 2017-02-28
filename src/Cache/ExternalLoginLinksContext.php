<?php

namespace Drupal\site_entry\Cache;

use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ExternalLoginContext.
 *
 * Cache context ID: 'external_login_links'.
 *
 * @package Drupal\site_entry\Cache
 */
class ExternalLoginLinksContext implements CacheContextInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ExternalLoginContext constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('BR external login links');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $config = $this->configFactory->get('site_entry.external_login');
    $string = $config->get('connection_settings.forgot_password') . $config->get('connection_settings.login_help') . $config->get('connection_settings.learn_br') . $config->get('connection_settings.goto_br');

    $hash = md5($string);

    return $hash;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
