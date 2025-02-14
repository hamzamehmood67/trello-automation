(function ($) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	console.log('Trello Automation Admin JS loaded!');
	jQuery(document).ready(function ($) {
		$('#add_mapping').on('click', function () {
			console.log('clicked');
			$('#trello_product_list_mapping').append(`
				<div class="mapping-row">
					<input type="text" name="trello_automation_trello_options[trello_product_list_mapping][product][]" placeholder="Product Name">
					<input type="text" name="trello_automation_trello_options[trello_product_list_mapping][list_id][]" placeholder="Trello List ID">
				</div>
			`);
		});
	});

})(jQuery);
