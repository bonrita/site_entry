<?php

namespace Drupal\site_entry\Plugin\rest\resource;

use Drupal\site_entry\ExternalLoginServiceInterface;
use Drupal\mm_rest\Plugin\ResourceBase;
use Drupal\mm_rest\Plugin\RestEntityProcessorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\mm_rest\CacheableMetaDataCollectorInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "external_logout:v1",
 *   label = @Translation("br external logout API (v1)"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/{domain}/{language}/external_logout",
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/{domain}/{language}/external_logout",
 *   }
 * )
 */
class ExternalLogOutRestResource extends ResourceBase {

  /**
   * The login service.
   *
   * @var \Drupal\site_entry\ExternalLoginServiceInterface
   */
  protected $loginService;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\mm_rest\Plugin\RestEntityProcessorManager $entity_processor
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\mm_rest\CacheableMetaDataCollectorInterface $cacheable_metadata_collector
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, Request $request, RestEntityProcessorManager $entity_processor, ConfigFactoryInterface $configFactory, CacheableMetaDataCollectorInterface $cacheable_metadata_collector, ExternalLoginServiceInterface $login_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $request, $entity_processor, $configFactory, $cacheable_metadata_collector);

    $this->loginService = $login_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('mm_rest'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('plugin.manager.mm_rest_entity_processor'),
      $container->get('config.factory'),
      $container->get('mm_rest.cacheable_metadata_collector'),
      $container->get('site_entry.external_login_service')
    );
  }

  /**
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($domain, $language) {
    $variables = [];
    $message = '%type: @message in %function (line %line of %file). LONG MESSAGE: @long_message';

    try {

      $this->loginService->logoutUser();
      $this->requestData = [
        'message' => $this->t('User has been logged out.'),
      ];

    } catch (\Exception $e) {
      $variables['@long_message'] = $e->getLongMessage();
      watchdog_exception('forms', $e, $message, $variables);
      throw new HttpException($e->getCode(), $e->getMessage(), $e);
    }

    return $this->responseData();
  }

  /**
   * {@inheritdoc}
   */
  protected function responseData() {
    return $this->requestData;
  }

}
