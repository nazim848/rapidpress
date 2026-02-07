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
		this.setupResetSettings();
		this.setupPurgePageCache();
		this.setupPreloadPageCache();
		this.setupClearCssCache();
	}

	setupResetSettings() {
		this.$("#rapidpress-reset-settings").on("click", e => {
			e.preventDefault();
			if (
				confirm(
					"Are you sure you want to reset all RapidPress settings? This action cannot be undone."
				)
			) {
				this.$.ajax({
					url: rapidpress_admin.ajax_url,
					type: "POST",
					data: {
						action: "rapidpress_reset_settings",
						nonce: rapidpress_admin.nonce
					},
					success: response => {
						if (response.success) {
							alert(
								"Settings reset successfully. The page will now reload."
							);
							location.reload();
						} else {
							alert("Failed to reset settings. Please try again.");
						}
					},
					error: () => {
						alert("An error occurred. Please try again.");
					}
				});
			}
		});
	}

	setupPurgePageCache() {
		this.$("#rapidpress-purge-page-cache").on("click", e => {
			e.preventDefault();
			if (!confirm("Purge all cached HTML pages now?")) {
				return;
			}

			this.$.ajax({
				url: rapidpress_admin.ajax_url,
				type: "POST",
				data: {
					action: "rapidpress_purge_page_cache",
					nonce: rapidpress_admin.nonce
				},
				success: response => {
					if (response.success) {
						alert("Page cache purged successfully.");
					} else {
						alert("Failed to purge page cache. Please try again.");
					}
				},
				error: () => {
					alert("An error occurred while purging page cache.");
				}
			});
		});
	}

	setupPreloadPageCache() {
		this.$("#rapidpress-preload-page-cache").on("click", e => {
			e.preventDefault();

			this.$.ajax({
				url: rapidpress_admin.ajax_url,
				type: "POST",
				data: {
					action: "rapidpress_preload_page_cache",
					nonce: rapidpress_admin.nonce
				},
					success: response => {
						if (response.success) {
							const data = response.data || {};
							alert(data.message || "Cache preload completed.");
							const fallbackDisplay = new Date().toLocaleString();
							const display =
								data.last_run_display && data.last_run_display !== ""
									? data.last_run_display
									: fallbackDisplay;
							this.$("#rapidpress-preload-status").text(
								`Last preload: ${display} (${data.last_count || 0} URLs)`
							);
						} else {
							alert("Failed to preload cache. Please try again.");
						}
				},
				error: () => {
					alert("An error occurred while preloading cache.");
				}
			});
		});
	}

	setupClearCssCache() {
		this.$("#rapidpress-clear-css-cache").on("click", e => {
			e.preventDefault();
			this.$.ajax({
				url: rapidpress_admin.ajax_url,
				type: "POST",
				data: {
					action: "rapidpress_clear_css_cache",
					nonce: rapidpress_admin.nonce
				},
				success: response => {
					if (response.success) {
						alert("CSS cache cleared successfully.");
					} else {
						alert("Failed to clear CSS cache. Please try again.");
					}
				},
				error: () => {
					alert("An error occurred while clearing CSS cache.");
				}
			});
		});
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

		// Update this event handler
		this.$(document).on("click", `.remove-${ruleName}-rule`, event => {
			this.$(event.target).closest("tr").remove();
			updateTableHead();
		});
	}

	createNewRow(ruleName) {
		const timestamp = Date.now();
		const commonFields = `
			 <td>
			 <select name="rapidpress_options[${ruleName}_disable_rules][new_${timestamp}][scope]" class="${ruleName}-disable-scope">
						<option value="entire_site">Entire Site</option>
						<option value="front_page">Front Page</option>
						<option value="specific_pages">Specific Pages</option>
				  </select><div class="checkbox-radio"><label class="${ruleName}-exclude-pages-wrapper" style="display:inline-block;"><input type="checkbox" name="rapidpress_options[${ruleName}_disable_rules][new_${timestamp}][exclude_enabled]" class="${ruleName}-exclude-enabled" value="1"> Exclude pages?</label></div>
				 <textarea cols="63" rows="3" name="rapidpress_options[${ruleName}_disable_rules][new_${timestamp}][exclude_pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="${ruleName}-exclude-pages" style="display:none;"></textarea>
				 <textarea cols="63" rows="3" name="rapidpress_options[${ruleName}_disable_rules][new_${timestamp}][pages]" placeholder="https://example.com/page1/&#10;https://example.com/page2/" class="${ruleName}-disable-pages" style="display:none;"></textarea>
     </td>
     <td>
	  <div class="checkbox-btn status"><label><input type="checkbox" name="rapidpress_options[${ruleName}_disable_rules][new_${timestamp}][is_active]" value="1" checked><span>Active</span></label></div>
         <button type="button" class="button remove-${ruleName}-rule">Remove</button>
     </td>
		`;
		if (ruleName === "js") {
			return `
				  <tr>
						<td><textarea cols="63" rows="3" name="rapidpress_options[js_disable_rules][new_${timestamp}][scripts]" placeholder="Script URL or Handle (one per line)"></textarea></td>
						${commonFields}
				  </tr>`;
		} else if (ruleName === "css") {
			return `
				  <tr>
						<td><textarea cols="63" rows="3" name="rapidpress_options[css_disable_rules][new_${timestamp}][styles]" placeholder="CSS URL or Handle (one per line)"></textarea></td>
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

		// Handle JS and CSS disable scope change
		this.$(document).on(
			"change",
			".js-disable-scope, .css-disable-scope",
			event => {
				const $select = this.$(event.target);
				const $row = $select.closest("tr");
				const $excludeWrapper = $row.find(
					".js-exclude-pages-wrapper, .css-exclude-pages-wrapper"
				);
				const $excludePages = $row.find(
					".js-exclude-pages, .css-exclude-pages"
				);
				const $disablePages = $row.find(
					".js-disable-pages, .css-disable-pages"
				);

				if ($select.val() === "entire_site") {
					$excludeWrapper.show();
					$disablePages.hide();
				} else {
					$excludeWrapper.hide();
					$excludePages.hide();
					if ($select.val() === "specific_pages") {
						$disablePages.show();
					} else {
						$disablePages.hide();
					}
				}
			}
		);

		// Handle exclude pages checkbox change
		this.$(document).on(
			"change",
			".js-exclude-enabled, .css-exclude-enabled",
			event => {
				const $checkbox = this.$(event.target);
				const $row = $checkbox.closest("tr");
				const $excludePages = $row.find(
					".js-exclude-pages, .css-exclude-pages"
				);
				$excludePages.toggle($checkbox.is(":checked"));
			}
		);
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

		// Handle JS delay options
		const $jsDelay = this.$("#rapidpress_js_delay");
		const $jsDelayOptions = this.$("#rapidpress_js_delay_options");
		const $jsDelayType = this.$("#rapidpress_js_delay_type");
		const $jsDelaySpecific = this.$("#rapidpress_js_delay_specific");
		const $jsDelayAll = this.$("#js_delay_exclusions_wrapper");
		const $jsDelayDuration = this.$("#rapidpress_js_delay_duration").parent(); // Select the parent container
		const $jsDelayExclusionsRow = this.$(
			"#rapidpress_js_delay_exclusions_row"
		);
		const $enableJsDelayExclusions = this.$(
			"#rapidpress_enable_js_delay_exclusions"
		);

		const updateJsDelayVisibility = () => {
			const isJsDelayChecked = $jsDelay.is(":checked");
			const isSpecific = $jsDelayType.val() === "specific";
			const isExclusionsEnabled = $enableJsDelayExclusions.is(":checked");

			$jsDelayOptions.toggle(isJsDelayChecked);
			$jsDelaySpecific.toggle(isJsDelayChecked && isSpecific);
			$jsDelayAll.toggle(isJsDelayChecked && !isSpecific);
			$jsDelayDuration.toggle(isJsDelayChecked); // Always show duration when JS delay is checked
			$jsDelayExclusionsRow.toggle(
				isJsDelayChecked && !isSpecific && isExclusionsEnabled
			);
			$enableJsDelayExclusions
				.closest(".checkbox-btn")
				.toggle(isJsDelayChecked && !isSpecific);
		};

		$jsDelay.change(updateJsDelayVisibility);
		$jsDelayType.change(updateJsDelayVisibility);
		$enableJsDelayExclusions.change(updateJsDelayVisibility);

		// Initial state
		updateJsDelayVisibility();

		// Handle JS and CSS disable scope change
		this.$(document).on(
			"change",
			".js-disable-scope, .css-disable-scope",
			event => {
				const $select = this.$(event.target);
				const $pagesTextarea = $select.siblings(
					".js-disable-pages, .css-disable-pages"
				);
				$pagesTextarea.toggle($select.val() === "specific_pages");
			}
		);
	}

	setupToggleOption(mainCheckboxId, exclusionCheckboxId, rowId) {
		const $mainCheckbox = this.$(mainCheckboxId);
		const $exclusionCheckbox = this.$(exclusionCheckboxId);
		const $row = this.$(rowId);

		const updateVisibility = () => {
			const isMainChecked = $mainCheckbox.is(":checked");
			const isExclusionChecked = $exclusionCheckbox.is(":checked");

			$exclusionCheckbox.closest(".checkbox-btn").toggle(isMainChecked);
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
			this.$(".accordion-content").not($content).slideUp(250);

			// Toggle the clicked accordion
			$this.toggleClass("active", !isActive);
			$content.slideToggle(250);

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

			// Serialize the form data
			let formData = form.serializeArray();

			// Add unchecked checkboxes to the formData
			form.find("input[type=checkbox]:not(:checked)").each(function () {
				formData.push({ name: this.name, value: "0" });
			});

			// Convert formData to a string
			formData = jQuery.param(formData);

			formData += "&action=rapidpress_save_settings";
			formData += "&rapidpress_nonce=" + rapidpress_admin.nonce;

			// Show loading indicator
			this.showLoadingIndicator();

			jQuery.ajax({
				url: rapidpress_admin.ajax_url,
				type: "POST",
				data: formData,
				success: response => {
					this.hideLoadingIndicator();
					if (response.success) {
						this.showNotice(response.data, "success");
					} else {
						let errorMessage =
							response.data ||
							"Failed to save settings. Please try again.";
						this.showNotice(errorMessage, "error");
					}
				},
				error: (jqXHR, textStatus, errorThrown) => {
					this.hideLoadingIndicator();
					this.showNotice("An error occurred. Please try again.", "error");
				}
			});
		});
	}

	showNotice(message, type) {
		// Remove any existing notices
		this.$(".rapidpress-inline-notice").remove();

		let noticeClass = type === "success" ? "notice-success" : "notice-error";
		let notice = this.$(
			`<span class="rapidpress-inline-notice ${noticeClass}">${message}</span>`
		);

		// Insert the notice after the submit button
		this.$("#submit").after(notice);

		// Automatically remove the notice after 3 seconds
		setTimeout(() => {
			notice.fadeOut(300, function () {
				jQuery(this).remove();
			});
		}, 3000);
	}

	showLoadingIndicator() {
		// Add a loading indicator to the submit button
		const submitButton = this.$("#submit");
		submitButton.val("Saving...");
	}

	hideLoadingIndicator() {
		// Remove the loading indicator from the submit button
		const submitButton = this.$("#submit");
		submitButton.val("Save Changes");
	}

	setupSubmitButtonVisibility() {
		// this.$(".nav-tab-wrapper .nav-tab").on("click", e => {
		// 	e.preventDefault();
		// 	const tabId = this.$(e.currentTarget).attr("href").substring(1);
		// 	this.$("#submit-button").toggle(tabId !== "general");
		// });
	}

	// Initialization methods
	initializeTabs() {
		const urlParams = new URLSearchParams(window.location.search);
		const activeTab = urlParams.get("tab") || "general";
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
	const activeTab = urlParams.get("tab") || "general";
	setActiveTab(activeTab);

	// Make submit button visible after all the tabs are loaded
	setTimeout(function () {
		let submitButton = document.getElementById("submit-button");
		if (submitButton) {
			submitButton.style.display = "block";
		}
	}, 50); // Delay of 50 milliseconds

	// Hide save changes notification after few seconds
	setTimeout(function () {
		let notice = $(".notice-success");
		if (notice) {
			notice.slideToggle();
		}
	}, 1500); // Delay of 1.5 seconds
});
