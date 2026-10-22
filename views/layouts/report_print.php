<?php
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
?>
<!doctype html>
<html lang="en"><head>
<?php require __DIR__.'/../partials/site_favicon.php'; ?><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $escape($report_title) ?> — <?= $escape($store_name) ?></title><link rel="stylesheet" href="<?= url('assets/css/report-print.css') ?>?v=<?= filemtime(__DIR__.'/../../assets/css/report-print.css') ?>"><link rel="stylesheet" href="<?= url('assets/css/print-actions.css') ?>">
<?php if (($print_orientation ?? '') === 'portrait'): ?>
<style>
@media print {
 @page { size:A4 portrait; margin:10mm; }
 .monthly-business-report { table-layout:fixed; width:100%; }
 .monthly-business-report th:first-child { width:19%; }
 .monthly-business-report th:last-child { width:31%; }
 .monthly-business-report th { white-space:normal; }
 .monthly-business-report td { padding:3px 7px; font-size:9px; }
 .print-letterhead { padding-bottom:7px; margin-bottom:8px; }
 .print-heading { padding-bottom:8px; margin-bottom:8px; }
 .print-filters { padding:7px 9px; margin-bottom:8px; }
 .print-section { margin-top:10px; }
 .print-footer { margin-top:10px; }
}
</style>
<?php endif; ?>
</head>
<body>
<nav class="print-toolbar" aria-label="Print actions"><a href="<?= $escape(url($back_url)) ?>">Back to Reports</a><button type="button" onclick="window.print()" aria-label="Print report or save as PDF">Print / Save PDF</button></nav>
<main class="print-sheet">
<header class="print-heading print-formal-header">
    <div class="print-letterhead"><p class="print-business-name"><?= $escape($store_name) ?></p><p class="print-business-caption">Business Reports</p></div>
    <div class="print-document-heading"><div><p class="print-document-label">REPORT</p><h1><?= $escape($report_title) ?></h1></div><div class="print-meta"><div><span>Prepared by</span><strong><?= $escape($_SESSION['user']['name'] ?? 'Staff') ?></strong></div><div><span>Date prepared</span><strong><?= date('M d, Y · h:i A') ?></strong></div></div></div>
</header>
<div class="print-filters"><strong>Branch:</strong> <?= $escape($branch_name) ?> &nbsp; · &nbsp; <strong>Dates:</strong> <?= $escape($date_label) ?><?php if (!empty($search)): ?> &nbsp; · &nbsp; <strong>Search:</strong> <?= $escape($search) ?><?php endif; ?></div>
<?php if (!empty($cards)): ?><div class="print-stats"><?php foreach ($cards as $label => $value): ?><div class="print-stat"><span><?= $escape($label) ?></span><strong><?= $escape($value) ?></strong></div><?php endforeach; ?></div><?php endif; ?>
<?php foreach ($sections as $section): ?><section class="print-section"><h2><?= $escape($section['title']) ?></h2><div class="print-table-wrap"><table class="<?= $escape($section['class'] ?? '') ?>"><thead><tr><?php foreach ($section['columns'] as $column): ?><th><?= $escape($column) ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach ($section['rows'] as $row_index => $row): ?><tr class="<?= $escape($section['row_classes'][$row_index] ?? '') ?>"><?php foreach ($row as $cell): ?><td title="<?= $escape($cell) ?>"><?= $escape($cell) ?></td><?php endforeach; ?></tr><?php endforeach; ?><?php if (!$section['rows']): ?><tr><td class="empty" colspan="<?= count($section['columns']) ?>">No records match these filters.</td></tr><?php endif; ?></tbody><?php if (!empty($section['footer'])): ?><tfoot><tr><?php foreach ($section['footer'] as $column_index => $cell): ?><td class="<?= $column_index === 6 ? 'number' : '' ?>"><?= $escape($cell) ?></td><?php endforeach; ?></tr></tfoot><?php endif; ?></table></div></section><?php endforeach; ?>
<?php if (!empty($bottom_totals)): ?>
<div class="print-bottom-totals" aria-label="Report totals">
<?php foreach ($bottom_totals as $label => $value): ?><div><span><?= $escape($label) ?></span><strong><?= $escape($value) ?></strong></div><?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!empty($report_note)): ?><p class="print-note"><?= $escape($report_note) ?></p><?php endif; ?><footer class="print-footer">End of <?= $escape($report_title) ?> · <?= $escape($store_name) ?></footer></main>
</body></html>
