<?php

namespace Drupal\site_entry\Cache;

use Drupal\Core\Cache\Context\CacheContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ExternalLoginContext.
 *
 * Cache context ID: 'external_login'.
 *
 * @package Drupal\site_entry\Cache
 */
class ExternalLoginContext implements CacheContextInterface {

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
    return t('BR external login');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->currentRequest->cookies->has('external_login') ? $this->currentRequest->cookies->get('external_login') : '0';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
