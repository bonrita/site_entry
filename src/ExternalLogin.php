<?php

namespace Drupal\site_entry;

/**
 * Class ExternalLogin.
 *
 * @package Drupal\site_entry
 */
class ExternalLogin implements ExternalLoginInterface {

  const MESSAGE = '%type: @message in %function (line %line of %file). LONG MESSAGE: @long_message';

  /**
   * The client to connect to the service.
   *
   * @var \Drupal\site_entry\LoginClientInterface
   */
  protected $client;

  protected $username;
  protected $password;

  /**
   * The token instance.
   *
   * @var \Lcobucci\JWT\Token
   */
  protected $tokenInstance;

  /**
   * Login constructor.
   *
   * @param \Drupal\site_entry\LoginClientInterface $client
   *   The client to connect to the service.
   */
  public function __construct(LoginClientInterface $client, $username, $password) {
    $this->client = $client;
    $this->username = $username;
    $this->password = $password;
  }

  /**
   * {@inheritdoc}
   */
  public function login() {
    return $this->getTokenInstance();
  }

  /**
   * Get token.
   *
   * @return bool
   *   TRUE/ FALSE.
   */
  protected function getTokenInstance() {
    $success = TRUE;
    if (NULL == $this->tokenInstance) {
      $login_response = $this->client->performRequest($this->getPostData(), LoginClient::HTTP_POST);
      $response_data = json_decode($login_response->getBody()->getContents());
      $this->tokenInstance = (new TokenParser($response_data))->getToken();
    }

    return $success;

  }

  /**
   * Get user given name.
   *
   * @return string
   *   The username.
   */
  public function getUsername() {
    return $this->getTokenClaims()['given_name']->getValue();
  }

  /**
   * The time the token will expire.
   *
   * @return int
   *   The timestamp.
   */
  public function getTokenExpirationTime() {
    return $this->getTokenClaims()['exp']->getValue();
  }

  /**
   * The time the token was issued.
   *
   * @return int
   *   The timestamp.
   */
  public function getTokenIssuedTime() {
    return $this->getTokenClaims()['iat']->getValue();
  }

  /**
   * @return string
   *
   */
  public function getEmail() {
    return $this->getTokenClaims()['email']->getValue();
  }

  /**
   * Returns the token claim set.
   *
   * @return array
   *   A list of claims.
   */
  protected function getTokenClaims() {
    return $this->tokenInstance->getClaims();
  }

  /**
   * @return array
   */
  public function getPostData() {
    return [
      'username' => $this->username,
      'password' => $this->password,
      'application_id' => $this->client->getApplicationId()
    ];
  }

}
