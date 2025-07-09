/**
 * @file
 * Uses Jquery UI Sortable to sort entities in views.
 */

// eslint-disable-next-line func-names
(function ($, sortable) {
  Drupal.sortableviews = Drupal.sortableviews || {};

  /**
   * Processes a single sortableview.
   *
   * Applies jquery.ui.sortable to the view contents and creates
   * a save button.
   *
   * @param {jQuery} $view - The jQuery object representing the view.
   * @param {object} viewSettings - The settings for the sortable view.
   */
  // eslint-disable-next-line func-names
  Drupal.sortableviews.processView = function ($view, viewSettings) {
    const $viewContent = $('.view-content', $view);
    const $sortableItemsContainer =
      viewSettings.selector === 'self'
        ? $viewContent
        : $(viewSettings.selector, $viewContent);
    if (!$sortableItemsContainer.length) {
      return;
    }
    sortable.create($sortableItemsContainer[0], {
      handle: '.sortableviews-handle',
      animation: 120,
      onSort() {
        // Remove existing drupal messages within the view.
        $('.status-messages', $view).remove();

        // Create an array with the current order.
        viewSettings.current_order = [];
        // eslint-disable-next-line func-names
        $('.sortableviews-handle', $(this.el)).each(function (index, element) {
          viewSettings.current_order.push($(element).attr('data-id'));
        });

        // Create a clone of the settings object.
        const viewDataClone = $.extend({}, viewSettings);

        // Reverse the order if the sort order is descendant.
        if (viewDataClone.sort_order === 'desc') {
          viewDataClone.current_order = viewDataClone.current_order.reverse();
        }

        // Add the "Save changes" button.
        $('.sortableviews-save-changes', $view)
          .html('')
          .append(
            $('<a>')
              .attr({
                class: 'sortableviews-ajax-trigger button button--primary',
                id: Math.random().toString(36).slice(2),
                href: '#',
              })
              .html(Drupal.t('Save changes'))
              .addDrupalAjax(viewDataClone.ajax_url, viewDataClone),
          );
      },
    });
  };

  /**
   * A jQuery plugin that attaches ajax to an anchor.
   *
   * @param {string} ajaxUrl - The URL for the AJAX request.
   * @param {Object} dataToSubmit - The data to be submitted with the AJAX request.
   * @return {jQuery} - The jQuery object for chaining.
   */
  // eslint-disable-next-line func-names
  $.fn.addDrupalAjax = function (ajaxUrl, dataToSubmit) {
    const ajaxSettings = {
      url: ajaxUrl,
      event: 'click',
      progress: {
        type: 'throbber',
      },
      setClick: true,
      submit: dataToSubmit,
      element: this[0],
    };
    Drupal.ajax[this.attr('id')] = new Drupal.ajax(ajaxSettings);
    return this;
  };

  /**
   * Attaches the table drag behavior to tables.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Processes all Sortable views in the page.
   */
  Drupal.behaviors.sortable = {
    attach(context, settings) {
      // eslint-disable-next-line func-names
      $.each(settings.sortableviews, function (viewDomId, viewSettings) {
        Drupal.sortableviews.processView(
          $(`.js-view-dom-id-${viewDomId}`),
          viewSettings,
        );
      });
    },
  };
})(jQuery, Sortable);
