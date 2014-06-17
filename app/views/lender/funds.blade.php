@extends('layouts.master')

@section('page-title')
Funds
@stop

@section('content')
<div class="page-header">
    <h1>Add or Withdraw Funds</h1>
</div>


<div >
    <strong>Balance available: USD {{ $currentBalance }} </strong>
</div>

<div>
    <strong>Add Funds</strong>
</div>

{{ BootstrapForm::open(array('route' => 'lender:funds', 'translationDomain' => 'fund')) }}
{{ BootstrapForm::populate($form) }}

{{ BootstrapForm::text('creditAmount', null, ['id' => 'credit-amount']) }}
{{ BootstrapForm::text('donationAmount', null, ['id' => 'donation-amount']) }}

{{ BootstrapForm::hidden('feeAmount', null, ['id' => 'fee-amount']) }}
{{ BootstrapForm::hidden('totalAmount', null, ['id' => 'total-amount']) }}


{{ BootstrapForm::label("Payment Transfer Cost") }}:
USD <span id="fee-amount-display"></span>

<br/>

{{ BootstrapForm::label("Total amount to be charged to your account") }} 
USD <span id="total-amount-display"></span>

<br/>

{{ BootstrapForm::submit('save') }}

{{ BootstrapForm::close() }}

@stop

@section('script-footer')
<script type="text/javascript">
    $(function() {
        var $donationAmount = $('#donation-amount'),
            $creditAmount = $('#credit-amount'),
            $feeAmount = $('#fee-amount'),
            $totalAmount = $('#total-amount'),
            $feeAmountDisplay = $('#fee-amount-display'),
            $totalAmountDisplay = $('#total-amount-display'),
            feePercentage = 0.025;
        
        function parseMoney(value) {
            return Number(value.replace(/[^0-9\.]+/g,""));
        }

        function formatMoney(value) {
            return value.toFixed(2);
        }
        
        function calculateAmounts() {
            var donationAmount = parseMoney($donationAmount.val()),
                creditAmount = parseMoney($creditAmount.val()),
                subtotalAmount = donationAmount + creditAmount,
                feeAmount = subtotalAmount * feePercentage,
                totalAmount = subtotalAmount + feeAmount;

            $feeAmount.val(formatMoney(feeAmount));
            $totalAmount.val(formatMoney(totalAmount));
            $feeAmountDisplay.text(formatMoney(feeAmount));
            $totalAmountDisplay.text(formatMoney(totalAmount));
        }
        
        $donationAmount.on('keyup', calculateAmounts);
        $creditAmount.on('keyup', calculateAmounts);
    });
</script>
@stop
