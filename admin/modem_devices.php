<?php
include "login.check.php";
require_once dirname(__DIR__) . '/lib/bootstrap.php';
include "header.php";

use Midhub\Db;
use Midhub\Security\Csrf;

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$flash = isset($_SESSION['gw_flash']) ? $_SESSION['gw_flash'] : null;
unset($_SESSION['gw_flash']);
$newToken = isset($_SESSION['modem_new_token']) ? $_SESSION['modem_new_token'] : null;
unset($_SESSION['modem_new_token']);
$devices = Db::all("SELECT d.*, (SELECT COUNT(*) FROM send_query q WHERE q.modem_device_id = d.id AND q.status = '1' AND q.submitted_date > NOW() - INTERVAL 1 DAY) AS done24,
                           (SELECT COUNT(*) FROM send_query q WHERE q.modem_device_id = d.id AND q.status = '4') AS in_progress
                    FROM modem_devices d ORDER BY d.id DESC");
$base = rtrim(APP_URL, '/') . '/apimobile/';
?>
<div class="content-page"><div class="content"><div class="container-fluid">
  <div class="row"><div class="col-12"><div class="page-title-box">
    <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="home.php">Home</a></li><li class="breadcrumb-item active">SIM Modem Devices</li></ol></div>
    <h4 class="page-title">SIM Modem Gateway Devices</h4>
  </div>
  <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show"><?= $flash['html'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  <?php if ($newToken): ?>
    <div class="alert alert-warning"><b>Device token (shown once):</b><div class="input-group mt-2"><input class="form-control font-monospace" readonly value="<?= $h($newToken['token']) ?>" id="newtok"><button class="btn btn-outline-secondary" type="button" onclick="var i=document.getElementById('newtok');i.select();document.execCommand('copy');this.textContent='Copied';">Copy</button></div>
    <small>Put it in the modem app as the API key, or send it as <code>Authorization: Bearer &lt;token&gt;</code>. Only a hash is stored; if it is lost, create a new device and disable this one.</small></div>
  <?php endif; ?>
  </div></div>

  <div class="row">
    <div class="col-lg-4"><div class="card"><div class="card-body">
      <h5 class="mt-0">Add device</h5>
      <form method="post" action="route.php?action=ModemDeviceCreate" autocomplete="off">
        <?= Csrf::field() ?>
        <div class="mb-2"><label class="form-label small mb-1">Name</label><input class="form-control form-control-sm" name="name" placeholder="GP Modem Shop-1" required></div>
        <div class="mb-2"><label class="form-label small mb-1">Operators (blank = all)</label><input class="form-control form-control-sm" name="operators" placeholder="grameenphone, robi"></div>
        <div class="mb-2"><label class="form-label small mb-1">Self-PIN</label><input type="password" inputmode="numeric" class="form-control form-control-sm" name="self_pin" required></div>
        <button class="btn btn-success btn-sm">Create device &amp; token</button>
      </form>
      <hr>
      <p class="small text-muted mb-1">Endpoints (manual-queue recharges, status 0):</p>
      <ul class="small font-monospace ps-3 mb-0">
        <li><?= $h($base) ?>pending_status.php?operator=grameenphone</li>
        <li><?= $h($base) ?>status_success.php?rid=ID&amp;txnid=..</li>
        <li><?= $h($base) ?>status_failed.php?rid=ID&amp;reason=..</li>
        <li><?= $h($base) ?>report_msg.php</li>
      </ul>
    </div></div></div>
    <div class="col-lg-8"><div class="card"><div class="card-body">
      <div class="table-responsive"><table class="table table-sm small align-middle">
        <thead><tr><th>Device</th><th>Operators</th><th>Token</th><th>Last seen</th><th class="text-end">Done 24h</th><th class="text-end">In progress</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($devices as $d): ?>
          <tr>
            <td><?= $h($d['name']) ?></td><td><?= $h($d['operators'] ?: 'all') ?></td><td class="font-monospace">…<?= $h($d['token_hint']) ?></td>
            <td class="text-nowrap"><?= $h($d['last_seen_at'] ?: 'never') ?><br><small class="text-muted"><?= $h($d['last_ip']) ?></small></td>
            <td class="text-end"><?= (int) $d['done24'] ?></td><td class="text-end"><?= (int) $d['in_progress'] ?></td>
            <td><span class="badge bg-<?= $d['active'] ? 'success' : 'secondary' ?>"><?= $d['active'] ? 'active' : 'disabled' ?></span></td>
            <td><form method="post" action="route.php?action=ModemDeviceToggle" class="d-flex gap-1 m-0"><?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                <input type="password" inputmode="numeric" class="form-control form-control-sm" style="width:80px" name="self_pin" placeholder="PIN" required>
                <button class="btn btn-sm btn-outline-<?= $d['active'] ? 'danger' : 'success' ?> py-0"><?= $d['active'] ? 'Disable' : 'Enable' ?></button></form></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$devices): ?><tr><td colspan="8" class="text-center text-muted">No devices yet.</td></tr><?php endif; ?>
        </tbody>
      </table></div>
      <small class="text-muted">A row stuck "in progress" (status 4) appears in Waiting Recharge, where it can be resent or settled.</small>
    </div></div></div>
  </div>
</div></div>
<?php include "footer-data.php"; ?>
