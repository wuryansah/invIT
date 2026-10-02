<?php $company = \App\Models\Setting::value('company_name'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1a2233; margin: 0; padding: 32px; font-size: 13px; }
        .print-head { display: flex; justify-content: space-between; border-bottom: 2px solid #1a2233; padding-bottom: 12px; margin-bottom: 20px; }
        .print-title { font-size: 20px; font-weight: 700; letter-spacing: .3px; }
        .print-sub { color: #5b6472; margin-top: 4px; }
        .no-print { position: fixed; top: 12px; right: 12px; }
        .btn-print { background: #2456ff; color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; }
        .btn-print:hover { background: #1c43c9; }
        .muted { color: #5b6472; }
        h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .5px; margin: 24px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #cfd6e4; padding: 7px 10px; text-align: left; vertical-align: top; font-size: 13px; }
        th { background: #eef1f8; font-weight: 600; white-space: nowrap; }
        .sig { display: flex; gap: 40px; margin-top: 60px; }
        .sig-box { flex: 1; }
        .sig-line { border-top: 1px solid #1a2233; margin-top: 40px; padding-top: 6px; font-size: 12px; }
        .notice { border: 1px dashed #cfd6e4; background: #f7f8fb; padding: 10px 12px; margin-top: 24px; font-size: 12px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print"><button class="btn-print" onclick="window.print()">Print / Save as PDF</button></div>
    <div class="print-head">
        <div>
            <div class="print-title">IT EQUIPMENT LOAN FORM</div>
            <div class="print-sub">Temporary loan of equipment to an employee</div>
        </div>
        <div style="text-align:right;">
            <div class="strong"><?= e($company) ?></div>
            <div class="muted">IT Department</div>
        </div>
    </div>

    <table>
        <tr><th style="width:160px;">Loan No</th><td colspan="3"><strong><?= e($row['loan_no']) ?></strong></td></tr>
        <tr><th>Asset</th><td colspan="3"><?= e($row['asset_code']) ?> — <?= e($row['asset_name']) ?> (<?= e($row['asset_condition']) ?>)</td></tr>
        <tr><th>Serial / Model</th><td colspan="3"><?= e($row['serial_number'] ?: '—') ?> / <?= e($row['brand'] ?: '—') ?> <?= e($row['model'] ?: '') ?></td></tr>
        <tr><th>Employee</th><td><?= e($row['employee_name']) ?></td><td style="width:120px;">Employee No</td><td><?= e($row['employee_number']) ?></td></tr>
        <tr><th>Department</th><td><?= e($row['department_name'] ?? '—') ?></td><td>Position</td><td><?= e($row['position'] ?: '—') ?></td></tr>
        <tr><th>Loan Date</th><td><?= e(format_date($row['loan_date'])) ?></td><td>Expected Return</td><td><?= e(format_date($row['expected_return_date'])) ?></td></tr>
        <tr><th>Purpose</th><td colspan="3"><?= e($row['purpose'] ?: '—') ?></td></tr>
        <tr><th>Condition at Handover</th><td colspan="3"><?= e($row['condition_on_handover'] ?? e($row['asset_condition'])) ?></td></tr>
        <tr><th>Issued By</th><td><?= e($row['issued_by_name'] ?? '—') ?></td><th>Approved By</th><td><?= e($row['approved_by_name'] ?? '—') ?></td></tr>
    </table>

    <div class="notice">
        I agree to return the above equipment by the expected return date, take proper care of it and report any
        damage, malfunction or loss to the IT Department immediately. I understand this equipment remains company
        property and must be surrendered on request or upon separation from the company.
    </div>

    <div class="sig">
        <div class="sig-box">
            <div class="sig-line">Borrower signature &amp; date</div>
        </div>
        <div class="sig-box">
            <div class="sig-line">IT Department signature &amp; date</div>
        </div>
    </div>

    <script>setTimeout(() => { if (window.matchMedia('(display-mode: standalone)').matches) window.print(); }, 300);</script>
</body>
</html>