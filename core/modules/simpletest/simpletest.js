(function ($) {

"use strict";

/**
 * Add the cool table collapsing on the testing overview page.
 */
Drupal.behaviors.simpleTestMenuCollapse = {
  attach: function (context, settings) {
    var timeout = null;
    // Adds expand-collapse functionality.
    $('div.simpletest-image').once('simpletest-image', function () {
      var $this = $(this);
      var direction = settings.simpleTest[this.id].imageDirection;
      $this.html(settings.simpleTest.images[direction]);

      // Adds group toggling functionality to arrow images.
      $this.click(function () {
        var trs = $this.closest('tbody').children('.' + settings.simpleTest[this.id].testClass);
        var direction = settings.simpleTest[this.id].imageDirection;
        var row = direction ? trs.length - 1 : 0;

        // If clicked in the middle of expanding a group, stop so we can switch directions.
        if (timeout) {
          clearTimeout(timeout);
        }

        // Function to toggle an individual row according to the current direction.
        // We set a timeout of 20 ms until the next row will be shown/hidden to
        // create a sliding effect.
        function rowToggle() {
          if (direction) {
            if (row >= 0) {
              $(trs[row]).hide();
              row--;
              timeout = setTimeout(rowToggle, 20);
            }
          }
          else {
            if (row < trs.length) {
              $(trs[row]).removeClass('js-hide').show();
              row++;
              timeout = setTimeout(rowToggle, 20);
            }
          }
        }

        // Kick-off the toggling upon a new click.
        rowToggle();

        // Toggle the arrow image next to the test group title.
        $this.html(settings.simpleTest.images[(direction ? 0 : 1)]);
        settings.simpleTest[this.id].imageDirection = !direction;

      });
    });
  }
};

/**
 * Select/deselect all the inner checkboxes when the outer checkboxes are
 * selected/deselected.
 */
Drupal.behaviors.simpleTestSelectAll = {
  attach: function (context, settings) {
    $('td.simpletest-select-all').once('simpletest-select-all', function () {
      var testCheckboxes = settings.simpleTest['simpletest-test-group-' + $(this).attr('id')].testNames;
      var groupCheckbox = $('<input type="checkbox" class="form-checkbox" id="' + $(this).attr('id') + '-select-all" />');

      // Each time a single-test checkbox is checked or unchecked, make sure
      // that the associated group checkbox gets the right state too.
      function updateGroupCheckbox() {
        var checkedTests = 0;
        for (var i = 0; i < testCheckboxes.length; i++) {
          if ($('#' + testCheckboxes[i]).prop('checked')) {
            checkedTests++;
          }
        }
        $(groupCheckbox).prop('checked', (checkedTests === testCheckboxes.length));
      }

      // Have the single-test checkboxes follow the group checkbox.
      groupCheckbox.change(function () {
        var checked = $(this).prop('checked');
        for (var i = 0; i < testCheckboxes.length; i++) {
          $('#' + testCheckboxes[i]).prop('checked', checked);
        }
      });

      // Have the group checkbox follow the single-test checkboxes.
      for (var i = 0; i < testCheckboxes.length; i++) {
        $('#' + testCheckboxes[i]).change(updateGroupCheckbox);
      }

      // Initialize status for the group checkbox correctly.
      updateGroupCheckbox();
      $(this).append(groupCheckbox);
    });
  }
};


/**
 * Filters the module list table by a text input search string.
 *
 * Additionally accounts for multiple tables being wrapped in "package" details
 * elements.
 *
 * Text search input: input.table-filter-text
 * Target table:      input.table-filter-text[data-table]
 * Source text:       .table-filter-text-source
 */
Drupal.behaviors.simpletestTableFilterByText = {
  attach: function (context, settings) {
    var $input = $('input.table-filter-text').once('table-filter-text');
    var $table = $($input.attr('data-table'));
    var $rowsAndDetails, $rows, $details;

    function hidePackageDetails(index, element) {
      var $details = $(element);
      var $visibleRows = $details.find('table:not(.sticky-header)').find('tbody tr:visible');
      $details.toggle($visibleRows.length > 0);
    }

    function filterModuleList (e) {
      var query = $(e.target).val().toLowerCase();

      function showModuleRow (index, row) {
        var $row = $(row);
        var $sources = $row.find('.table-filter-text-source');
        var textMatch = $sources.text().toLowerCase().indexOf(query) !== -1;
        $row.closest('tr').toggle(textMatch);
      }

      // Filter if the length of the query is at least 2 characters.
      if (query.length >= 2) {
        $rows.each(showModuleRow);

        // Hide the package <details> if they don't have any visible rows.
        // Note that we first show() all <details> to be able to use ':visible'.
        $details.show().each(hidePackageDetails);
      }
      else {
        $rowsAndDetails.show();
      }
    }

    if ($table.length) {
      $rowsAndDetails = $table.find('tr, details');
      $rows = $table.find('tbody tr');
      $details = $table.find('details');

      // @todo Use autofocus attribute when possible.
      $input.focus().on('keyup', filterModuleList);
    }
  }
};

})(jQuery);
