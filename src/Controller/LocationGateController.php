<?php

namespace Drupal\site_entry\Controller;

use Drupal\country\CountryManagerInterface;
use Drupal\ips\IndustryManagerInterface;
use Drupal\site_entry\SiteEntryManager;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SiteEntryController.
 *
 * @package Drupal\site_entry\Controller
 */
class LocationGateController extends ControllerBase {

  /**
   * The current country service.
   *
   * @var \Drupal\ips\IndustryManagerInterface
   */
  protected $industry;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\country\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * @var \Drupal\site_entry\SiteEntryManager
   */
  protected $siteEntryManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(IndustryManagerInterface $industry, CountryManagerInterface $country_manager, SiteEntryManager $siteEntryService) {
    $this->industry = $industry;
    $this->countryManager = $country_manager;
    $this->siteEntryManager = $siteEntryService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ips.industry_manager'),
      $container->get('country.country_manager'),
      $container->get('site_entry.manager')
    );
  }

  /**
   * Page controller for Location Gate.
   *
   * @return array
   *   Render Array.
   */
  public function LocationGate() {

    list($regions, $countries) = $this->removeUnusedRegions();

    $build = [
      '#theme' => 'site_entry_location_gate',
      '#suggested_country_entity' => $this->countryManager->suggestCountry(),
      '#suggested_language' => $this->siteEntryManager->getPreferredLanguage(),
      '#regions' => $regions,
      '#countries_per_region' => $countries,
      '#languages_per_country' => $this->countryManager->getLanguagesPerCountry(),
    ];

    return $build;
  }

  /**
   * @param $counts
   * @return array
   */
  protected function removeUnusedRegions() {
    $regions = $this->countryManager->getRegions();
    $countries = $this->countryManager->getCountriesPerRegion();
    $domains = \Drupal::entityTypeManager()
      ->getStorage('domain')
      ->loadByProperties();

    foreach ($countries as $reg => &$counts) {
      /** @var \Drupal\country\Entity\Country $country */
      foreach ($counts as $key => $country) {
        $domain = "country_{$country->getSlug()}";

        if (!array_key_exists($domain, $domains)) {
          unset($counts[$key]);
        }
      }
    }

    // Remove all regions that don't have countries.
    foreach ($regions as $key => $region) {
      if (empty($countries[$key])) {
        unset($regions[$key]);
        unset($countries[$key]);
      }
    }

    return array($regions, $countries);
  }

}
