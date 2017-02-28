<?php

namespace Drupal\site_entry\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class ExternalLoginNormalizer.
 *
 * @package Drupal\site_entry\Normalizer
 */
class ExternalLoginNormalizer implements DenormalizerInterface, NormalizerInterface {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('json');


  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    return $object;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    return in_array($format, $this->formats);
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return array_key_exists('username', $data) && array_key_exists('password', $data) && is_string($data['username']) && is_string($data['password']);
  }

}
