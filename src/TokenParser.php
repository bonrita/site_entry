<?php

namespace Drupal\site_entry;

use Lcobucci\JWT\Parser;

/**
 * Class TokenParser.
 *
 * @package Drupal\site_entry
 */
class TokenParser {

  /**
   * @var string
   */
  protected $accessToken;

  /**
   * @var string
   */
  protected $tokenType;

  /**
   * @var int
   */
  protected $expiresIn;

  /**
   * @var string
   */
  protected $version;

  protected $scope;

  /**
   * TokenParser constructor.
   */
  public function __construct($login_response) {
    $this->tokenType = $login_response->token_type;
    $this->accessToken = $login_response->access_token;
    $this->expiresIn = $login_response->expires_in;
    $this->version = $login_response->version;
  }

  /**
   * Get token object.
   *
   * @return \Lcobucci\JWT\Token
   *   Token instance.
   */
  public function getToken() {
    return (new Parser())->parse($this->accessToken);
  }

  /**
   * @return string
   */
  public function getAccessToken() {
    return $this->accessToken;
  }

  /**
   * @return string
   */
  public function getTokenType() {
    return $this->tokenType;
  }

  /**
   * @return int
   */
  public function getExpiresIn() {
    return $this->expiresIn;
  }

  /**
   * @return string
   */
  public function getVersion() {
    return $this->version;
  }

}
