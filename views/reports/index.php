<div class="page-head">
    <div>
        <h1 class="page-title">Reports</h1>
        <p class="page-desc">Generate, view and export inventory reports</p>
    </div>
</div>

<div class="stat-grid">
    <a class="card card-pad" href="<?= url('/reports/inventory') ?>">
        <div class="flex"><span class="stat-icon bg-blue"><?= svg_icon('box') ?></span><div><div class="stat-label strong">Inventory Report</div><div class="td-sub">All assets with filters</div></div></div>
    </a>
    <a class="card card-pad" href="<?= url('/reports/employee-assets') ?>">
        <div class="flex"><span class="stat-icon bg-green"><?= svg_icon('users') ?></span><div><div class="stat-label strong">Employee Asset Report</div><div class="td-sub">Equipment by employee</div></div></div>
    </a>
    <a class="card card-pad" href="<?= url('/reports/loans') ?>">
        <div class="flex"><span class="stat-icon bg-amber"><?= svg_icon('clock') ?></span><div><div class="stat-label strong">Loan Report</div><div class="td-sub">Active / returned / overdue</div></div></div>
    </a>
    <a class="card card-pad" href="<?= url('/reports/department') ?>">
        <div class="flex"><span class="stat-icon bg-violet"><?= svg_icon('users') ?></span><div><div class="stat-label strong">Department Report</div><div class="td-sub">Equipment by department</div></div></div>
    </a>
    <a class="card card-pad" href="<?= url('/reports/maintenance') ?>">
        <div class="flex"><span class="stat-icon bg-cyan"><?= svg_icon('wrench') ?></span><div><div class="stat-label strong">Maintenance Report</div><div class="td-sub">Under maintenance assets</div></div></div>
    </a>
    <a class="card card-pad" href="<?= url('/reports/employee-assets') ?>#history">
        <div class="flex"><span class="stat-icon bg-red"><?= svg_icon('shield') ?></span><div><div class="stat-label strong">Asset History Report</div><div class="td-sub">From each asset record</div></div></div>
    </a>
</div>

<div class="card card-pad">
    <div class="section-label" style="margin-top:0;">Quick Exports (Excel)</div>
    <div class="page-actions" style="margin-top:10px;">
        <a class="btn btn-outline btn-sm" href="<?= url('/reports/export/inventory') ?>"><?= svg_icon('download') ?> Inventory.xlsx</a>
        <a class="btn btn-outline btn-sm" href="<?= url('/reports/export/employee-assets') ?>"><?= svg_icon('download') ?> Employee_Assets.xlsx</a>
        <a class="btn btn-outline btn-sm" href="<?= url('/reports/export/loans-active') ?>"><?= svg_icon('download') ?> Active_Loans.xlsx</a>
        <a class="btn btn-outline btn-sm" href="<?= url('/reports/export/loans-overdue') ?>"><?= svg_icon('download') ?> Overdue_Loans.xlsx</a>
        <a class="btn btn-outline btn-sm" href="<?= url('/reports/export/loans-returned') ?>"><?= svg_icon('download') ?> Returned_Loans.xlsx</a>
        <a class="btn btn-outline btn-sm" href="<?= url('/reports/export/loans-history') ?>"><?= svg_icon('download') ?> Loan_History.xlsx</a>
        <a class="btn btn-outline btn-sm" href="<?= url('/reports/export/maintenance') ?>"><?= svg_icon('download') ?> Maintenance_Report.xlsx</a>
        <a class="btn btn-outline btn-sm" href="<?= url('/reports/export/adjustments') ?>"><?= svg_icon('download') ?> Adjustments.xlsx</a>
        <a class="btn btn-outline btn-sm" href="<?= url('/reports/export/department') ?>"><?= svg_icon('download') ?> Department_Report.xlsx</a>
    </div>
</div>