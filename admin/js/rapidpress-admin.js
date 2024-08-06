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
		var target = $(this).attr("href").substr(1);
		setActiveTab(target);
		// Update URL without page reload
		history.pushState(
			null,
			null,
			updateQueryStringParameter(window.location.href, "tab", target)
		);
	});

	// Set active tab based on URL parameter or default to dashboard
	var urlParams = new URLSearchParams(window.location.search);
	var activeTab = urlParams.get("tab") || "dashboard";
	setActiveTab(activeTab);

	// Handle form submission
	$("form").on("submit", function (e) {
		e.preventDefault();
		var form = $(this);
		var activeTab = $("#rapidpress_active_tab").val();

		$.post(form.attr("action"), form.serialize(), function (response) {
			// Update the URL with the active tab and settings-updated parameter
			var newUrl = updateQueryStringParameter(
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
		var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		var separator = uri.indexOf("?") !== -1 ? "&" : "?";
		if (uri.match(re)) {
			return uri.replace(re, "$1" + key + "=" + value + "$2");
		} else {
			return uri + separator + key + "=" + value;
		}
	}
});
