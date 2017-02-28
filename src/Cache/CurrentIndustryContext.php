<?php

namespace Drupal\site_entry\Cache;

use Drupal\Core\Cache\Context\CacheContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class LoginGateContext.
 *
 * Cache context ID: 'current_industry'.
 *
 * This context is added so as to make sure that parts of the site that
 * depend on the industry cookie are updated appropriately.
 *
 * @package Drupal\site_entry\Cache
 */
class CurrentIndustryContext implements CacheContextInterface {

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
    return t('BR current industry');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $context = 0;

    if ($this->currentRequest->cookies->has('industry')) {
      $context = $this->currentRequest->cookies->get('industry');
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
