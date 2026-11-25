<?php
include "login.check.php";
require_once dirname(__DIR__) . '/lib/bootstrap.php';
include "header.php";

use Midhub\Admin\GatewayAdminController;
use Midhub\Security\Csrf;

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$gateways = GatewayAdminController::viewData();
$activeTab = isset($_GET['tab']) && isset($gateways[$_GET['tab']]) ? $_GET['tab'] : (string) key($gateways);
$flash = isset($_SESSION['gw_flash']) ? $_SESSION['gw_flash'] : null;
unset($_SESSION['gw_flash']);
?>
<style>
  .gw-nav .nav-link { display:flex; justify-content:space-between; align-items:center; gap:.5rem; text-align:left; }
  .gw-nav .nav-link .badge { font-size:.65rem; }
  .gw-cred-card { border:1px solid rgba(128,128,128,.25); border-radius:.4rem; padding:1rem; height:100%; }
  .gw-cred-card.is-active { border-color:#1abc9c; box-shadow:0 0 0 1px #1abc9c inset; }
  .gw-url-group input { font-family: monospace; font-size:.8rem; }
  .gw-note { font-size:.85rem; }
  .gw-secret-hint { font-size:.75rem; }
  textarea.gw-key { font-family: monospace; font-size:.75rem; min-height: 90px; }
</style>

<div class="content-page">
  <div class="content">
    <div class="container-fluid">

      <div class="row">
        <div class="col-12">
          <div class="page-title-box">
            <div class="page-title-right">
              <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                <li class="breadcrumb-item active">Gateway Settings</li>
              </ol>
            </div>
            <h4 class="page-title">Payment &amp; Service Gateway Settings</h4>
          </div>
          <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
              <?= $flash['html'] /* pre-escaped by the controller */ ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          <?php if (!$gateways): ?>
            <div class="alert alert-warning">The <code>payment_gateways</code> table is empty. Run <code>php database/migrate.php</code> on the server.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="row">
        <!-- gateway list -->
        <div class="col-lg-3 col-md-4 mb-3">
          <div class="card"><div class="card-body p-2">
            <div class="nav flex-column nav-pills gw-nav" role="tablist" aria-orientation="vertical">
              <?php foreach ($gateways as $slug => $g): $r = $g['row']; ?>
                <a class="nav-link <?= $slug === $activeTab ? 'active' : '' ?>" data-bs-toggle="pill" href="#gw-<?= $h($slug) ?>" role="tab">
                  <span><i class="mdi <?= $h($g['def']['icon']) ?> me-1"></i><?= $h($g['def']['name']) ?></span>
                  <span class="text-nowrap">
                    <span class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $r['status'] === 'active' ? 'ON' : 'OFF' ?></span>
                    <span class="badge bg-<?= $r['mode'] === 'live' ? 'danger' : 'info' ?>"><?= $r['mode'] === 'live' ? 'LIVE' : 'SANDBOX' ?></span>
                  </span>
                </a>
              <?php endforeach; ?>
            </div>
          </div></div>
          <div class="alert alert-info gw-note">
            Secrets are stored encrypted (AES-256-GCM) and never displayed. Leave a secret field blank to keep the saved value.
            Every save requires your <b>Self-PIN</b> (5 wrong attempts lock saving for 15 minutes).
          </div>
        </div>

        <!-- gateway forms -->
        <div class="col-lg-9 col-md-8">
          <div class="tab-content">
          <?php foreach ($gateways as $slug => $g): $r = $g['row']; $def = $g['def']; ?>
            <div class="tab-pane fade <?= $slug === $activeTab ? 'show active' : '' ?>" id="gw-<?= $h($slug) ?>" role="tabpanel">
              <div class="card"><div class="card-body">

                <div class="d-flex flex-wrap justify-content-between align-items-start mb-2 gap-2">
                  <div>
                    <h4 class="header-title mb-1"><i class="mdi <?= $h($def['icon']) ?> me-1"></i><?= $h($def['name']) ?></h4>
                    <p class="text-muted gw-note mb-0"><?= $h($def['note']) ?></p>
                  </div>
                  <form method="post" action="route.php?action=GatewayTest" class="m-0">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="slug" value="<?= $h($slug) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-lan-connect"></i> Test saved <?= $h($r['mode']) ?> config</button>
                  </form>
                </div>

                <?php if ($g['return_url'] || $g['webhook_url']): ?>
                <div class="row g-2 mb-3">
                  <?php foreach (array('Return / Callback URL' => $g['return_url'], 'Webhook (IPN) URL' => $g['webhook_url']) as $label => $url): if (!$url) continue; ?>
                  <div class="col-md-6">
                    <label class="form-label mb-1 small"><?= $h($label) ?></label>
                    <div class="input-group input-group-sm gw-url-group">
                      <input type="text" class="form-control" value="<?= $h($url) ?>" readonly>
                      <button class="btn btn-outline-secondary gw-copy" type="button" data-copy="<?= $h($url) ?>" title="Copy"><i class="mdi mdi-content-copy"></i></button>
                    </div>
                  </div>
                  <?php endforeach; ?>
                  <div class="col-12"><small class="text-muted">These are set automatically on each checkout. Register the webhook URL in the provider's dashboard where required (e.g. Stripe &rarr; Developers &rarr; Webhooks).</small></div>
                </div>
                <?php endif; ?>

                <form method="post" action="route.php?action=GatewaySave" autocomplete="off">
                  <?= Csrf::field() ?>
                  <input type="hidden" name="slug" value="<?= $h($slug) ?>">

                  <div class="row g-3 align-items-center mb-3">
                    <div class="col-sm-4">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="status" value="active" id="st-<?= $h($slug) ?>" <?= $r['status'] === 'active' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="st-<?= $h($slug) ?>">Enabled</label>
                      </div>
                    </div>
                    <div class="col-sm-8">
                      <div class="btn-group" role="group" aria-label="Mode">
                        <input type="radio" class="btn-check gw-mode" name="mode" value="sandbox" id="ms-<?= $h($slug) ?>" data-slug="<?= $h($slug) ?>" <?= $r['mode'] !== 'live' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-info btn-sm" for="ms-<?= $h($slug) ?>"><i class="mdi mdi-flask-outline"></i> Sandbox / Test</label>
                        <input type="radio" class="btn-check gw-mode gw-mode-live" name="mode" value="live" id="ml-<?= $h($slug) ?>" data-slug="<?= $h($slug) ?>" <?= $r['mode'] === 'live' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-danger btn-sm" for="ml-<?= $h($slug) ?>"><i class="mdi mdi-flash"></i> Live / Production</label>
                      </div>
                      <div class="text-danger small mt-1 gw-live-note" data-slug="<?= $h($slug) ?>" style="<?= $r['mode'] === 'live' ? '' : 'display:none' ?>">
                        Live mode moves real money. Test in Sandbox first.
                      </div>
                    </div>
                  </div>

                  <div class="row g-3 mb-3">
                    <?php foreach (array('sandbox' => 'Sandbox / Test', 'live' => 'Live / Production') as $m => $mLabel): ?>
                    <div class="col-lg-6">
                      <div class="gw-cred-card <?= $r['mode'] === $m ? 'is-active' : '' ?>" data-mode-card="<?= $h($slug . ':' . $m) ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <h5 class="m-0"><?= $h($mLabel) ?></h5>
                          <span class="badge bg-<?= $g['complete'][$m] ? 'success' : 'warning' ?>"><?= $g['complete'][$m] ? 'Complete' : 'Incomplete' ?></span>
                        </div>
                        <div class="mb-2">
                          <label class="form-label small mb-1">Base URL</label>
                          <input type="url" class="form-control form-control-sm" name="<?= $m ?>_base_url" value="<?= $h($r[$m . '_base_url']) ?>" placeholder="<?= $h($def[$m . '_base_url'] ?: 'https://') ?>">
                        </div>
                        <?php foreach ($def['fields'] as $k => $f):
                            $st = $g['fields'][$m][$k];
                            $id = 'f-' . $slug . '-' . $m . '-' . $k;
                            $isTa = isset($f['type']) && $f['type'] === 'textarea'; ?>
                          <div class="mb-2">
                            <label class="form-label small mb-1" for="<?= $h($id) ?>"><?= $h($f['label']) ?><?= !empty($f['required']) ? ' <span class="text-danger">*</span>' : '' ?></label>
                            <?php if (!empty($f['secret'])): ?>
                              <?php if ($isTa): ?>
                                <textarea class="form-control form-control-sm gw-key" id="<?= $h($id) ?>" name="cred[<?= $m ?>][<?= $h($k) ?>]" placeholder="<?= $st['set'] ? 'Saved (hidden). Paste a new key to replace.' : 'Not set' ?>" spellcheck="false"></textarea>
                              <?php else: ?>
                                <input type="password" class="form-control form-control-sm" id="<?= $h($id) ?>" name="cred[<?= $m ?>][<?= $h($k) ?>]" placeholder="<?= $st['set'] ? $h('Saved: ' . $st['mask']) : 'Not set' ?>" autocomplete="new-password">
                              <?php endif; ?>
                              <?php if ($st['set']): ?>
                                <div class="form-check gw-secret-hint mt-1">
                                  <input class="form-check-input" type="checkbox" name="clear[<?= $m ?>][<?= $h($k) ?>]" value="1" id="<?= $h($id) ?>-clr">
                                  <label class="form-check-label text-muted" for="<?= $h($id) ?>-clr">Clear saved value</label>
                                </div>
                              <?php endif; ?>
                            <?php elseif ($isTa): ?>
                              <textarea class="form-control form-control-sm gw-key" id="<?= $h($id) ?>" name="cred[<?= $m ?>][<?= $h($k) ?>]" spellcheck="false"><?= $h($st['value']) ?></textarea>
                            <?php else: ?>
                              <input type="text" class="form-control form-control-sm" id="<?= $h($id) ?>" name="cred[<?= $m ?>][<?= $h($k) ?>]" value="<?= $h($st['value']) ?>" spellcheck="false">
                            <?php endif; ?>
                            <?php if (!empty($f['help'])): ?><small class="text-muted"><?= $h($f['help']) ?></small><?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                    <?php endforeach; ?>
                  </div>

                  <?php if ($def['extra']): ?>
                  <h5 class="mt-2">Endpoint &amp; integration settings</h5>
                  <div class="row g-2 mb-3">
                    <?php foreach ($def['extra'] as $k => $f):
                        $val = isset($g['extra'][$k]) ? $g['extra'][$k] : (isset($f['default']) ? $f['default'] : ''); ?>
                      <div class="col-md-6">
                        <label class="form-label small mb-1"><?= $h($f['label']) ?></label>
                        <input type="text" class="form-control form-control-sm font-monospace" name="extra[<?= $h($k) ?>]" value="<?= $h($val) ?>" placeholder="<?= $h(isset($f['default']) ? $f['default'] : '') ?>">
                        <?php if (!empty($f['help'])): ?><small class="text-muted"><?= $h($f['help']) ?></small><?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <?php endif; ?>

                  <h5 class="mt-2">Limits &amp; pricing</h5>
                  <div class="row g-2 mb-3">
                    <div class="col-sm-3">
                      <label class="form-label small mb-1">Charge %</label>
                      <input type="number" step="0.01" min="0" max="20" class="form-control form-control-sm" name="charge_percentage" value="<?= $h($r['charge_percentage']) ?>">
                      <small class="text-muted">Added on top, paid by customer</small>
                    </div>
                    <div class="col-sm-3">
                      <label class="form-label small mb-1">Min (<?= $h($def['currency']) ?>)</label>
                      <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" name="min_amount" value="<?= $h($r['min_amount']) ?>">
                    </div>
                    <div class="col-sm-3">
                      <label class="form-label small mb-1">Max (<?= $h($def['currency']) ?>)</label>
                      <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" name="max_amount" value="<?= $h($r['max_amount']) ?>">
                    </div>
                    <?php if ($slug === 'stripe'): ?>
                    <div class="col-sm-3">
                      <label class="form-label small mb-1">1 USD = ? BDT</label>
                      <input type="number" step="0.0001" min="1" max="1000" class="form-control form-control-sm" name="exchange_rate" value="<?= $h($r['exchange_rate']) ?>">
                      <small class="text-muted">Locked per invoice at creation</small>
                    </div>
                    <?php endif; ?>
                  </div>

                  <div class="row g-2 align-items-end border-top pt-3">
                    <div class="col-sm-4">
                      <label class="form-label small mb-1" for="pin-<?= $h($slug) ?>">Super Admin Self-PIN <span class="text-danger">*</span></label>
                      <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="8" class="form-control" id="pin-<?= $h($slug) ?>" name="self_pin" required autocomplete="off">
                    </div>
                    <div class="col-sm-8 d-flex flex-wrap gap-2 align-items-center">
                      <button type="submit" class="btn btn-success"><i class="mdi mdi-content-save"></i> Save <?= $h($def['name']) ?></button>
                      <small class="text-muted">Last updated <?= $h($r['updated_at']) ?> by <?= $h($r['updated_by'] ?: '-') ?></small>
                    </div>
                  </div>
                </form>

                <?php if ($g['audit']): ?>
                <details class="mt-3">
                  <summary class="text-muted small">Recent changes</summary>
                  <table class="table table-sm small mt-2 mb-0">
                    <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>Fields changed</th><th>IP</th></tr></thead>
                    <tbody>
                    <?php foreach ($g['audit'] as $a): $ch = json_decode((string) $a['changes'], true) ?: array(); ?>
                      <tr><td class="text-nowrap"><?= $h($a['created_at']) ?></td><td><?= $h($a['admin_user']) ?></td><td><?= $h($a['action']) ?></td>
                          <td><?= $h($ch ? implode(', ', $ch) : '-') ?></td><td><?= $h($a['ip']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                  </table>
                </details>
                <?php endif; ?>

              </div></div>
            </div>
          <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
<script>
(function () {
  document.querySelectorAll('.gw-copy').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = b.getAttribute('data-copy');
      var done = function () { b.innerHTML = '<i class="mdi mdi-check"></i>'; setTimeout(function () { b.innerHTML = '<i class="mdi mdi-content-copy"></i>'; }, 1500); };
      if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(t).then(done); }
      else { var i = b.parentNode.querySelector('input'); i.select(); document.execCommand('copy'); done(); }
    });
  });
  document.querySelectorAll('.gw-mode').forEach(function (r) {
    r.addEventListener('change', function () {
      var slug = r.getAttribute('data-slug');
      document.querySelectorAll('[data-mode-card^="' + slug + ':"]').forEach(function (c) {
        c.classList.toggle('is-active', c.getAttribute('data-mode-card') === slug + ':' + r.value);
      });
      var n = document.querySelector('.gw-live-note[data-slug="' + slug + '"]');
      if (n) { n.style.display = r.value === 'live' ? '' : 'none'; }
    });
  });
})();
</script>
<?php
include "footer-data.php";
?>
