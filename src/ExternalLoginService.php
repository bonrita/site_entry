<?php

namespace Drupal\site_entry;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\user\PrivateTempStoreFactory;

/**
 * Class ExternalLogin.
 *
 * @package Drupal\site_entry
 */
class ExternalLoginService implements ExternalLoginServiceInterface {

  /**
   * The exception message template.
   */
  const MESSAGE = '%type: @message in %function (line %line of %file). LONG MESSAGE: @long_message';

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The login instance.
   *
   * @var \Drupal\site_entry\ExternalLogin
   */
  protected $loginInstance;

  /**
   * The session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Gets the current active user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The private temporary store.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStore;

  protected $sessionStarted;

  /**
   * EloquaServiceBase constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   The session manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current active user.
   * @param \Drupal\user\PrivateTempStoreFactory $user_private_temp_store
   *   The private temporary store.
   */
  public function __construct(ConfigFactoryInterface $config_factory, SessionManagerInterface $session_manager, AccountProxyInterface $current_user, PrivateTempStoreFactory $user_private_temp_store) {
    $this->configFactory = $config_factory;
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;
    $this->tempStore = $user_private_temp_store->get('external_login');
    $this->sessionStarted = $this->sessionManager->isStarted();
  }

  /**
   * {@inheritdoc}
   */
  public function login($username, $password) {
    $client = $this->getClient();
    $client->setProtocol(LoginClient::HTTP_PLAIN);

    $this->loginInstance = new ExternalLogin($client, $username, $password);

    return $this->loginInstance->login();
  }

  /**
   * {@inheritdoc}
   */
  public function getClient() {
    $credentials = $this->configFactory->get('site_entry.external_login');

    $application_id = $credentials->get('connection_settings.application_id');
    $end_point = '/' . $credentials->get('connection_settings.end_point');
    $host = $credentials->get('connection_settings.host');

    return new LoginClient($application_id, $host, $end_point);
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginInstance() {
    return $this->loginInstance;
  }

  /**
   * {@inheritdoc}
   */
  public function startSession() {

    // We need to explicitly set a value in the session,
    // before the session is persisted for anonymous users.
    // For some strange reason, the private store doesn't trigger this,
    // so we do it here.
    $currentRequest = \Drupal::requestStack()->getCurrentRequest();
    if (!$this->sessionStarted && $this->currentUser->isAnonymous() && !$currentRequest->getSession()
        ->get('session_started') != NULL
    ) {
      $currentRequest->getSession()->set('session_started', TRUE);
      $this->sessionStarted = TRUE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function saveUserDataToSession() {
    $request = \Drupal::requestStack()->getCurrentRequest();
    $user = [
      'given_name' => $this->loginInstance->getUsername(),
      'email' => $this->loginInstance->getEmail(),
      'expire' => $this->loginInstance->getTokenExpirationTime(),
      'issued' => $this->loginInstance->getTokenIssuedTime(),
      'user-agent' => $request->headers->get('User-Agent', ''),
    ];

    $this->tempStore->set('external_user', $user);
  }

  /**
   * {@inheritdoc}
   */
  public function setLoginCookie($expire = 0) {
    // Set cookie which expires when the browser is closed.
    $params = session_get_cookie_params();

    // Generate hash.
    $username = $this->loginInstance->getUsername();
    $email = $this->loginInstance->getEmail();
    $created = $this->loginInstance->getTokenIssuedTime();

    $hash = $this->generateHash($username, $email, $created);

    setcookie('external_login', $hash, $expire, $params['path'], $params['domain'], $params['secure'], $params['httponly']);

    if ($this->sessionStarted) {
      $currentRequest = \Drupal::requestStack()->getCurrentRequest();
      $currentRequest->getSession()->set('external_login', $hash);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLoginCookie() {
    $params = session_get_cookie_params();

    // Assure that the expiration date is in the past,
    // to trigger the removal mechanism in the browser.
    setcookie('external_login', '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }

  /**
   * {@inheritdoc}
   */
  public function logoutUser() {
    $this->deleteLoginCookie();

    if ($this->currentUser->isAnonymous() && $this->sessionManager->isStarted()) {
      $this->sessionManager->destroy();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateHash($username, $email, $created_date) {
    $string = $username . $email . $created_date;
    return md5($string);
  }

}
