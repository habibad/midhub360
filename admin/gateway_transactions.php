<?php
include "login.check.php";
require_once dirname(__DIR__) . '/lib/bootstrap.php';
include "header.php";

use Midhub\Db;
use Midhub\Security\Csrf;

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$view = (isset($_GET['view']) && $_GET['view'] === 'recharge') ? 'recharge' : 'deposit';
$status = isset($_GET['status']) ? preg_replace('/[^a-z0-9]/', '', (string) $_GET['status']) : '';
$flash = isset($_SESSION['gw_flash']) ? $_SESSION['gw_flash'] : null;
unset($_SESSION['gw_flash']);

if ($view === 'deposit') {
    $sql = 'SELECT t.*, u.user AS username FROM gateway_transactions t LEFT JOIN user u ON u.id = t.user_id';
    $rows = $status !== ''
        ? Db::all($sql . ' WHERE t.status = ? ORDER BY t.id DESC LIMIT 200', 's', array($status))
        : Db::all($sql . ' ORDER BY t.id DESC LIMIT 200');
    $totals = Db::all("SELECT status, COUNT(*) n, SUM(credit_amount) total FROM gateway_transactions WHERE created_at > NOW() - INTERVAL 30 DAY GROUP BY status");
} else {
    $sql = "SELECT q.id, q.phone, q.operator, q.balance, q.status, q.gateway_mode, q.gateway_ref, q.gateway_status, q.txnid, q.msg, q.submitted_date, q.check_attempts, u.user AS username
            FROM send_query q LEFT JOIN user u ON u.id = q.user_id WHERE q.gateway = 'paywell'";
    $rows = $status !== ''
        ? Db::all($sql . ' AND q.status = ? ORDER BY q.id DESC LIMIT 200', 's', array($status))
        : Db::all($sql . ' ORDER BY q.id DESC LIMIT 200');
    $totals = Db::all("SELECT status, COUNT(*) n, SUM(balance) total FROM send_query WHERE gateway = 'paywell' AND submitted_date > NOW() - INTERVAL 30 DAY GROUP BY status");
}
$badge = array('completed' => 'success', 'pending' => 'warning', 'initiated' => 'secondary', 'failed' => 'danger', 'cancelled' => 'dark', 'review' => 'danger',
    '1' => 'success', '6' => 'warning', '3' => 'danger', '0' => 'secondary');
$rcNames = array('0' => 'manual queue', '1' => 'success', '3' => 'failed / refunded', '6' => 'processing', '4' => 'waiting');
?>
<div class="content-page"><div class="content"><div class="container-fluid">
  <div class="row"><div class="col-12"><div class="page-title-box">
    <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="home.php">Home</a></li><li class="breadcrumb-item active">Gateway Transactions</li></ol></div>
    <h4 class="page-title"><?= $view === 'deposit' ? 'Online Deposits' : 'Paywell Recharges' ?></h4>
  </div>
  <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show"><?= $flash['html'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  </div></div>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link <?= $view === 'deposit' ? 'active' : '' ?>" href="gateway_transactions.php">Online Deposits</a></li>
    <li class="nav-item"><a class="nav-link <?= $view === 'recharge' ? 'active' : '' ?>" href="gateway_transactions.php?view=recharge">Paywell Recharges</a></li>
  </ul>

  <div class="row mb-2">
    <?php foreach ($totals as $t): ?>
      <div class="col-6 col-md-2"><div class="card"><div class="card-body p-2">
        <span class="badge bg-<?= $h(isset($badge[$t['status']]) ? $badge[$t['status']] : 'secondary') ?>"><?= $h($view === 'recharge' ? (isset($rcNames[$t['status']]) ? $rcNames[$t['status']] : $t['status']) : $t['status']) ?></span>
        <div class="fw-bold mt-1"><?= $h(number_format((float) $t['total'], 2)) ?> Tk</div><small class="text-muted"><?= (int) $t['n'] ?> in 30 days</small>
      </div></div></div>
    <?php endforeach; ?>
  </div>

  <div class="card"><div class="card-body">
    <form class="row g-2 mb-2" method="get">
      <input type="hidden" name="view" value="<?= $h($view) ?>">
      <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
          <option value="">All statuses</option>
          <?php foreach (($view === 'deposit' ? array('pending', 'completed', 'review', 'failed', 'cancelled', 'initiated') : array('6', '1', '3', '0')) as $s): ?>
            <option value="<?= $h($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= $h($view === 'recharge' ? $rcNames[$s] : $s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
      <?php if ($view === 'recharge'): ?><div class="col"><small class="text-muted">Do not cancel <b>processing</b> rows from the legacy pending pages: the provider may still deliver them. Use <b>Check</b>; the poller settles them automatically.</small></div><?php endif; ?>
    </form>
    <div class="table-responsive">
    <?php if ($view === 'deposit'): ?>
      <table class="table table-sm table-striped small align-middle">
        <thead><tr><th>Invoice</th><th>User</th><th>Gateway</th><th>Mode</th><th>Wallet</th><th class="text-end">Credit (BDT)</th><th class="text-end">Paid</th><th>Status</th><th>Gateway Trx</th><th>Created</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="font-monospace"><?= $h($r['invoice_no']) ?></td>
            <td><?= $h($r['username']) ?> <small class="text-muted">#<?= (int) $r['user_id'] ?></small></td>
            <td><?= $h($r['gateway_slug']) ?></td>
            <td><span class="badge bg-<?= $r['mode'] === 'live' ? 'danger' : 'info' ?>"><?= $h($r['mode']) ?></span></td>
            <td><?= $h($r['wallet']) ?></td>
            <td class="text-end"><?= $h($r['credit_amount']) ?></td>
            <td class="text-end"><?= $h($r['payable_amount'] . ' ' . $r['currency']) ?><?= $r['exchange_rate'] ? '<br><small class="text-muted">@' . $h($r['exchange_rate']) . '</small>' : '' ?></td>
            <td><span class="badge bg-<?= $h(isset($badge[$r['status']]) ? $badge[$r['status']] : 'secondary') ?>"><?= $h($r['status']) ?></span>
                <?= $r['failure_reason'] ? '<br><small class="text-muted">' . $h($r['failure_reason']) . '</small>' : '' ?></td>
            <td class="font-monospace"><?= $h($r['gateway_trx_id']) ?></td>
            <td class="text-nowrap"><?= $h($r['created_at']) ?></td>
            <td><?php if (in_array($r['status'], array('pending', 'initiated'), true)): ?>
              <form method="post" action="route.php?action=DepositRecheck" class="m-0"><?= Csrf::field() ?><input type="hidden" name="invoice_no" value="<?= $h($r['invoice_no']) ?>"><button class="btn btn-xs btn-sm btn-outline-primary py-0">Check</button></form>
            <?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="11" class="text-center text-muted">No transactions.</td></tr><?php endif; ?>
        </tbody>
      </table>
    <?php else: ?>
      <table class="table table-sm table-striped small align-middle">
        <thead><tr><th>#</th><th>Ref</th><th>User</th><th>Number</th><th>Operator</th><th class="text-end">Amount</th><th>Mode</th><th>Status</th><th>Provider</th><th>Submitted</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int) $r['id'] ?></td>
            <td class="font-monospace"><?= $h($r['gateway_ref']) ?></td>
            <td><?= $h($r['username']) ?></td>
            <td><?= $h($r['phone']) ?></td>
            <td><?= $h($r['operator']) ?></td>
            <td class="text-end"><?= $h($r['balance']) ?></td>
            <td><span class="badge bg-<?= $r['gateway_mode'] === 'live' ? 'danger' : 'info' ?>"><?= $h($r['gateway_mode']) ?></span></td>
            <td><span class="badge bg-<?= $h(isset($badge[$r['status']]) ? $badge[$r['status']] : 'secondary') ?>"><?= $h(isset($rcNames[$r['status']]) ? $rcNames[$r['status']] : $r['status']) ?></span></td>
            <td><small><?= $h($r['gateway_status']) ?><?= $r['txnid'] && $r['txnid'] !== '0' ? '<br>trx ' . $h($r['txnid']) : '' ?><?= (int) $r['check_attempts'] ? '<br>' . (int) $r['check_attempts'] . ' checks' : '' ?></small></td>
            <td class="text-nowrap"><?= $h($r['submitted_date']) ?></td>
            <td><?php if ($r['status'] === '6'): ?>
              <form method="post" action="route.php?action=RechargeRecheck" class="m-0"><?= Csrf::field() ?><input type="hidden" name="gateway_ref" value="<?= $h($r['gateway_ref']) ?>"><button class="btn btn-sm btn-outline-primary py-0">Check</button></form>
            <?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="11" class="text-center text-muted">No Paywell recharges yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    <?php endif; ?>
    </div>
  </div></div>
</div></div>
<?php include "footer-data.php"; ?>
