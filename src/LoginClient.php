<?php

namespace Drupal\site_entry;


/**
 * Class LoginClient.
 *
 * @package Drupal\site_entry
 */
class LoginClient implements LoginClientInterface {

  const HTTP_POST = 'POST';
  const HTTP_GET = 'GET';

  const HTTP_PLAIN = 'http://';
  const HTTP_SSL = 'https://';


  protected $applicationID;

  /**
   * The login host.
   *
   * @var string
   */
  protected $host;

  /**
   * The endpoint.
   *
   * @var string
   */
  protected $endPoint;

  /**
   * Set the connection protocol.
   *
   * @var string
   */
  protected $protocol;

  /**
   * Client constructor.
   *
   * @param $applicationID
   * @param string $host
   */
  public function __construct($applicationID, $host, $end_point) {
    $this->applicationID = $applicationID;
    $this->host = $host;
    $this->endPoint = $end_point;

  }

  /**
   * @param $app_id
   * @return $this
   */
  public function setApplicationId($app_id) {
    $this->applicationID = $app_id;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function performRequest($data, $method) {
    $parameters = [];

    if (NULL == $this->host) {
      throw new Exception('No host has been set.', 110);
    }

    if (NULL == $this->endPoint) {
      throw new Exception('Endpoint has not been set.', 110);
    }

    if (NULL == $this->protocol) {
      throw new Exception('No Protocol has been set.', 110);
    }

    if (NULL == $this->applicationID) {
      throw new Exception('Application ID not set', 120);
    }

    $client = new \GuzzleHttp\Client(['base_uri' => $this->protocol . $this->host]);

    if (NULL != $data && self::HTTP_POST == $method) {
      $parameters['json'] = $data;
    }
//    try {
      $response = $client->request($method, $this->endPoint, $parameters);
//    } catch (\Exception $e) {
//
//    }
    return $response;
  }

  /**
   * The application ID.
   *
   * @return string
   *   The application ID.
   */
  public function getApplicationId() {
    return $this->applicationID;
  }

  /**
   * @return string
   */
  public function getHost() {
    return $this->host;
  }

  /**
   * @return string
   */
  public function getEndPoint() {
    return $this->endPoint;
  }

  /**
   * @return string
   */
  public function getProtocol() {

    if (FALSE == $this->protocol) {
      $this->protocol = self::HTTP_SSL;
    }

    return $this->protocol;
  }

  /**
   * @param string $protocol
   */
  public function setProtocol($protocol) {
    $this->protocol = $protocol;
  }

}
