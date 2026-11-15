@php $mpesaStatus = setting('payment_mpesa_status'); @endphp
<table class="table payment-method-item">
    <tbody>
        <tr class="border-pay-row">
            <td class="border-pay-col"><i class="fa fa-theme-payments"></i></td>
            <td style="width: 20%;">
                <img style="height: 40px; filter: grayscale(1)"
                    src="{{ url('vendor/core/plugins/mpesa/images/logo.jpg') }}" alt="MPESA">
            </td>
            <td class="border-right">
                <ul>
                    <li>
                        <a href="https://mpesa.safaricom.co.ke" target="_blank">MPESA</a>
                        <p>{{ trans('plugins/mpesa::mpesa.mpesa_description') }}</p>
                    </li>
                </ul>
            </td>
        </tr>
        <tr class="bg-white">
            <td colspan="3">
                <div class="float-start" style="margin-top: 5px;">
                    <div class="payment-name-label-group @if ($mpesaStatus == 0) hidden @endif">
                        <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span> <label
                            class="ws-nm inline-display method-name-label">{{ setting('payment_mpesa_name') }}</label>
                    </div>
                </div>
                <div class="float-end">
                    <a
                        class="btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger @if ($mpesaStatus == 0) hidden @endif">{{ trans('plugins/payment::payment.edit') }}</a>
                    <a
                        class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger @if ($mpesaStatus == 1) hidden @endif">{{ trans('plugins/payment::payment.settings') }}</a>
                </div>
            </td>
        </tr>
        <tr class="mpesa-online-payment payment-content-item hidden">
            <td class="border-left" colspan="3">
                {!! Form::open() !!}
                {!! Form::hidden('type', MPESA_PAYMENT_METHOD_NAME, ['class' => 'payment_type']) !!}
                <div class="row">
                    <div class="col-sm-6">
                        <ul>
                            <li>
                                <label>{{ trans('plugins/payment::payment.configuration_instruction', ['name' => 'mpesa']) }}</label>
                            </li>
                            <li class="payment-note">
                                <p>{{ trans('plugins/payment::payment.configuration_requirement', ['name' => 'mpesa']) }}:
                                </p>
                                <ul class="m-md-l" style="list-style-type:decimal">
                                    <li style="list-style-type:decimal">
                                        <a href="https://mpesa.safaricom.co.ke" target="_blank">
                                            {{ trans('plugins/payment::payment.service_registration', ['name' => 'mpesa']) }}
                                        </a>
                                    </li>
                                    <li style="list-style-type:decimal">
                                        <p>{{ trans('plugins/mpesa::mpesa.after_service_registration_msg') }}</p>
                                    </li>
                                    <li style="list-style-type:decimal">
                                        <p>{{ trans('plugins/mpesa::mpesa.enter_credentials') }}</p>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <div class="well bg-white">
                            <div class="form-group mb-3">
                                <label class="text-title-field"
                                    for="mpesa_name">{{ trans('plugins/payment::payment.method_name', ['name' => 'mpesa']) }}</label>
                                <input type="text" class="next-input input-name" name="payment_mpesa_name"
                                    id="mpesa_name" data-counter="400"
                                    value="{{ setting('payment_mpesa_name', trans('plugins/payment::payment.pay_online_via', ['name' => 'mpesa'])) }}">
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-title-field"
                                    for="payment_mpesa_description">{{ trans('core/base::forms.description') }}</label>
                                <textarea class="next-input" name="payment_mpesa_description"
                                    id="payment_mpesa_description">{{ get_payment_setting('description', 'mpesa', __('You will be receive STK push to complete Mpesa payment.')) }}</textarea>
                            </div>
                            <p class="payment-note">
                                {{ trans('plugins/payment::payment.please_provide_information') }} <a target="_blank"
                                    href="https://mpesa.safaricom.co.ke">Mpesa</a>:
                            </p>
                            <div class="form-group mb-3">
                                <label class="text-title-field"
                                    for="mpesa_business_shortcode">{{ trans('plugins/mpesa::mpesa.mpesa_business_shortcode') }}</label>
                                <input type="text" class="next-input" name="mpesa_business_shortcode"
                                    id="mpesa_business_shortcode" value="{{ setting('mpesa_business_shortcode') }}">
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-title-field" for="mpesa_till_number">{{ __('Store / Till Number') }}</label>
                                <input type="text" class="next-input" name="mpesa_till_number"
                                    id="mpesa_till_number" value="{{ setting('mpesa_till_number') }}">
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-title-field"
                                    for="mpesa_passkey">{{ trans('plugins/mpesa::mpesa.mpesa_passkey') }}</label>
                                <input type="text" class="next-input" name="mpesa_passkey" id="mpesa_passkey"
                                    value="{{ setting('mpesa_passkey') }}">
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-title-field"
                                    for="mpesa_consumer_key">{{ trans('plugins/mpesa::mpesa.mpesa_consumer_key') }}</label>
                                <input type="text" class="next-input" name="mpesa_consumer_key" id="mpesa_consumer_key"
                                    value="{{ setting('mpesa_consumer_key') }}">
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-title-field"
                                    for="mpesa_consumer_secret">{{ trans('plugins/mpesa::mpesa.mpesa_consumer_secret') }}</label>
                                <input type="text" class="next-input" name="mpesa_consumer_secret"
                                    id="mpesa_consumer_secret" value="{{ setting('mpesa_consumer_secret') }}">
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-title-field"
                                    for="mpesa_mode">{{ trans('plugins/mpesa::mpesa.mpesa_mode') }}</label>
                                <div class="input-option">
                                    <select name="mpesa_mode" class="next-input">
                                        <option {{ setting('mpesa_mode') == 0 ? 'selected' : '' }} value="0">
                                            {{ __('Sandbox') }}</option>
                                        <option {{ setting('mpesa_mode') == 1 ? 'selected' : '' }} value="1">
                                            {{ __('Production') }}</option>
                                    </select>
                                </div>
                            </div>
                            {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, 'mpesa') !!}
                        </div>
                    </div>
                </div>
                <div class="col-12 bg-white text-end">
                    <button class="btn btn-warning disable-payment-item @if ($mpesaStatus == 0) hidden @endif"
                        type="button">{{ trans('plugins/payment::payment.deactivate') }}</button>
                    <button
                        class="btn btn-info save-payment-item btn-text-trigger-save @if ($mpesaStatus == 1) hidden @endif"
                        type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                    <button
                        class="btn btn-info save-payment-item btn-text-trigger-update @if ($mpesaStatus == 0) hidden @endif"
                        type="button">{{ trans('plugins/payment::payment.update') }}</button>
                </div>
                {!! Form::close() !!}
            </td>
        </tr>
    </tbody>
</table>

<!-- Region-specific M-Pesa Settings Card -->
<div class="card mt-3" id="mpesa-settings-container">
    <div class="card-header">
        <h4>Region-specific M-Pesa Settings</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <i class="fa fa-info-circle"></i>
            Configure Mpesa payment settings for each region. Each region can have its own unique Mpesa configuration.
        </div>

        <div class="d-flex align-items-center mb-3" id="region-selector-container">
            <div class="flex-grow-1 me-2">
                <select id="region-selector" class="form-control">
                    <option value="">Select a region...</option>
                    <!-- Options will be populated by JavaScript -->
                </select>
            </div>
            <button id="add-region-setting" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add Setting
            </button>
        </div>

        <!-- Region settings list container - Will be populated by JavaScript -->
        <div id="mpesa-region-settings-list" class="mt-4">
            <!-- Region settings cards will be dynamically inserted here -->
        </div>
    </div>
</div>

<!-- Include region settings JS -->
<script>
    $(document).ready(function() {
        // Only initialize after Botble's JS has loaded
        if ($('#mpesa-settings-container').length) {
            // Set route names for use in JS
            window.mpesaRegionRoutes = {
                getStates: '{{ route("payments.mpesa.regions.states") }}',
                getSettings: '{{ route("payments.mpesa.regions.get", ["regionId" => "__regionId__"]) }}',
                saveSettings: '{{ route("payments.mpesa.regions.save") }}',
                deleteSettings: '{{ route("payments.mpesa.regions.delete", ["regionId" => "__regionId__"]) }}'
            };
        }
    });
</script>
