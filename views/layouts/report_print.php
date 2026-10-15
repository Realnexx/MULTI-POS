<?php
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
?>
<!doctype html>
<html lang="en"><head>
<?php require __DIR__.'/../partials/site_favicon.php'; ?><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $escape($report_title) ?> — <?= $escape($store_name) ?></title><link rel="stylesheet" href="<?= url('assets/css/report-print.css') ?>"><link rel="stylesheet" href="<?= url('assets/css/print-actions.css') ?>">
</head>
<body>
<nav class="print-toolbar" aria-label="Print actions"><a href="<?= $escape(url($back_url)) ?>">Back to Reports</a><button type="button" onclick="window.print()">Print / Save PDF</button></nav>
<main class="print-sheet">
<header class="print-heading print-formal-header">
    <div class="print-letterhead"><p class="print-business-name"><?= $escape($store_name) ?></p><p class="print-business-caption">Business Reports</p></div>
    <div class="print-document-heading"><div><p class="print-document-label">REPORT</p><h1><?= $escape($report_title) ?></h1></div><div class="print-meta"><div><span>Prepared by</span><strong><?= $escape($_SESSION['user']['name'] ?? 'Staff') ?></strong></div><div><span>Date prepared</span><strong><?= date('M d, Y · h:i A') ?></strong></div></div></div>
</header>
<div class="print-filters"><strong>Branch:</strong> <?= $escape($branch_name) ?> &nbsp; · &nbsp; <strong>Dates:</strong> <?= $escape($date_label) ?><?php if (!empty($search)): ?> &nbsp; · &nbsp; <strong>Search:</strong> <?= $escape($search) ?><?php endif; ?></div>
<?php if (!empty($cards)): ?><div class="print-stats"><?php foreach ($cards as $label => $value): ?><div class="print-stat"><span><?= $escape($label) ?></span><strong><?= $escape($value) ?></strong></div><?php endforeach; ?></div><?php endif; ?>
<?php foreach ($sections as $section): ?><section class="print-section"><h2><?= $escape($section['title']) ?></h2><div class="print-table-wrap"><table class="<?= $escape($section['class'] ?? '') ?>"><thead><tr><?php foreach ($section['columns'] as $column): ?><th><?= $escape($column) ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach ($section['rows'] as $row): ?><tr><?php foreach ($row as $cell): ?><td title="<?= $escape($cell) ?>"><?= $escape($cell) ?></td><?php endforeach; ?></tr><?php endforeach; ?><?php if (!$section['rows']): ?><tr><td class="empty" colspan="<?= count($section['columns']) ?>">No records match these filters.</td></tr><?php endif; ?></tbody></table></div></section><?php endforeach; ?>
<?php if (!empty($report_note)): ?><p class="print-note"><?= $escape($report_note) ?></p><?php endif; ?><footer class="print-footer">End of <?= $escape($report_title) ?> · <?= $escape($store_name) ?></footer></main>
</body></html>
