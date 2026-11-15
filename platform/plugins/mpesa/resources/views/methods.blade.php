@if (setting('payment_mpesa_status') == 1)
    <li class="list-group-item">
        <input class="magic-radio js_payment_method" type="radio" name="payment_method" id="payment_mpesa"
               @if ($selecting == MPESA_PAYMENT_METHOD_NAME) checked @endif
               value="mpesa" data-bs-toggle="collapse" data-bs-target=".payment_mpesa_wrap" data-toggle="collapse" data-target=".payment_mpesa_wrap" data-parent=".list_payment_method">
        <label for="payment_mpesa" class="text-start">{{ setting('payment_mpesa_name', trans('plugins/mpesa::mpesa.payment_via_mpesa')) }}</label>
        <div class="payment_mpesa_wrap payment_collapse_wrap collapse @if ($selecting == MPESA_PAYMENT_METHOD_NAME) show @endif" style="padding: 15px 0;">
            <p>{!! BaseHelper::clean(setting('payment_mpesa_description')) !!}</p>
        </div>
    </li>       
@endif
