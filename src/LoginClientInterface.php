<?php

namespace Drupal\site_entry;

/**
 * Interface LoginClientInterface.
 *
 * @package Drupal\site_entry
 */
interface LoginClientInterface {

  /**
   * Send a request.
   *
   * @param $endpoint
   * @param null $data
   * @param string $method
   * @throws ClientException|Exception
   * @return \GuzzleHttp\Psr7\Response
   */
  public function performRequest($data, $method);

}
