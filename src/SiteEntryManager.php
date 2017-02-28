<?php

namespace Drupal\site_entry;

use Drupal\ips\IndustryManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The Site Entry manager service.
 *
 * @package Drupal\site_entry
 */
class SiteEntryManager implements SiteEntryManagerInterface {

  /**
   * The language the visitor wants to see the content in.
   *
   * @var string
   */
  protected $preferredLanguage;

  /**
   * The country the visitor wants to see the content of.
   *
   * @var string
   */
  protected $preferredCountry;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\ips\IndustryManagerInterface
   */
  protected $industryManager;

  /**
   * CookieService constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   */
  public function __construct(RequestStack $request_stack, QueryFactory $entityQuery, EntityTypeManagerInterface $entityTypeManager, IndustryManagerInterface $industryManager) {
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->entityQuery = $entityQuery;
    $this->entityTypeManager = $entityTypeManager;
    $this->industryManager = $industryManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocationGateURL() {
    return '/location-gate';
  }

  /**
   * {@inheritdoc}
   */
  public function getBrandMoviesURL($country_code, $language_code) {
    return "/$country_code/$language_code/industry-select";
  }

  /**
   * {@inheritdoc}
   */
  public function getBrandMovie() {
    $node = NULL;

    $language = $this->getPreferredLanguage();

    // Load the Brand Movie. We assume there is only one.
    /** @var \Drupal\node\Entity\Node $node */
    $nid = $this->entityQuery->get('node')
      ->condition('type', 'movie')
      ->condition('status', 1)
      ->range(0, 1)
      ->execute();

    if ($nid) {
      $node = $this->entityTypeManager->getStorage('node')->load(reset($nid));
      if ($language && $node->hasTranslation($language)) {
        $node = $node->getTranslation($language);
      }
    }

    return $node;
  }

  /**
   * {@inheritdoc}
   */
  public function getIndustryHomeURL() {
    $url = '';
    $country = $this->getPreferredCountry();
    $language = $this->getPreferredLanguage();

    // Load the Industry landing page. We assume there is only one.
    /** @var \Drupal\node\Entity\Node $node */
    $tid = $this->industryManager->getPreferredIndustry();
    $nids = $this->entityQuery->get('node')
      ->condition('type', 'industry')
      ->condition('field__term_industry', $tid)
      ->addTag('node_access')
      ->condition('status', 1)
      ->range(0, 1)
      ->execute();

    if ($nids) {
      $node = $this->entityTypeManager->getStorage('node')->load(reset($nids));
      if ($language && $node->hasTranslation($language)) {
        $node = $node->getTranslation($language);
      }
      $url = $node->urlInfo()->toString();
    }

    return "/$country" . $url;
  }

  /**
   * {@inheritdoc}
   */
  public function setPreferredLanguage($language) {

    // We store the cookie we set to re-use is later during this page call.
    $params = session_get_cookie_params();
    setcookie('language', $language, REQUEST_TIME + $this::COOKIE_LIFETIME, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    $this->preferredLanguage = $language;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreferredLanguage() {
    $language = '';

    if (!isset($this->preferredLanguage)) {
      $cookies = $this->currentRequest->cookies;
      if ($cookies->has('language')) {
        $language = $cookies->get('language');
        $this->preferredLanguage = $language;
      }
    }
    else {
      $language = $this->preferredLanguage;
    }

    return $language;
  }

  /**
   * {@inheritdoc}
   */
  public function setPreferredCountry($country) {

    // We store the cookie we set to re-use is later during this page call.
    $params = session_get_cookie_params();
    setcookie('country', $country, REQUEST_TIME + $this::COOKIE_LIFETIME, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    $this->preferredCountry = $country;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreferredCountry() {
    $country = '';

    if (!isset($this->preferredCountry)) {
      $cookies = $this->currentRequest->cookies;
      if ($cookies->has('country')) {
        $country = $cookies->get('country');
        $this->preferredCountry = $country;
      }
    }
    else {
      $country = $this->preferredCountry;
    }

    return $country;
  }

}
