<?php
include "login.check.php";
require_once dirname(__DIR__) . '/lib/bootstrap.php';
include "header.php";

use Midhub\Db;
use Midhub\Ops\CircuitBreaker;
use Midhub\Security\Csrf;

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$alerts = Db::all('SELECT * FROM admin_alerts ORDER BY id DESC LIMIT 200');
$breakers = CircuitBreaker::all();
$flash = isset($_SESSION['gw_flash']) ? $_SESSION['gw_flash'] : null;
unset($_SESSION['gw_flash']);
$sev = array('critical' => 'danger', 'warning' => 'warning', 'info' => 'info');
?>
<div class="content-page"><div class="content"><div class="container-fluid">
  <div class="row"><div class="col-12"><div class="page-title-box">
    <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="home.php">Home</a></li><li class="breadcrumb-item active">System Alerts</li></ol></div>
    <h4 class="page-title">System Alerts</h4>
  </div></div></div>
  <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>"><?= $flash['html'] ?></div><?php endif; ?>
  <div class="card"><div class="card-body">
    <h5 class="mt-0">Automatic kill switches (circuit breakers)</h5>
    <p class="text-muted small">After 5 consecutive failures a provider (or one operator at Paywell) is paused for 10 minutes: recharges go to the manual queue and failing payment methods are hidden. It resumes automatically.</p>
    <table class="table table-sm small align-middle mb-0">
      <thead><tr><th>Service</th><th>State</th><th class="text-end">Recent failures</th><th>Last error</th><th>Updated</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($breakers as $b): ?>
        <tr><td class="font-monospace"><?= $h($b['cb_key']) ?></td>
            <td><?= $b['is_open'] ? '<span class="badge bg-danger">PAUSED until ' . $h($b['opened_until']) . '</span>' : '<span class="badge bg-success">running</span>' ?></td>
            <td class="text-end"><?= (int) $b['failures'] ?></td><td><?= $h($b['last_error']) ?></td><td class="text-nowrap"><?= $h($b['updated_at']) ?></td>
            <td><form method="post" action="route.php?action=BreakerReset" class="m-0"><?= Csrf::field() ?><input type="hidden" name="cb_key" value="<?= $h($b['cb_key']) ?>"><button class="btn btn-sm btn-outline-primary py-0">Resume now</button></form></td></tr>
      <?php endforeach; ?>
      <?php if (!$breakers): ?><tr><td colspan="6" class="text-center text-muted">All services healthy.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
  <div class="card"><div class="card-body">
    <p class="text-muted small">Low provider balance, deposits held for review (paid but not credited) and stuck transactions. Raised by the cron jobs in <code>cron/</code>. Set <code>ALERT_EMAIL</code> in <code>.env</code> to also receive them by email.</p>
    <div class="table-responsive">
    <table class="table table-sm small align-middle">
      <thead><tr><th>When</th><th>Severity</th><th>Title</th><th>Message</th></tr></thead>
      <tbody>
      <?php foreach ($alerts as $a): ?>
        <tr><td class="text-nowrap"><?= $h($a['created_at']) ?></td><td><span class="badge bg-<?= $h(isset($sev[$a['severity']]) ? $sev[$a['severity']] : 'secondary') ?>"><?= $h($a['severity']) ?></span></td>
            <td><?= $h($a['title']) ?></td><td><?= $h($a['message']) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$alerts): ?><tr><td colspan="4" class="text-center text-muted">No alerts.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div></div>
</div></div>
<?php include "footer-data.php"; ?>
