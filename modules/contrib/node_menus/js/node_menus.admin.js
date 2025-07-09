
(function ($, Drupal) {
  Drupal.behaviors.nodeMenusChangeParentItems = {
    attach: function attach(context, settings) {

      $('.js-node-menus').each(function () {
        var $menu = $(this).once('menu-parent');
        if ($menu.length) {
          Drupal.nodeMenusUpdateParentList($menu);

          $menu.on('change', 'input', function () {
            Drupal.nodeMenusUpdateParentList($menu);
          });
        }
      });

    }
  };

  Drupal.nodeMenusUpdateParentList = function ($menu) {
    var values = [];
    var langcode = $menu.data('langcode');

    $menu.find('input:checked').each(function () {
      values.push(Drupal.checkPlain($.trim($(this).val())));
    });

    $.ajax({
      url: window.location.protocol + '//' + window.location.host + Drupal.url('admin/structure/menu/parents'),
      type: 'POST',
      data: { 'menus[]': values },
      dataType: 'json',
      success: function success(options) {
        var $select = $('#edit-node-menus-languages-' + langcode + '-menu-parent');

        var selected = $select.val();

        $select.children().remove();

        var totalOptions = 0;
        Object.keys(options || {}).forEach(function (machineName) {
          $select.append($('<option ' + (machineName === selected ? ' selected="selected"' : '') + '></option>').val(machineName).text(options[machineName]));
          totalOptions++;
        });

        $select.closest('div').toggle(totalOptions > 0).attr('hidden', totalOptions === 0);
      }
    });

  };
})(jQuery, Drupal);
