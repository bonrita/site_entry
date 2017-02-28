<?php

namespace Drupal\site_entry;

/**
 * Interface CookieServiceInterface.
 *
 * @package Drupal\site_entry
 */
interface SiteEntryManagerInterface {

  /**
   * Cookie lifetime (1 year).
   */
  const COOKIE_LIFETIME = 31104000;

  /**
   * Gets the URL of the brand movie page.
   *
   * The full URL including country and language prefixes.
   */
  public function getLocationGateURL();

  /**
   * Gets the URL of the brand movies page.
   *
   * The page houses all brand movies of that country.
   * It shows one brand movie from each industry.
   *
   * The full URL including country and language prefixes.
   */
  public function getBrandMoviesURL($country_code, $language_code);

  /**
   * Gets the Node of the brand movie page.
   *
   * @return \Drupal\node\Entity\Node
   */
  public function getBrandMovie();

  /**
   * Gets the URL of the industry landing page.
   *
   * The full URL including country and language prefixes.
   */
  public function getIndustryHomeURL();

  /**
   * Sets the language the user wants.
   *
   * @param string
   *   Language code string. 2 char, lower case.
   */
  public function setPreferredLanguage($language);

  /**
   * Load the user preferred language stored in a cookie.
   *
   * @return string
   *   Language code or empty when not set.
   */
  public function getPreferredLanguage();

  /**
   * Sets the country the user wants.
   *
   * @param string
   *   Country code string. 2 char, lower case.
   */
  public function setPreferredCountry($country);

  /**
   * Load the user preferred location stored in a cookie.
   *
   * @return string
   *   Country code or empty when not set.
   */
  public function getPreferredCountry();

}
