<?php

namespace Drupal\site_entry\Controller;

use Drupal\country\CountryManagerInterface;
use Drupal\ips\IndustryManagerInterface;
use Drupal\site_entry\SiteEntryManager;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\node\Entity\Node;

/**
 * Class SiteEntryController.
 *
 * @package Drupal\site_entry\Controller
 */
class BrandMovieController extends ControllerBase {

  /**
   * The current country service.
   *
   * @var \Drupal\ips\IndustryManagerInterface
   */
  protected $industry;

  /**
   * @var \Drupal\country\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * @var \Drupal\site_entry\SiteEntryManager
   */
  protected $siteEntryManager;

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * {@inheritdoc}
   */
  public function __construct(IndustryManagerInterface $industry, CountryManagerInterface $country_manager, SiteEntryManager $siteEntryService, QueryFactory $entityQuery) {
    $this->industry = $industry;
    $this->countryManager = $country_manager;
    $this->siteEntryManager = $siteEntryService;
    $this->entityQuery = $entityQuery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ips.industry_manager'),
      $container->get('country.country_manager'),
      $container->get('site_entry.manager'),
      $container->get('entity.query')
    );
  }

  /**
   * Page controller for Brand movie.
   *
   * @return array
   *   Render Array.
   */
  // @todo Will the movie be included in the movie node, or the movie node into this controller?
  public function brandMovie(Request $request) {
    // At this stage override the cookie values and set the preferred
    // country the user chose.
    $path = $request->getPathInfo();
    $path = trim($path, '/');
    $elements = explode('/', $path);
    $this->siteEntryManager->setPreferredCountry($elements[0]);
    $this->siteEntryManager->setPreferredLanguage($elements[1]);

    // Build cache metadata for the data that is added to the template.
    $cacheableMetadata = new CacheableMetadata();
    $cache_list = [];
    $industries = [];

    $domain = $this->siteEntryManager->getPreferredCountry();
    $language_code = $this->siteEntryManager->getPreferredLanguage();

    // Chances the user reached here by accessing the URL directly.
    // In that case the cookies may not be set yet. So i set them here.
    if (empty($domain) || empty($language_code)) {
      $domain = \Drupal::service('country.current')->getSlug();
      $language_code = \Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
      $this->siteEntryManager->setPreferredCountry($domain);
      $this->siteEntryManager->setPreferredLanguage($language_code);
    }

    // Load the Brand Movie. I assume there is only one.
    /** @var \Drupal\node\Entity\Node $node */
    $nids = $this->entityQuery->get('node')
      ->condition('type', 'movie')
      ->condition('status', 1)
      ->condition('field_domain_access', "country_{$domain}")
      ->condition('langcode', $language_code)
      ->execute();

    if (!empty($nids)) {
      // Load the Brand Movie. We assume there is only one.
      $mobile_movie = $this->entityQuery->get('node')
        ->condition('type', 'brand_movie_mobile')
        ->condition('status', 1)
        ->condition('field_domain_access', "country_{$domain}")
        ->condition('langcode', $language_code)
        ->range(0, 1)
        ->execute();

      $mobile_movie_node = array_values($mobile_movie);

      if (!empty($mobile_movie_node)) {
        /** @var \Drupal\node\Entity\Node $mobile_movie_node */
        $mobile_movie_node = $this->entityTypeManager()
          ->getStorage('node')
          ->load($mobile_movie_node[0]);
        if ($language_code && $mobile_movie_node->hasTranslation($language_code)) {
          $movie_node = $mobile_movie_node->getTranslation($language_code);

          /** @var \Drupal\file\Entity\File $mobile_movie */
          $mobile_movie = $movie_node->field_brand_movie->referencedEntities()[0];

          /** @var \Drupal\file\Entity\File $mobile_movie_cover */
          $mobile_movie_cover = $movie_node->field_brand_movie_cover->referencedEntities()[0];
          $industries['mobile'] = [
            'movie_url' => $mobile_movie->url(),
            'movie_cover' => $mobile_movie_cover->url(),
          ];
        }
      }

      foreach ($nids as $nid) {
        /** @var \Drupal\node\Entity\Node $movie_node */
        $movie_node = $this->entityTypeManager()
          ->getStorage('node')
          ->load($nid);
        if ($language_code && $movie_node->hasTranslation($language_code)) {
          $movie_node = $movie_node->getTranslation($language_code);
        }

        /** @var \Drupal\node\Entity\Node $industry_node */
        $industry_node = $movie_node->field_industry->referencedEntities()[0];
        if ($language_code && $industry_node->hasTranslation($language_code)) {
          $industry_node = $industry_node->getTranslation($language_code);
        }

        /** @var \Drupal\taxonomy\Entity\Term $industry_term */
        $industry_term = $movie_node->field_industry->referencedEntities()[0]->field__term_industry->referencedEntities()[0];

        /** @var \Drupal\file\Entity\File $movie */
        $movie = $movie_node->field_brand_movie->referencedEntities()[0];

        /** @var \Drupal\file\Entity\File $movie_cover */
        $movie_cover = $movie_node->field_brand_movie_cover->referencedEntities()[0];

        $industries[$industry_term->id()] = [
          'label' => $industry_node->label(),
          'url' => $industry_node->toUrl(),
          'movie_url' => $movie->url(),
          'movie_cover' => $movie_cover->url(),
        ];

        // Add cache.
        $cacheableMetadata->addCacheableDependency($movie_node);
        $cacheableMetadata->addCacheableDependency($industry_node);
        $cacheableMetadata->addCacheableDependency($industry_term);
      }
    }

    // Apply cache metadata for the data that was added to the template.
    $cacheableMetadata->applyTo($cache_list);

    $build = [
      '#theme' => 'site_entry_brand_movie',
      '#industries' => $industries,
      '#cache' => $cache_list['#cache'],
    ];

    return $build;
  }

}
