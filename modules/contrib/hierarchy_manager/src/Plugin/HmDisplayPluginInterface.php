<?php

namespace Drupal\hierarchy_manager\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for Hierarchy manager display plugin plugins.
 */
interface HmDisplayPluginInterface extends PluginInspectionInterface {

  /**
   * Build the tree form.
   */
  public function getForm(string $url_source, string $url_update, array &$form = [], FormStateInterface &$form_state = NULL, $options = NULL);

  /**
   * Build the data array that JS library accepts.
   */
  public function treeData(array $data);

}
