<?php

namespace Drupal\site_entry;

/**
 * Interface ExternalLoginInterface.
 *
 * @package Drupal\site_entry
 */
interface ExternalLoginServiceInterface {

  /**
   * Login to the service.
   *
   * @param string $username
   *   The username.
   * @param string $password
   *   The password.
   *
   * @return bool
   *   TRUE/ FALSE.
   */
  public function login($username, $password);

  /**
   * Get the client instance.
   *
   * @return \Drupal\site_entry\LoginClientInterface
   *   The client interface.
   */
  public function getClient();

  /**
   * The login instance.
   *
   * @return \Drupal\site_entry\ExternalLogin
   *   The login instance.
   */
  public function getLoginInstance();

  /**
   * Start a session.
   *
   * The session is started only if the user is anonymous.
   */
  public function startSession();

  /**
   * Save user data to the session.
   */
  public function saveUserDataToSession();

  /**
   * Set cookie.
   *
   * @param int $expire
   *   When the cookie will expire.
   */
  public function setLoginCookie($expire = 0);

  /**
   * Unset or delete a cookie.
   */
  public function deleteLoginCookie();

  /**
   * Log out user.
   */
  public function logoutUser();

  /**
   * Generate hash.
   *
   * @param string $username
   *   The username.
   * @param string $email
   *   The email.
   * @param int $created_date
   *   The timestamp when user logged in.
   *
   * @return string
   *   The generated hash.
   */
  public function generateHash($username, $email, $created_date);

}
