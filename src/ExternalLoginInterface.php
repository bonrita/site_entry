<?php

namespace Drupal\site_entry;

/**
 * Interface ExternalLoginInterface.
 *
 * @package Drupal\site_entry
 */
interface ExternalLoginInterface {

  /**
   * Login to the service.
   *
   * @return bool
   *   TRUE/ FALSE.
   */
  public function login();

}
