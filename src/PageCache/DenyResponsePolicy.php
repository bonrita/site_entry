<?php

namespace Drupal\site_entry\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DenyResponsePolicy.
 *
 * This policy rule denies caching of responses generated when pages:
 * - don't have a domain or language prefix.
 * - When the language a d
 * accesses the base url instead of being redirected to the location gate.
 *
 * @package Drupal\site_entry\PageCache
 */
class DenyResponsePolicy implements ResponsePolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Response $response, Request $request) {
    $allow = TRUE;
    $count = 0;
    $path = $request->getPathInfo();
    $path = trim($path, '/');


    if (empty($path)) {
      $allow = FALSE;
    }
    else {
      $elements = explode('/', $path);
      $count = count($elements);
    }

    if (!$request->cookies->has('language') || !$request->cookies->has('country')) {
      $allow = FALSE;
    }

    if ($count >= 2 && !$request->cookies->has('industry')) {
      $allow = FALSE;
    }

    return $allow ? NULL : RequestPolicyInterface::DENY;
  }

}
