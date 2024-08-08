// Define setActiveTab as a global function
window.setActiveTab = function (tab) {
	jQuery(".nav-tab-wrapper a").removeClass("nav-tab-active");
	jQuery('.nav-tab-wrapper a[href="#' + tab + '"]').addClass("nav-tab-active");
	jQuery(".tab-content > div").hide();
	jQuery("#" + tab).show();
	jQuery("#rapidpress_active_tab").val(tab);
};

jQuery(document).ready(function ($) {
	// Tab switching
	$(".nav-tab-wrapper a").on("click", function (e) {
		e.preventDefault();
		let target = $(this).attr("href").substr(1);
		setActiveTab(target);
		// Update URL without page reload
		history.pushState(
			null,
			null,
			updateQueryStringParameter(window.location.href, "tab", target)
		);
	});

	// Set active tab based on URL parameter or default to dashboard
	let urlParams = new URLSearchParams(window.location.search);
	let activeTab = urlParams.get("tab") || "dashboard";
	setActiveTab(activeTab);

	// Handle form submission
	$("form").on("submit", function (e) {
		e.preventDefault();
		let form = $(this);
		let activeTab = $("#rapidpress_active_tab").val();

		$.post(form.attr("action"), form.serialize(), function (response) {
			// Update the URL with the active tab and settings-updated parameter
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

			// Reload the page with the new URL
			window.location.href = newUrl;
		});
	});

	// Helper function to update URL parameter
	function updateQueryStringParameter(uri, key, value) {
		let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		let separator = uri.indexOf("?") !== -1 ? "&" : "?";
		if (uri.match(re)) {
			return uri.replace(re, "$1" + key + "=" + value + "$2");
		} else {
			return uri + separator + key + "=" + value;
		}
	}

	// New function for CSS exclusions toggle
	function toggleCssExclusions() {
		if ($("#rapidpress_combine_css").is(":checked")) {
			$("#rapidpress_css_exclusions_row").show();
		} else {
			$("#rapidpress_css_exclusions_row").hide();
		}
	}

	// Initial state
	toggleCssExclusions();

	// On change
	$("#rapidpress_combine_css").change(toggleCssExclusions);

	// Add this to the existing jQuery document ready function
	function toggleJsDeferExclusions() {
		if ($("#rapidpress_js_defer").is(":checked")) {
			$("#rapidpress_js_defer_exclusions_row").show();
		} else {
			$("#rapidpress_js_defer_exclusions_row").hide();
		}
	}

	// Initial state
	toggleJsDeferExclusions();

	// On change
	$("#rapidpress_js_defer").change(toggleJsDeferExclusions);

	function toggleJsDelayOptions() {
		if ($("#rapidpress_js_delay").is(":checked")) {
			$("#rapidpress_js_delay_options").show();
			$("#rapidpress_js_delay_exclusions_row").show();
		} else {
			$("#rapidpress_js_delay_options").hide();
			$("#rapidpress_js_delay_exclusions_row").hide();
		}
	}

	// Initial state
	toggleJsDelayOptions();

	// On change
	$("#rapidpress_js_delay").change(toggleJsDelayOptions);

	// Accordion
	let accordionHeaders = document.querySelectorAll(".accordion-header");

	accordionHeaders.forEach(function (header) {
		header.addEventListener("click", function (e) {
			e.preventDefault();
			// Check if the clicked header is already active
			let isActive = this.classList.contains("active");

			// Close all accordions
			accordionHeaders.forEach(function (h) {
				h.classList.remove("active");
				h.nextElementSibling.classList.remove("active");
			});

			// If the clicked header wasn't active, open it
			if (!isActive) {
				this.classList.add("active");
				this.nextElementSibling.classList.add("active");
			}
		});
	});

	$("#add-js-rule").on("click", function () {
		var newRow =
			"<tr>" +
			'<td><input style="width:60%" type="text" name="rapidpress_js_disable_rules[new_' +
			Date.now() +
			'][url]" value="" placeholder="Script URL" /> ' +
			'<span> or </span><input type="text" name="rapidpress_js_disable_rules[new_' +
			Date.now() +
			'][handle]" value="" placeholder="Handle (optional)" />   </td>' +
			'<td><textarea cols="60" rows="3" name="rapidpress_js_disable_rules[new_' +
			Date.now() +
			'][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/"></textarea></td>' +
			'<td><button type="button" class="button remove-js-rule">Remove</button></td>' +
			"</tr>";
		$("#js-asset-management").append(newRow);
	});

	// Handle removing JS rule
	$(document).on("click", ".remove-js-rule", function () {
		$(this).closest("tr").remove();
	});
});
