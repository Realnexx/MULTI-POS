<?php if (!empty($operations)): ?>
<link rel="stylesheet" href="<?= url('assets/css/dashboard-operations.css') ?>?v=<?= filemtime(__DIR__.'/../../assets/css/dashboard-operations.css') ?>">
<section class="dashboard-operations" aria-label="Sales and operations overview">
    <?php if(($operations_section??'all')==='all'||$operations_section==='summary'): ?>
    <div class="do-summaries">
        <?php foreach (['today'=>["Today's sales", 'Today'], 'week'=>['Weekly sales', 'Monday to today'], 'month'=>['Monthly sales', 'Month to date']] as $key=>$label): $summary=$operations['summaries'][$key]; ?>
        <article class="do-metric"><p><?= $label[0] ?></p><strong>₱<?= number_format($summary['total'],2) ?></strong><span><?= number_format($summary['transactions']) ?> transactions · <?= $label[1] ?></span></article>
        <?php endforeach; ?>
        <article class="do-metric"><p>Estimated gross profit</p><strong>₱<?= number_format($operations['gross_profit'],2) ?></strong><span><?= $display_date ?> · Sales less estimated item costs</span></article>
    </div>
    <p class="do-note">Completed sales only. Weeks start on Monday. Gross profit uses recorded stock costs where available and current product costs otherwise; it excludes operating expenses.</p>
    <?php endif; ?>
    <?php if(($operations_section??'all')==='all'||$operations_section==='comparisons'): ?>
    <div class="do-panels">
        <?php foreach(['branches'=>'Branch performance','payments'=>'Payment methods'] as $key=>$heading): ?>
        <article class="do-panel">
            <div class="do-panel-heading"><div><h3><?= $heading ?></h3><p><?= $display_date ?> · <?= $display_branch ?></p></div><?php if($key==='branches'): ?><span><?= count($operations['branches']) ?> <?= count($operations['branches'])===1?'branch':'branches' ?></span><?php endif; ?></div>
            <?php if ($key==='branches' && $can_choose_branch): ?><p class="do-note">Choose All Branches in the branch filter to compare the entire active branch network.</p><?php endif; ?>
            <div class="do-table-scroll"><table><thead><tr><th scope="col"><?= $key==='branches'?'Branch':'Method' ?></th><th scope="col">Transactions</th><th scope="col">Sales</th></tr></thead><tbody>
            <?php foreach($operations[$key] as $row): ?><tr><th scope="row"><?= htmlspecialchars($key==='branches'?$row['name']:($row['payment_method'] ?: 'Unspecified')) ?></th><td><?= number_format($row['transactions']) ?></td><td>₱<?= number_format($row['total'],2) ?></td></tr><?php endforeach; ?>
            <?php if(!$operations[$key]): ?><tr><td colspan="3">No data available in this scope.</td></tr><?php endif; ?>
            </tbody></table></div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if((($operations_section??'all')==='all'||$operations_section==='attention')&&$operations['can_view_shifts']): ?>
    <article class="do-panel do-shifts">
        <div class="do-panel-heading"><div><h3>Cashier shifts requiring attention</h3><p>Pending reviews, cash discrepancies and shifts left open overnight · Your permitted shifts</p></div><a href="<?= url('?route=pos/shifts') ?>">View shifts →</a></div>
        <?php if(!$operations['shifts_ready']): ?><p class="do-note">Shift tracking is not set up yet.</p>
        <?php elseif(!$operations['shifts']): ?><p class="do-clear">No shifts currently require attention.</p>
        <?php else: ?><div class="do-table-scroll"><table><thead><tr><th scope="col">Cashier / branch</th><th scope="col">Shift</th><th scope="col">Status</th><th scope="col">Cash difference</th></tr></thead><tbody>
        <?php foreach($operations['shifts'] as $shift): ?><tr><th scope="row"><?= htmlspecialchars($shift['cashier_name']) ?><small><?= htmlspecialchars($shift['branch_name']) ?></small></th><td>#<?= (int)$shift['id'] ?></td><td><?= htmlspecialchars(ucwords(str_replace('_',' ',$shift['status']))) ?></td><td><?= $shift['difference']===null?'Not closed':'₱'.number_format($shift['difference'],2) ?></td></tr><?php endforeach; ?>
        </tbody></table></div><p class="do-note">Showing up to 30 shifts needing attention. Open View shifts for the full list.</p><?php endif; ?>
    </article>
    <?php endif; ?>
</section>
<?php endif; ?>
