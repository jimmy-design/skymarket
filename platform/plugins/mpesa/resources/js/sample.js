/**
 * Mpesa Region Settings Manager
 * Handles loading, creating, updating, and deleting Mpesa settings for different regions
 */
(function ($) {
    'use strict';

    // Store all settings and regions
    const state = {
        regions: [],
        regionSettings: [],
        isLoading: false
    };

    /**
     * Initialize the region settings manager
     */
    function init() {
        console.log("Hello Joash")
        console.log("Initializing M-Pesa Region Settings");

        // Bind event listeners
        bindEvents();

        // Load data
        loadRegions().then(() => {
            loadRegionSettings();
        });
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        // Add region setting
        $(document).on('click', '#add-region-setting', function () {
            const regionId = $('#region-selector').val();
            if (regionId) {
                addRegionSetting(regionId);
            } else {
                Botble.showError('Please select a region first.');
            }
        });

        // Delete region setting
        $(document).on('click', '.delete-region-setting', function () {
            const settingId = $(this).data('id');
            deleteRegionSetting(settingId);
        });

        // Save region setting
        $(document).on('click', '.save-region-setting', function () {
            const settingId = $(this).data('id');
            saveRegionSetting(settingId);
        });
    }

    /**
     * Load all available regions (states)
     */
    function loadRegions() {
        state.isLoading = true;
        updateUI();

        // Get the route from window.mpesaRegionRoutes
        const url = window.mpesaRegionRoutes.getStates;

        return $.ajax({
            url: url,
            method: 'GET',
            data: {
                country_id: 1 // Using country ID 1 for Kenya
            }
        }).done(function (response) {
            // Check if the response status is successful
            if (response.status === true) {
                // The response now has a data array with id and name for each state
                state.regions = response.data || [];
                console.log(`Loaded ${state.regions.length} regions successfully:`, state.regions);
            } else {
                console.error('Failed to load regions: Status not successful', response);
                Botble.showError(response.message || 'Failed to load regions. Please try again.');
            }
            updateUI();
        }).fail(function (error) {
            console.error('Failed to load regions:', error);
            Botble.showError('Failed to load regions. Please refresh the page and try again.');
        }).always(function () {
            state.isLoading = false;
            updateUI();
        });
    }

    /**
     * Load existing region settings
     */
    function loadRegionSettings() {
        state.isLoading = true;
        updateUI();

        // Reset existing settings
        state.regionSettings = [];

        // For each region, try to load its settings
        const promises = state.regions.map(region => {
            // Replace placeholder in route with actual region ID
            const url = window.mpesaRegionRoutes.getSettings.replace('__regionId__', region.id);

            return $.ajax({
                url: url,
                method: 'GET'
            }).done(function (response) {
                // Only add if we have actual settings (any field is not null)
                if (
                    response.businessShortcode ||
                    response.passkey ||
                    response.consumerKey ||
                    response.consumerSecret ||
                    response.paymentMode
                ) {
                    state.regionSettings.push({
                        id: region.id,
                        regionId: region.id,
                        regionName: region.name,
                        businessShortcode: response.businessShortcode || '',
                        passkey: response.passkey || '',
                        consumerKey: response.consumerKey || '',
                        consumerSecret: response.consumerSecret || '',
                        paymentMode: response.paymentMode || '0'
                    });
                }
            }).fail(function (error) {
                console.log(`No settings found for region ${region.id} (${region.name})`, error);
                // No need to show error - it's expected that some regions won't have settings yet
            });
        });

        // When all requests are done
        return $.when.apply($, promises).always(function () {
            state.isLoading = false;
            updateUI();
        });
    }

    /**
     * Add a new region setting
     */
    function addRegionSetting(regionId) {
        regionId = parseInt(regionId);

        // Check if we already have a setting for this region
        if (state.regionSettings.some(setting => setting.regionId === regionId)) {
            Botble.showError('A setting for this region already exists.');
            return;
        }

        // Find the region
        const region = state.regions.find(r => r.id === regionId);
        if (!region) {
            Botble.showError('Selected region not found.');
            return;
        }

        // Add new setting
        state.regionSettings.push({
            id: regionId,
            regionId: regionId,
            regionName: region.name,
            businessShortcode: '',
            passkey: '',
            consumerKey: '',
            consumerSecret: '',
            paymentMode: '0' // Default to Sandbox
        });

        // Reset region selector
        $('#region-selector').val('');

        // Update UI
        updateUI();

        Botble.showSuccess(`Added new setting for ${region.name}. Don't forget to save!`);
    }

    /**
     * Delete a region setting
     */
    function deleteRegionSetting(settingId) {
        settingId = parseInt(settingId);

        if (confirm('Are you sure you want to delete this region setting?')) {
            // Find the setting in our state
            const settingIndex = state.regionSettings.findIndex(setting => setting.id === settingId);

            if (settingIndex !== -1) {
                const setting = state.regionSettings[settingIndex];
                const url = window.mpesaRegionRoutes.deleteSettings.replace('__regionId__', settingId);

                // Delete from backend
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    cache: false // Prevent caching of the delete request
                }).done(function (response) {
                    // Remove from our state array
                    state.regionSettings.splice(settingIndex, 1);
                    updateUI();
                    Botble.showSuccess(response.message || 'Setting deleted successfully.');

                    // Force a refresh of the settings after a short delay to ensure database updates are complete
                    setTimeout(() => {
                        loadRegionSettings();
                    }, 500);
                }).fail(function (error) {
                    console.error('Failed to delete setting:', error);
                    if (error.responseJSON && error.responseJSON.message) {
                        Botble.showError(error.responseJSON.message);
                    } else {
                        Botble.showError('Failed to delete setting. Please try again.');
                    }
                });
            }
        }
    }

    /**
     * Save a region setting
     */
    function saveRegionSetting(settingId) {
        settingId = parseInt(settingId);

        // Find the setting in our state
        const setting = state.regionSettings.find(s => s.id === settingId);

        if (!setting) {
            Botble.showError('Setting not found.');
            return;
        }

        // Get the form data
        const $form = $(`#region-setting-form-${settingId}`);

        const formData = {
            regionId: settingId,
            settings: {
                businessShortcode: $form.find(`.business-shortcode-${settingId}`).val(),
                passkey: $form.find(`.passkey-${settingId}`).val(),
                consumerKey: $form.find(`.consumer-key-${settingId}`).val(),
                consumerSecret: $form.find(`.consumer-secret-${settingId}`).val(),
                paymentMode: $form.find(`.payment-mode-${settingId}`).val()
            }
        };

        // Save to backend
        $.ajax({
            url: window.mpesaRegionRoutes.saveSettings,
            method: 'POST',
            data: formData,
            cache: false // Prevent caching
        }).done(function (response) {
            // Update our state
            setting.businessShortcode = formData.settings.businessShortcode;
            setting.passkey = formData.settings.passkey;
            setting.consumerKey = formData.settings.consumerKey;
            setting.consumerSecret = formData.settings.consumerSecret;
            setting.paymentMode = formData.settings.paymentMode;

            Botble.showSuccess(response.message || 'Setting saved successfully.');

            // Force a refresh of settings to ensure we have the latest data
            setTimeout(() => {
                loadRegionSettings();
            }, 500);
        }).fail(function (error) {
            console.error('Failed to save setting:', error);
            if (error.responseJSON && error.responseJSON.message) {
                Botble.showError(error.responseJSON.message);
            } else {
                Botble.showError('Failed to save setting. Please try again.');
            }
        });
    }

    function updateUI() {
        // Update region selector
        const $regionSelector = $('#region-selector');
        $regionSelector.empty().append('<option value="">Select a region...</option>');

        // Filter out regions that already have settings
        const availableRegions = state.regions.filter(region => {
            // Convert IDs to strings for consistent comparison
            const regionId = String(region.id);
            return !state.regionSettings.some(setting => String(setting.regionId) === regionId);
        });

        console.log(`Available regions: ${availableRegions.length}, Total regions: ${state.regions.length}`);

        // If no available regions, disable the selector and add button
        if (availableRegions.length === 0) {
            $regionSelector.append('<option disabled>All regions have settings</option>');
            $('#add-region-setting').prop('disabled', true)
                .attr('title', 'All available regions already have settings');
        } else {
            $('#add-region-setting').prop('disabled', false)
                .attr('title', 'Add setting for selected region');

            // Add available regions to dropdown
            availableRegions.forEach(region => {
                $regionSelector.append(`<option value="${region.id}">${region.name}</option>`);
            });
        }

        // Update region settings list
        const $settingsList = $('#mpesa-region-settings-list');
        $settingsList.empty();

        if (state.isLoading) {
            $settingsList.html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
            return;
        }

        if (state.regionSettings.length === 0) {
            $settingsList.html('<div class="alert alert-warning">No region-specific settings found. Add a new region setting above.</div>');
            return;
        }

        // Render each region setting
        state.regionSettings.forEach(setting => {
            const regionName = setting.regionName || `Region ${setting.regionId}`;
            const settingId = setting.id;

            const card = `
            <div class="card mb-3 region-setting-card" data-id="${settingId}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">${regionName} Region Settings</h5>
                    <button type="button" class="btn btn-sm btn-danger delete-region-setting" data-id="${settingId}">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <form id="region-setting-form-${settingId}">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="business-shortcode-${settingId}">Business Shortcode</label>
                                <input type="text" id="business-shortcode-${settingId}" class="form-control business-shortcode-${settingId}" value="${setting.businessShortcode}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="passkey-${settingId}">Passkey</label>
                                <input type="password" id="passkey-${settingId}" class="form-control passkey-${settingId}" value="${setting.passkey}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="consumer-key-${settingId}">Consumer Key</label>
                                <input type="text" id="consumer-key-${settingId}" class="form-control consumer-key-${settingId}" value="${setting.consumerKey}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="consumer-secret-${settingId}">Consumer Secret</label>
                                <input type="password" id="consumer-secret-${settingId}" class="form-control consumer-secret-${settingId}" value="${setting.consumerSecret}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="payment-mode-${settingId}">Payment Mode</label>
                                <select id="payment-mode-${settingId}" class="form-control payment-mode-${settingId}">
                                    <option value="0" ${setting.paymentMode === '0' ? 'selected' : ''}>Sandbox</option>
                                    <option value="1" ${setting.paymentMode === '1' ? 'selected' : ''}>Production</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-primary save-region-setting" data-id="${settingId}">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

            $settingsList.append(card);
        });
    }

    // Initialize on document ready
    $(function() {
        // Check if we're on the Mpesa settings page
        if ($('#mpesa-settings-container').length) {
            // Wait for Botble's JS to finish loading
            setTimeout(init, 1500);
        }
    });

})(jQuery);