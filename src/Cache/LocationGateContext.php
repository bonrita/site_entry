<?php

namespace Drupal\site_entry\Cache;

use Drupal\Core\Cache\Context\CacheContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class LoginGateContext.
 *
 * Cache context ID: 'location_gate'.
 *
 * This context is added so as to make sure that drupal does not cache the home
 * page url as i need users to choose a language and an industry when they
 * land on the home page.
 *
 * @package Drupal\site_entry\Cache
 */
class LocationGateContext implements CacheContextInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentRequest;

  /**
   * ExternalLoginContext constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('BR location gate');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $context = 0;

    if ($this->currentRequest->cookies->has('country') && $this->currentRequest->cookies->has('language')) {
      $domain = $this->currentRequest->cookies->get('country');
      $language = $this->currentRequest->cookies->get('language');
      $context = $domain . $language;
    }
    return $context;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
