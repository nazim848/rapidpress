(function ($) {
	"use strict";

	$(document).ready(function () {
		$("#rapidpress_optimization_scope")
			.change(function () {
				if ($(this).val() === "specific_pages") {
					$("#rapidpress_specific_pages_row").show();
					$("#rapidpress_excluded_pages_row").hide();
					$("#rapidpress_enable_scope_exclusions_label").hide();
				} else if ($(this).val() === "entire_site") {
					$("#rapidpress_specific_pages_row").hide();
					$("#rapidpress_enable_scope_exclusions_label").show();
					if ($("#rapidpress_enable_scope_exclusions").is(":checked")) {
						$("#rapidpress_excluded_pages_row").show();
					}
				} else {
					$("#rapidpress_specific_pages_row").hide();
					$("#rapidpress_enable_scope_exclusions_label").hide();
					$("#rapidpress_excluded_pages_row").hide();
				}
			})
			.change(); // Trigger change event on page load
		$("#rapidpress_enable_scope_exclusions").change(function () {
			if ($(this).is(":checked")) {
				$("#rapidpress_excluded_pages_row").show();
			} else {
				$("#rapidpress_excluded_pages_row").hide();
			}
		});
		// $("#rapidpress_enable_exclusions").change();

		// Handle combine css click event
		$("#rapidpress_combine_css")
			.change(function () {
				if ($(this).is(":checked")) {
					$("#rapidpress_enable_css_combine_exclusions_btn").show();
					if (
						$("#rapidpress_enable_css_combine_exclusions").is(":checked")
					) {
						$("#rapidpress_css_exclusions_row").show();
					}
				} else {
					$("#rapidpress_enable_css_combine_exclusions_btn").hide();
					$("#rapidpress_css_exclusions_row").hide();
				}
			})
			.change();

		$("#rapidpress_enable_css_combine_exclusions").change(function () {
			if ($(this).is(":checked")) {
				$("#rapidpress_css_exclusions_row").show();
			} else {
				$("#rapidpress_css_exclusions_row").hide();
			}
		});

		// Handle JS disable scope change
		$(document).on("change", ".js-disable-scope", function () {
			var $pagesTextarea = $(this).siblings(".js-disable-pages");
			if ($(this).val() === "specific_pages") {
				$pagesTextarea.show();
			} else {
				$pagesTextarea.hide();
			}
		});

		// Handle CSS disable scope change
		$(document).on("change", ".css-disable-scope", function () {
			var $pagesTextarea = $(this).siblings(".css-disable-pages");
			if ($(this).val() === "specific_pages") {
				$pagesTextarea.show();
			} else {
				$pagesTextarea.hide();
			}
		});
	});

	// Helper function to update URL parameter
	function updateQueryStringParameter(uri, key, value) {
		let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		let separator = uri.indexOf("?") !== -1 ? "&" : "?";
		return uri.match(re)
			? uri.replace(re, "$1" + key + "=" + value + "$2")
			: uri + separator + key + "=" + value;
	}

	// Function to save accordion state
	function saveAccordionState() {
		let activeAccordions = [];
		$(".accordion-item").each(function (index) {
			if ($(this).find(".accordion-header").hasClass("active")) {
				activeAccordions.push(index);
			}
		});
		localStorage.setItem(
			"rapidpressActiveAccordions",
			JSON.stringify(activeAccordions)
		);
	}

	// Function to restore accordion state
	function restoreAccordionState() {
		let activeAccordions =
			JSON.parse(localStorage.getItem("rapidpressActiveAccordions")) || [];
		$(".accordion-item").each(function (index) {
			let $header = $(this).find(".accordion-header");
			let $content = $(this).find(".accordion-content");
			if (activeAccordions.includes(index)) {
				$header.addClass("active");
				$content.show();
			} else {
				$header.removeClass("active");
				$content.hide();
			}
		});
	}

	// Toggle visibility based on checkbox
	function toggleVisibility(checkboxId, targetId) {
		const $checkbox = $(checkboxId);
		const $target = $(targetId);

		function toggle() {
			$target.toggle($checkbox.is(":checked"));
		}

		$checkbox.change(toggle);
		toggle(); // Initial state
	}

	// Modify the addRuleRow function
	function addRuleRow(buttonId, tableId, ruleName) {
		const $table = $(tableId);
		const $tableHead = $table.find("tr:first");

		// Show table head if there are existing rows
		if ($table.find("tr").length > 1) {
			$tableHead.show();
		} else {
			$tableHead.hide();
		}

		$(buttonId).on("click", function () {
			$tableHead.show(); // Show table head when adding a new rule
			var newRow = "";
			if (ruleName === "js") {
				newRow = `
						<tr>
							 <td><textarea cols="65" rows="3" name="rapidpress_js_disable_rules[new_${Date.now()}][scripts]" placeholder="Script URL or Handle (one per line)"></textarea></td>
							 <td>
								  <select name="rapidpress_js_disable_rules[new_${Date.now()}][scope]" class="js-disable-scope">
										<option value="entire_site">Entire Site</option>
										<option value="front_page">Front Page</option>
										<option value="specific_pages">Specific Pages</option>
								  </select>
								  <textarea cols="65" rows="3" name="rapidpress_js_disable_rules[new_${Date.now()}][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="js-disable-pages" style="display:none;"></textarea>
							 </td>
							 <td><button type="button" class="button remove-js-rule">Remove</button></td>
						</tr>`;
			} else if (ruleName === "css") {
				newRow = `
						<tr>
							 <td><textarea cols="65" rows="3" name="rapidpress_css_disable_rules[new_${Date.now()}][styles]" placeholder="CSS URL or Handle (one per line)"></textarea></td>
							 <td>
								  <select name="rapidpress_css_disable_rules[new_${Date.now()}][scope]" class="css-disable-scope">
										<option value="entire_site">Entire Site</option>
										<option value="front_page">Front Page</option>
										<option value="specific_pages">Specific Pages</option>
								  </select>
								  <textarea cols="65" rows="3" name="rapidpress_css_disable_rules[new_${Date.now()}][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="css-disable-pages" style="display:none;"></textarea>
							 </td>
							 <td><button type="button" class="button remove-css-rule">Remove</button></td>
						</tr>`;
			}
			$table.append(newRow);
		});

		$(document).on("click", `.remove-${ruleName}-rule`, function () {
			$(this).closest("tr").remove();
			if ($table.find("tr").length === 1) {
				$tableHead.hide(); // Hide table head if no rules remain
			}
		});
	}

	// Main function to initialize everything
	function init() {
		// Tab switching
		$(".nav-tab-wrapper a").on("click", function (e) {
			e.preventDefault();
			let target = $(this).attr("href").substr(1);
			window.setActiveTab(target);
			history.pushState(
				null,
				null,
				updateQueryStringParameter(window.location.href, "tab", target)
			);
		});

		// Set active tab based on URL parameter or default to dashboard
		let urlParams = new URLSearchParams(window.location.search);
		let activeTab = urlParams.get("tab") || "dashboard";
		window.setActiveTab(activeTab);

		// Handle form submission
		$("form").on("submit", function (e) {
			e.preventDefault();
			let form = $(this);
			let activeTab = $("#rapidpress_active_tab").val();

			$.post(form.attr("action"), form.serialize(), function (response) {
				let newUrl = updateQueryStringParameter(
					window.location.href,
					"tab",
					activeTab
				);
				newUrl = updateQueryStringParameter(
					newUrl,
					"settings-updated",
					"true"
				);
				window.location.href = newUrl;
			});
		});

		// Toggle visibility for various options
		// toggleVisibility(
		// 	"#rapidpress_combine_css",
		// 	"#rapidpress_css_exclusions_row"
		// );
		toggleVisibility(
			"#rapidpress_js_defer",
			"#rapidpress_js_defer_exclusions_row"
		);
		toggleVisibility(
			"#rapidpress_js_delay",
			"#rapidpress_js_delay_options, #rapidpress_js_delay_exclusions_row"
		);

		// Accordion functionality
		$(document).on("click", ".accordion-header", function (e) {
			e.preventDefault();
			let $this = $(this);
			let isActive = $this.hasClass("active");

			$(".accordion-header").removeClass("active");
			$(".accordion-content").slideUp(300);

			if (!isActive) {
				$this.addClass("active");
				$this.next(".accordion-content").slideDown(300);
			}

			saveAccordionState();
		});

		restoreAccordionState();

		// Add rule rows
		addRuleRow("#add-js-rule", "#js-asset-management", "js");
		addRuleRow("#add-css-rule", "#css-asset-management", "css");

		// Hide submit button based on current tab
		$(".nav-tab-wrapper .nav-tab").on("click", function (e) {
			e.preventDefault();
			const tabId = $(this).attr("href").substring(1);
			$("#submit-button").toggle(tabId !== "dashboard");
		});

		// Save accordion state before form submission
		$("form").on("submit", saveAccordionState);
	}

	// Run the init function when the DOM is fully loaded
	$(function () {
		init();
	});
})(jQuery);

// Define setActiveTab as a global function
window.setActiveTab = function (tab) {
	jQuery(".nav-tab-wrapper a").removeClass("nav-tab-active");
	jQuery('.nav-tab-wrapper a[href="#' + tab + '"]').addClass("nav-tab-active");
	jQuery(".tab-content > div").hide();
	jQuery("#" + tab).show();
	jQuery("#rapidpress_active_tab").val(tab);
};
