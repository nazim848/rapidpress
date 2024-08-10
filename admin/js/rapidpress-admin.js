class RapidPressAdmin {
	constructor($) {
		this.$ = $;
		this.init();
	}

	init() {
		this.setupEventHandlers();
		this.initializeTabs();
		this.restoreAccordionState();
		this.initializeRuleManagement();
	}

	// Helper methods
	updateQueryStringParameter(uri, key, value) {
		let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		let separator = uri.indexOf("?") !== -1 ? "&" : "?";
		return uri.match(re)
			? uri.replace(re, "$1" + key + "=" + value + "$2")
			: uri + separator + key + "=" + value;
	}

	// Accordion methods
	saveAccordionState() {
		let activeAccordions = [];
		this.$(".accordion-item").each((index, item) => {
			if (this.$(item).find(".accordion-header").hasClass("active")) {
				activeAccordions.push(index);
			}
		});
		localStorage.setItem(
			"rapidpressActiveAccordions",
			JSON.stringify(activeAccordions)
		);
	}

	restoreAccordionState() {
		let activeAccordions =
			JSON.parse(localStorage.getItem("rapidpressActiveAccordions")) || [];
		this.$(".accordion-item").each((index, item) => {
			let $header = this.$(item).find(".accordion-header");
			let $content = this.$(item).find(".accordion-content");
			if (activeAccordions.includes(index)) {
				$header.addClass("active");
				$content.show();
			} else {
				$header.removeClass("active");
				$content.hide();
			}
		});
	}

	// Rule management methods
	addRuleRow(buttonId, tableId, ruleName) {
		const $table = this.$(tableId);
		const $tableHead = $table.find("tr:first");

		const updateTableHead = () => {
			$table.find("tr").length > 1 ? $tableHead.show() : $tableHead.hide();
		};

		updateTableHead();

		this.$(buttonId).on("click", () => {
			$tableHead.show();
			var newRow = this.createNewRow(ruleName);
			$table.append(newRow);
			updateTableHead();
		});

		this.$(document).on(
			"click",
			`.remove-${ruleName}-rule`,
			function () {
				this.$(this).closest("tr").remove();
				updateTableHead();
			}.bind(this)
		);
	}

	createNewRow(ruleName) {
		const timestamp = Date.now();
		const commonFields = `
			  <td>
					<select name="rapidpress_${ruleName}_disable_rules[new_${timestamp}][scope]" class="${ruleName}-disable-scope">
						 <option value="entire_site">Entire Site</option>
						 <option value="front_page">Front Page</option>
						 <option value="specific_pages">Specific Pages</option>
					</select>
					<textarea cols="65" rows="3" name="rapidpress_${ruleName}_disable_rules[new_${timestamp}][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="${ruleName}-disable-pages" style="display:none;"></textarea>
			  </td>
			  <td><button type="button" class="button remove-${ruleName}-rule">Remove</button></td>
		 `;

		if (ruleName === "js") {
			return `
					<tr>
						 <td><textarea cols="65" rows="3" name="rapidpress_js_disable_rules[new_${timestamp}][scripts]" placeholder="Script URL or Handle (one per line)"></textarea></td>
						 ${commonFields}
					</tr>`;
		} else if (ruleName === "css") {
			return `
					<tr>
						 <td><textarea cols="65" rows="3" name="rapidpress_css_disable_rules[new_${timestamp}][styles]" placeholder="CSS URL or Handle (one per line)"></textarea></td>
						 ${commonFields}
					</tr>`;
		}
	}

	// Event handlers
	setupEventHandlers() {
		this.setupOptimizationScope();
		this.setupCssJsOptions();
		this.setupAccordion();
		this.setupTabSwitching();
		this.setupFormSubmission();
		this.setupSubmitButtonVisibility();
	}

	setupOptimizationScope() {
		this.$("#rapidpress_optimization_scope")
			.change(() => {
				const val = this.$("#rapidpress_optimization_scope").val();
				this.$("#rapidpress_specific_pages_row").toggle(
					val === "specific_pages"
				);
				this.$("#rapidpress_optimization_excluded_pages_row").toggle(
					val === "entire_site" &&
						this.$("#rapidpress_enable_scope_exclusions").is(":checked")
				);
				this.$("#rapidpress_enable_scope_exclusions_label").toggle(
					val === "entire_site"
				);
			})
			.change();

		this.$("#rapidpress_enable_scope_exclusions").change(() => {
			this.$("#rapidpress_optimization_excluded_pages_row").toggle(
				this.$("#rapidpress_enable_scope_exclusions").is(":checked") &&
					this.$("#rapidpress_optimization_scope").val() === "entire_site"
			);
		});
	}

	setupCssJsOptions() {
		this.setupToggleOption(
			"#rapidpress_combine_css",
			"#rapidpress_enable_combine_css_exclusions",
			"#rapidpress_combine_css_exclusions_row"
		);
		this.setupToggleOption(
			"#rapidpress_js_defer",
			"#rapidpress_enable_js_defer_exclusions",
			"#rapidpress_js_defer_exclusions_row"
		);
		this.setupToggleOption(
			"#rapidpress_js_delay",
			"#rapidpress_enable_js_delay_exclusions",
			"#rapidpress_js_delay_exclusions_row"
		);

		// Additional toggle for JS delay options
		this.$("#rapidpress_js_delay")
			.change(() => {
				this.$("#rapidpress_js_delay_options").toggle(
					this.$("#rapidpress_js_delay").is(":checked")
				);
			})
			.change();

		// Handle JS and CSS disable scope change
		this.$(document).on(
			"change",
			".js-disable-scope, .css-disable-scope",
			function () {
				this.$(this)
					.siblings(".js-disable-pages, .css-disable-pages")
					.toggle(this.$(this).val() === "specific_pages");
			}.bind(this)
		);
	}

	setupToggleOption(mainCheckboxId, exclusionCheckboxId, rowId) {
		const $mainCheckbox = this.$(mainCheckboxId);
		const $exclusionCheckbox = this.$(exclusionCheckboxId);
		const $row = this.$(rowId);

		const updateVisibility = () => {
			const isMainChecked = $mainCheckbox.is(":checked");
			const isExclusionChecked = $exclusionCheckbox.is(":checked");

			$exclusionCheckbox.closest("label").toggle(isMainChecked);
			$row.toggle(isMainChecked && isExclusionChecked);
		};

		$mainCheckbox.change(updateVisibility);
		$exclusionCheckbox.change(updateVisibility);

		// Initial state
		updateVisibility();
	}

	setupAccordion() {
		this.$(document).on("click", ".accordion-header", e => {
			e.preventDefault();
			let $this = this.$(e.currentTarget);
			let $content = $this.next(".accordion-content");
			let isActive = $this.hasClass("active");

			// Close all other accordions
			this.$(".accordion-header").not($this).removeClass("active");
			this.$(".accordion-content").not($content).slideUp(300);

			// Toggle the clicked accordion
			$this.toggleClass("active", !isActive);
			$content.slideToggle(300);

			this.saveAccordionState();
		});
	}

	setupTabSwitching() {
		this.$(".nav-tab-wrapper a").on("click", e => {
			e.preventDefault();
			let target = this.$(e.currentTarget).attr("href").substr(1);
			this.setActiveTab(target);
			history.pushState(
				null,
				null,
				this.updateQueryStringParameter(window.location.href, "tab", target)
			);
		});
	}

	setupFormSubmission() {
		this.$("form").on("submit", e => {
			e.preventDefault();
			let form = this.$(e.currentTarget);
			let activeTab = this.$("#rapidpress_active_tab").val();

			this.$.post(form.attr("action"), form.serialize(), response => {
				let newUrl = this.updateQueryStringParameter(
					window.location.href,
					"tab",
					activeTab
				);
				newUrl = this.updateQueryStringParameter(
					newUrl,
					"settings-updated",
					"true"
				);
				window.location.href = newUrl;
			});
		});

		// Save accordion state before form submission
		this.$("form").on("submit", () => this.saveAccordionState());
	}

	setupSubmitButtonVisibility() {
		this.$(".nav-tab-wrapper .nav-tab").on("click", e => {
			e.preventDefault();
			const tabId = this.$(e.currentTarget).attr("href").substring(1);
			this.$("#submit-button").toggle(tabId !== "dashboard");
		});
	}

	// Initialization methods
	initializeTabs() {
		const urlParams = new URLSearchParams(window.location.search);
		const activeTab = urlParams.get("tab") || "dashboard";
		this.setActiveTab(activeTab);
	}

	initializeRuleManagement() {
		this.addRuleRow("#add-js-rule", "#js-asset-management", "js");
		this.addRuleRow("#add-css-rule", "#css-asset-management", "css");
	}

	setActiveTab(tab) {
		this.$(".nav-tab-wrapper a").removeClass("nav-tab-active");
		this.$(`.nav-tab-wrapper a[href="#${tab}"]`).addClass("nav-tab-active");
		this.$(".tab-content > div").hide();
		this.$(`#${tab}`).show();
		this.$("#rapidpress_active_tab").val(tab);
	}
}

// Initialize the admin functionality
jQuery(function ($) {
	const rapidPressAdmin = new RapidPressAdmin($);

	// Make setActiveTab accessible globally
	window.setActiveTab = function (tab) {
		rapidPressAdmin.setActiveTab(tab);
	};

	// Initialize tabs
	const urlParams = new URLSearchParams(window.location.search);
	const activeTab = urlParams.get("tab") || "dashboard";
	setActiveTab(activeTab);
});
