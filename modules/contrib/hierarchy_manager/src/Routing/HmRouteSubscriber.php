<?php

namespace Drupal\hierarchy_manager\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 *
 * Class HmRouteSubscriber.
 *
 * @package Drupal\hierarchy_manager\Routing
 */
class HmRouteSubscriber extends RouteSubscriberBase {

  /**
   * Overrides entity.taxonomy_vocabulary.overview_form route.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   Route Collection.
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change path of taxonomy overview to our overridden form.
    if ($route = $collection->get('entity.taxonomy_vocabulary.overview_form')) {
      $route->setDefault('_form', '\Drupal\hierarchy_manager\Form\HmOverviewTerms');
    }
  }

}
