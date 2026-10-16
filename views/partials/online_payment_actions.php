<?php if(in_array($order['status'],['pending','preparing','ready'],true) && in_array($order['payment_status'],['unpaid','processing'],true) && ($gatewayEnabled || ($gatewayPayment && in_array($gatewayPayment['state'],['creating','pending','review'],true)))): ?>
<div class="shop-panel" style="margin:20px 0;padding:20px">
<p class="eyebrow">ONLINE PAYMENT</p>
<?php if(($gatewayPayment && $gatewayPayment['mode']==='test') || (!$gatewayPayment && $gatewayEnabled && $gatewayMode==='test')): ?><p class="body-copy"><strong>Test mode — this does not collect real money.</strong></p><?php endif; ?>
<?php if($gatewayEnabled): ?><form method="post" action="<?= url('?route=orders/pay') ?>"><input type="hidden" name="csrf_token" value="<?= $escape(csrf_token()) ?>"><input type="hidden" name="tracking" value="<?= $escape($order['tracking_number']) ?>"><button class="button button-red" type="submit"><?= $order['payment_status']==='processing'?'Continue PayMongo checkout':'Pay online with PayMongo' ?></button></form><?php endif; ?>
<?php if($gatewayPayment && $gatewayPayment['session_id'] && in_array($gatewayPayment['state'],['pending','creating','review'],true)): ?><form method="post" action="<?= url('?route=orders/verify_payment') ?>" data-payment-verify style="margin-top:12px"><input type="hidden" name="csrf_token" value="<?= $escape(csrf_token()) ?>"><input type="hidden" name="tracking" value="<?= $escape($order['tracking_number']) ?>"><button class="button" type="submit">Check payment status</button></form><?php endif; ?>
<?php if($gatewayPayment && $gatewayPayment['state']==='pending'): ?><form method="post" action="<?= url('?route=orders/cancel_payment') ?>" style="margin-top:12px"><input type="hidden" name="csrf_token" value="<?= $escape(csrf_token()) ?>"><input type="hidden" name="tracking" value="<?= $escape($order['tracking_number']) ?>"><button class="button" type="submit">Cancel online payment</button></form><?php endif; ?>
<p class="body-copy" style="font-size:13px;margin-top:12px"><?= $order['payment_status']==='processing'?'A payment is in progress. Returning or cancelling the hosted page does not mark the order paid or cancel the payment session. Check its status before paying again.':'You may also pay at the counter. Online payment is confirmed only after PayMongo verifies it.' ?></p>
</div>
<?php endif; ?>

<?php if(isset($_GET['payment_return']) || isset($_GET['payment_cancelled'])): ?>
<script>document.querySelector('form[data-payment-verify]')?.requestSubmit();</script>
<?php endif; ?>
