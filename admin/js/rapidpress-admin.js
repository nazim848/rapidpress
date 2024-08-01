jQuery(document).ready(function ($) {
	// Function to set active tab
	function setActiveTab(tab) {
		$(".nav-tab-wrapper a").removeClass("nav-tab-active");
		$('.nav-tab-wrapper a[href="' + tab + '"]').addClass("nav-tab-active");
		$(".tab-content > div").removeClass("active");
		$(tab).addClass("active");
		$("#rapidpress_active_tab").val(tab);
	}

	// Tab switching
	$(".nav-tab-wrapper a").on("click", function (e) {
		e.preventDefault();
		var target = $(this).attr("href");
		setActiveTab(target);
		// Update URL without page reload
		history.pushState(null, null, "?page=rapidpress&tab=" + target.substr(1));
	});

	// Set active tab based on URL parameter or default to dashboard
	var urlParams = new URLSearchParams(window.location.search);
	var activeTab = urlParams.get("tab")
		? "#" + urlParams.get("tab")
		: "#dashboard";
	setActiveTab(activeTab);

	// Update hidden input when form is submitted
	$("form").on("submit", function () {
		$("#rapidpress_active_tab").val(
			$(".nav-tab-wrapper a.nav-tab-active").attr("href")
		);
	});
});
