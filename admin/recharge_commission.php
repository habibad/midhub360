<?php
include "login.check.php";
require_once dirname(__DIR__) . '/lib/bootstrap.php';
include "header.php";

use Midhub\Commission\CommissionService;
use Midhub\Db;
use Midhub\Security\Csrf;

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$flash = isset($_SESSION['gw_flash']) ? $_SESSION['gw_flash'] : null;
unset($_SESSION['gw_flash']);
$rates = CommissionService::rates();
$enabled = CommissionService::enabled();
$ln = Db::one('SELECT * FROM level_name WHERE id = 1') ?: array();
$names = array(
    4 => isset($ln['subadmin']) ? $ln['subadmin'] : 'Sub Admin',
    3 => isset($ln['level4']) ? $ln['level4'] : 'Level 3',
    2 => isset($ln['level3']) ? $ln['level3'] : 'Level 2',
    1 => isset($ln['level2']) ? $ln['level2'] : 'Level 1',
    0 => isset($ln['level1']) ? $ln['level1'] : 'Retailer',
);
$legacyUpper = Db::one('SELECT * FROM commission WHERE id = 2');
$legacyOn = $legacyUpper && ((float) $legacyUpper['subadmin'] + (float) $legacyUpper['level4'] + (float) $legacyUpper['level3'] + (float) $legacyUpper['level2']) > 0;
$recent = Db::all('SELECT p.*, u.user FROM commission_payouts p LEFT JOIN user u ON u.id = p.beneficiary_id ORDER BY p.id DESC LIMIT 30');
$total30 = Db::one('SELECT COUNT(*) n, COALESCE(SUM(amount),0) s FROM commission_payouts WHERE created_at > NOW() - INTERVAL 30 DAY');
?>
<div class="content-page"><div class="content"><div class="container-fluid">
  <div class="row"><div class="col-12"><div class="page-title-box">
    <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="home.php">Home</a></li><li class="breadcrumb-item active">Recharge Commission</li></ol></div>
    <h4 class="page-title">5-Level Recharge Commission</h4>
  </div>
  <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show"><?= $flash['html'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  <?php if ($enabled && $legacyOn): ?>
    <div class="alert alert-warning"><b>Double commission risk:</b> the legacy "Upper commission" on <a href="commission_set.php">Commission Set</a> is also non-zero, so uplines are paid once when balance is added to a downline <i>and</i> again on every recharge. Set one of the two to 0 unless that is intended.</div>
  <?php endif; ?>
  </div></div>

  <div class="row">
    <div class="col-lg-6"><div class="card"><div class="card-body">
      <p class="text-muted small">On every <b>successful</b> recharge (Paywell, SIM modem, admin manual success), each account in the requester's chain (the requester and up to 4 uplines) receives its level's percentage of the recharge amount in its <b>Recharge wallet</b>. Paid exactly once per recharge, never for failed or refunded recharges.</p>
      <form method="post" action="route.php?action=RechargeCommissionSave" autocomplete="off">
        <?= Csrf::field() ?>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" name="enabled" value="1" id="rc-on" <?= $enabled ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="rc-on">Pay commission on recharges</label>
        </div>
        <table class="table table-sm align-middle">
          <thead><tr><th>Level</th><th>Name</th><th style="width:140px">Commission %</th><th class="text-end">On 100 Tk</th></tr></thead>
          <tbody>
          <?php for ($l = 4; $l >= 0; $l--): ?>
            <tr><td><?= $l ?></td><td><?= $h($names[$l]) ?></td>
                <td><input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm rc-rate" name="rate[<?= $l ?>]" value="<?= $h($rates[$l]) ?>"></td>
                <td class="text-end rc-ex"><?= $h(number_format((float) $rates[$l], 2)) ?> Tk</td></tr>
          <?php endfor; ?>
          </tbody>
          <tfoot><tr><th colspan="3">Maximum paid out per 100 Tk recharge (retailer request)</th><th class="text-end" id="rc-total"></th></tr></tfoot>
        </table>
        <div class="row g-2 align-items-end">
          <div class="col-sm-5"><label class="form-label small mb-1">Self-PIN</label><input type="password" inputmode="numeric" class="form-control" name="self_pin" required autocomplete="off"></div>
          <div class="col-sm-7"><button class="btn btn-success"><i class="mdi mdi-content-save"></i> Save commission</button></div>
        </div>
      </form>
    </div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-body">
      <h5 class="mt-0">Recent payouts <small class="text-muted">(30 days: <?= (int) $total30['n'] ?> payouts, <?= $h(number_format((float) $total30['s'], 2)) ?> Tk)</small></h5>
      <div class="table-responsive"><table class="table table-sm small">
        <thead><tr><th>Recharge #</th><th>To</th><th>Level</th><th class="text-end">Rate</th><th class="text-end">Amount</th><th>When</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $p): ?>
          <tr><td><?= (int) $p['source_id'] ?></td><td><?= $h($p['user']) ?></td><td><?= (int) $p['beneficiary_level'] ?></td><td class="text-end"><?= $h($p['rate']) ?>%</td><td class="text-end"><?= $h($p['amount']) ?></td><td class="text-nowrap"><?= $h($p['created_at']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?><tr><td colspan="6" class="text-center text-muted">No commission paid yet.</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </div></div></div>
  </div>
</div></div>
<script>
(function () {
  function upd() { var t = 0; document.querySelectorAll('.rc-rate').forEach(function (i) { var v = parseFloat(i.value || 0); t += v; i.closest('tr').querySelector('.rc-ex').textContent = v.toFixed(2) + ' Tk'; }); document.getElementById('rc-total').textContent = t.toFixed(2) + ' Tk'; }
  document.querySelectorAll('.rc-rate').forEach(function (i) { i.addEventListener('input', upd); }); upd();
})();
</script>
<?php include "footer-data.php"; ?>
