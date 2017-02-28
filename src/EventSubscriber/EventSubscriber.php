<?php

namespace Drupal\site_entry\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Session\AccountProxy;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Drupal\Core\Render\HtmlResponse;

/**
 * Class EventSubscriber.
 *
 * @package Drupal\site_entry
 */
class EventSubscriber implements EventSubscriberInterface {

  const COUNTRY_CODE_LENGTH = 2;
  const LANGUAGE_CODE_LENGTH = 2;

  /**
   * This method is called whenever the kernel.request event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function onKernelRequest(GetResponseEvent $event) {

    $request = $event->getRequest();
    $accept_html = strpos($request->headers->get('accept'), 'html') !== FALSE;

    if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST
      && $accept_html
      && $this->isHomePage($request)
    ) {

      // @todo Use setter(?) injection to load service.
      /** @var \Drupal\site_entry\SiteEntryManagerInterface $siteEntryManager */
      $siteEntryManager = \Drupal::service('site_entry.manager');
      /** @var \Drupal\ips\IndustryManagerInterface $industryManager */
      $industryManager = \Drupal::service('ips.industry_manager');

      // Set preferred country.
      list($country_code, $language_code) = $this->frontPageData($request->getPathInfo());

      // Check to see if we already have cookies set for language and domain.
      if (empty($country_code) && empty($language_code) && $request->cookies->get('country') && $request->cookies->get('language')) {
        $country_code = $request->cookies->get('country');
        $language_code = $request->cookies->get('language');
      }

      // If language and country are known set them.
      if (!empty($country_code) && !empty($language_code)) {
        $siteEntryManager->setPreferredCountry($country_code);
        $siteEntryManager->setPreferredLanguage($language_code);
      }

      // Redirect to location gate if no location cookie was set.
      if (empty($siteEntryManager->getPreferredCountry())) {
        $url = $siteEntryManager->getLocationGateURL();
        $response = new RedirectResponse($url, 302);
        $event->setResponse($response);
        return;
      }

      // Redirect to brand movie if no industry cookie was set.
      if (!$industryManager->getPreferredIndustry()) {
        $url = $siteEntryManager->getBrandMoviesURL($country_code, $language_code);
        $response = new RedirectResponse($url, 302);
        $event->setResponse($response);
        return;
      }

      // If location and industry are known, redirect to Industry landing page.
      $url = $siteEntryManager->getIndustryHomeURL();
      $response = new RedirectResponse($url, 302);
      $event->setResponse($response);
      return;
    }

    // Check if domain and language exists.
    $format = $request->getRequestFormat();
    if ($request->getRequestUri() <> '/location-gate' && $accept_html && $format <> 'json') {
      $redirect_url = $this->getRedirectUrl($request);
      if (!empty($redirect_url)) {
        $response = new RedirectResponse($redirect_url, 302);
        $event->setResponse($response);
        return;
      }
    }

  }

  /**
   * The request is for the home page.
   *
   * The home page can be:
   * - Global home page ('/')
   * - Country home page ('/nl')
   * - Localized country home page ('/nl/nl')
   *
   * For performance reason, we don't use the route, but take data directly from
   * the request path.
   *
   * @param Request $request
   *
   * @return boolean
   *   True of the current request is for any of the home pages.
   */
  protected function isHomePage($request) {
    $path = $request->getPathInfo();

    list($country, $language) = $this->frontPageData($path);
    $home = !($country === FALSE || $language === FALSE);

    return $home;
  }

  /**
   * Extracts country and language data from the request path.
   *
   * @param $path
   *   Path string
   *
   * @return array
   *   Array with $country and $language.
   */
  protected function frontPageData($path) {
    $elements = [];
    $count = 0;
    $path = trim($path, '/');

    if ($path) {
      $elements = explode('/', $path);
      $count = count($elements);
    }

    // We only do a quick-and-dirty check for country and language to determine
    // if we are on a front page. In the Location gate the country and language
    // is chosen.
    switch ($count){
      // Corporate home page. Example: br.com
      case 0:
        $country = $language = '';
        break;

      // Localized country home page. Example: br.com/nl/nl
      case 2:
        $country = $this->getCountryCode($elements[0]);
        $language = $this->getLanguageCode($elements[1]);
        break;

      // Country home page. Example: br.com/nl
      case 1:
        $country = $this->getCountryCode($elements[0]);
        $language = '';
        break;

      // Any other page. Example: br.com/nl/nl/contact
      default:
        $country = $language = FALSE;
        break;
    }

    return [$country, $language];
  }

  /**
   * @param $element
   *
   * @return string
   */
  protected function getCountryCode($element) {
    $is_valid = strlen($element) == $this::COUNTRY_CODE_LENGTH && strtolower($element) == $element;
    return $is_valid ? $element : FALSE;
  }

  /**
   * @param $element
   *
   * @return string
   */
  protected function getLanguageCode($element) {
    $is_valid = strlen($element) == $this::LANGUAGE_CODE_LENGTH && strtolower($element) == $element;
    return $is_valid ? $element : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST] = ['onKernelRequest', 100];

    return $events;
  }

  /**
   * Check if domain and language exist otherwise redirect to global.
   * @param $request
   * @return string
   */
  protected function getRedirectUrl($request) {
    $domain_exists = FALSE;
    $language_exists = FALSE;
    $count = 0;
    $redirect_url = '';
    $path = $request->getPathInfo();
    $path = trim($path, '/');
    $domains = \Drupal::entityTypeManager()
      ->getStorage('domain')
      ->loadByProperties();

    if ($path) {
      $elements = explode('/', $path);
      $count = count($elements);
    }

    $key = "country_{$elements[0]}";
    if (array_key_exists($key, $domains)) {
      $domain_exists = TRUE;

      $languages_per_country = \Drupal::service('country.country_manager')
        ->getLanguagesPerCountry();

      if (!empty($languages_per_country[$elements[0]])
        && array_key_exists($elements[0], $languages_per_country)
        && !empty($languages_per_country[$elements[0]][$elements[1]])
      ) {
        $language_exists = TRUE;
      }
    }

    if (!$domain_exists && !$language_exists && $path) {
      // If all are not defined redirect to global domain.
      $redirect_url = "/aa/en/industry-select";

      // Set defaults as the prefered.
      $site_entry_manager = \Drupal::service('site_entry.manager');
      $site_entry_manager->setPreferredCountry('aa');
      $site_entry_manager->setPreferredLanguage('en');
      return $redirect_url;
    }
    elseif (!$language_exists && $path) {
      $languages_per_country = \Drupal::service('country.country_manager')
        ->getLanguagesPerCountry();
      $languages_available = array_values($languages_per_country[$elements[0]]);
      $language_id = $languages_available[0]->getId();
      $redirect_url = "/{$elements[0]}/{$language_id}/industry-select";
      return $redirect_url;
    }

    return $redirect_url;
  }

}
