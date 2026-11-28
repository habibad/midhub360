<?php
include "login.check.php";
require_once dirname(__DIR__) . '/lib/bootstrap.php';
include "header.php";

use Midhub\Admin\VoucherAdminController;
use Midhub\Db;
use Midhub\Ops\Settings;
use Midhub\Security\Csrf;
use Midhub\Voucher\PopularVoucherService;
use Midhub\Voucher\VoucherService;

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$flash = isset($_SESSION['gw_flash']) ? $_SESSION['gw_flash'] : null;
unset($_SESSION['gw_flash']);
$svc = PopularVoucherService::load();
$cfg = $svc->config();
$envIssue = $svc->isMock() ? '' : $svc->checkEnvironment();
$providerEnv = Settings::get('voucher_provider_env:' . $cfg->mode, '');

$filters = VoucherAdminController::filterParams($_GET);
list($where, $types, $params) = VoucherAdminController::filterSql($filters);
$total = (int) Db::scalar("SELECT COUNT(*) FROM voucher_products WHERE $where", $types, $params);
$perPage = VoucherAdminController::PER_PAGE;
$pages = max(1, (int) ceil($total / $perPage));
$page = max(1, min($pages, isset($_GET['page']) ? (int) $_GET['page'] : 1));
$products = Db::all("SELECT * FROM voucher_products WHERE $where ORDER BY active DESC, category, brand, cost_price LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $types, $params);

$categories = Db::all("SELECT category, COUNT(*) AS cnt, SUM(active = 1) AS active_cnt FROM voucher_products WHERE provider = 'popular_voucher' GROUP BY category ORDER BY cnt DESC");
$countries = Db::all("SELECT country_code, MAX(country_name) AS country_name, COUNT(*) AS cnt FROM voucher_products WHERE provider = 'popular_voucher' AND country_code IS NOT NULL GROUP BY country_code ORDER BY country_name");
$stats = Db::one("SELECT COUNT(*) AS total, SUM(" . VoucherService::SELLABLE_SQL . ") AS sellable, SUM(active = 1 AND sell_price < cost_price) AS below FROM voucher_products WHERE provider = 'popular_voucher'");
$orders = Db::all("SELECT o.*, u.user AS username FROM voucher_orders o LEFT JOIN user u ON u.id = o.user_id ORDER BY o.id DESC LIMIT 50");
$progress = VoucherService::syncProgress();
$last = json_decode(Settings::get(VoucherService::SYNC_LAST, ''), true);
$link = function (array $over) use ($filters) {
    $q = array_filter(array_merge($filters, $over), function ($v) {
        return $v !== '' && $v !== null;
    });
    return 'voucher_products.php' . ($q ? '?' . http_build_query($q) : '');
};
$hidden = function () use ($filters, $page, $h) {
    $o = '';
    foreach ($filters as $k => $v) {
        $o .= '<input type="hidden" name="' . $h($k) . '" value="' . $h($v) . '">';
    }
    return $o . '<input type="hidden" name="page" value="' . (int) $page . '">';
};
$label = function ($cat) {
    return ucwords(str_replace(array('_', '-'), ' ', (string) $cat));
};
?>
<div class="content-page"><div class="content"><div class="container-fluid">
  <div class="row"><div class="col-12"><div class="page-title-box">
    <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="home.php">Home</a></li><li class="breadcrumb-item active">Digital Vouchers</li></ol></div>
    <h4 class="page-title">Digital Vouchers (Popular Vouchers)</h4>
  </div>
  <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show"><?= $flash['html'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  <?php if ($envIssue !== ''): ?><div class="alert alert-danger"><b>Orders blocked:</b> <?= $h($envIssue) ?></div><?php endif; ?>
  <div class="alert alert-<?= $cfg->isActive() ? ($cfg->isLive() ? 'danger' : 'info') : 'secondary' ?> py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      Store is <b><?= $cfg->isActive() ? 'OPEN' : 'CLOSED' ?></b>, mode <b><?= $h(strtoupper($cfg->mode)) ?></b><?= $svc->isMock() ? ' (MOCK: TEST codes, no network)' : '' ?><?= $providerEnv !== '' ? ', provider account environment <b>' . $h(strtoupper($providerEnv)) . '</b>' : '' ?>.
      &bull; <b><?= (int) $stats['total'] ?></b> products synced, <b><?= (int) $stats['sellable'] ?></b> on sale<?= (int) $stats['below'] > 0 ? ', <a class="alert-link text-danger" href="voucher_products.php?state=below_cost">' . (int) $stats['below'] . ' active but below cost (hidden)</a>' : '' ?>.
      <a href="gateway_settings.php?tab=popular_voucher" class="alert-link ms-1">Gateway settings</a>
    </div>
    <a href="../customer/vouchers.php" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark"><i class="mdi mdi-eye"></i> Customer storefront</a>
  </div>
  </div></div>

  <div class="card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
      <div>
        <h5 class="m-0">Catalog &amp; pricing</h5>
        <small class="text-muted">
          <?php if ($progress): ?>
            <b class="text-warning">Sync in progress:</b> <?= (int) $progress['fetched'] ?> of <?= $h($progress['total'] !== null ? $progress['total'] : '?') ?> read. It continues every minute (cron), or click Sync again.
          <?php elseif (is_array($last)): ?>
            Last full sync <?= $h($last['at']) ?>: <?= (int) $last['total'] ?> listed, <?= (int) $last['new'] ?> new, <?= (int) $last['delisted'] ?> delisted<?= !empty($last['skipped']) ? ', ' . (int) $last['skipped'] . ' skipped (unsupported)' : '' ?>.
          <?php else: ?>New products arrive inactive. Apply a markup to a filtered set, then activate it.<?php endif; ?>
        </small>
      </div>
      <form method="post" action="route.php?action=VoucherSync" class="m-0"><?= Csrf::field() ?><button class="btn btn-sm btn-outline-primary"><i class="mdi mdi-sync"></i> <?= $progress ? 'Continue sync' : 'Sync from provider' ?></button></form>
    </div>

    <form method="get" class="row g-2 align-items-end mb-3">
      <div class="col-6 col-md-2"><label class="form-label small mb-0">Category</label>
        <select name="category" class="form-select form-select-sm"><option value="">All</option>
          <?php foreach ($categories as $c): ?><option value="<?= $h($c['category']) ?>" <?= (isset($filters['category']) && $filters['category'] === $c['category']) ? 'selected' : '' ?>><?= $h($label($c['category'])) ?> (<?= (int) $c['cnt'] ?><?= $c['active_cnt'] > 0 ? ', ' . (int) $c['active_cnt'] . ' on' : '' ?>)</option><?php endforeach; ?>
        </select></div>
      <div class="col-6 col-md-2"><label class="form-label small mb-0">Country</label>
        <select name="country" class="form-select form-select-sm"><option value="">All</option>
          <?php foreach ($countries as $c): ?><option value="<?= $h($c['country_code']) ?>" <?= (isset($filters['country']) && $filters['country'] === $c['country_code']) ? 'selected' : '' ?>><?= $h($c['country_name'] ?: strtoupper($c['country_code'])) ?> (<?= (int) $c['cnt'] ?>)</option><?php endforeach; ?>
        </select></div>
      <div class="col-6 col-md-2"><label class="form-label small mb-0">Status</label>
        <select name="state" class="form-select form-select-sm">
          <?php foreach (array('' => 'Any', 'active' => 'Active', 'inactive' => 'Inactive', 'below_cost' => 'Below cost', 'out_of_stock' => 'Out of stock / delisted', 'markup' => 'Auto-priced (markup)') as $k => $v): ?>
            <option value="<?= $h($k) ?>" <?= (isset($filters['state']) ? $filters['state'] : '') === $k ? 'selected' : '' ?>><?= $h($v) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-6 col-md-2"><label class="form-label small mb-0">Delivery</label>
        <select name="delivery" class="form-select form-select-sm">
          <?php foreach (array('' => 'Any', 'code' => 'Code / PIN', 'player_id' => 'Direct (ID / number)') as $k => $v): ?>
            <option value="<?= $h($k) ?>" <?= (isset($filters['delivery']) ? $filters['delivery'] : '') === $k ? 'selected' : '' ?>><?= $h($v) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-8 col-md-3"><label class="form-label small mb-0">Search</label><input type="search" name="q" value="<?= $h(isset($filters['q']) ? $filters['q'] : '') ?>" class="form-control form-control-sm" placeholder="Free Fire, PUBG, Google Play, Netflix..."></div>
      <div class="col-4 col-md-1 d-flex gap-1"><button class="btn btn-sm btn-primary w-100">Filter</button><?php if ($filters): ?><a href="voucher_products.php" class="btn btn-sm btn-outline-secondary" title="Reset">&times;</a><?php endif; ?></div>
    </form>

    <form method="post" action="route.php?action=VoucherBulk" class="border rounded p-2 mb-3" onsubmit="return confirm('Apply to all <?= (int) $total ?> product(s) matching the filter?');">
      <?= Csrf::field() ?><?= $hidden() ?>
      <div class="row g-2 align-items-end">
        <div class="col-12 col-md-3"><small class="text-muted">Bulk, for all <b><?= (int) $total ?></b> product(s) matching the filter:</small></div>
        <div class="col-6 col-md-2"><select name="bulk_action" class="form-select form-select-sm" required>
          <option value="">Action...</option><option value="markup">Set markup %</option><option value="activate">Activate</option><option value="deactivate">Deactivate</option></select></div>
        <div class="col-6 col-md-2"><div class="input-group input-group-sm"><input type="number" name="markup_pct" step="0.01" min="0" max="500" class="form-control" placeholder="e.g. 5"><span class="input-group-text">%</span></div></div>
        <div class="col-6 col-md-2"><input type="password" inputmode="numeric" name="self_pin" class="form-control form-control-sm" placeholder="Self-PIN" required autocomplete="off"></div>
        <div class="col-6 col-md-2"><button class="btn btn-sm btn-warning w-100">Apply</button></div>
      </div>
    </form>

    <form method="post" action="route.php?action=VoucherProductSave">
      <?= Csrf::field() ?><?= $hidden() ?>
      <div class="table-responsive">
      <table class="table table-sm small align-middle">
        <thead>
          <tr>
            <th style="width:34px"><input type="checkbox" class="form-check-input" id="checkAllVouchers" title="Select all on this page"></th>
            <th>Product</th>
            <th>Category</th>
            <th>Delivery</th>
            <th class="text-end">Cost</th>
            <th style="width:120px">Sell (Tk)</th>
            <th class="text-end">Margin</th>
            <th>State</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): $margin = (float) $p['sell_price'] - (float) $p['cost_price']; $foreign = $p['currency'] !== null && $p['currency'] !== '' && $p['currency'] !== 'BDT'; ?>
          <tr class="<?= $p['active'] ? 'table-success' : '' ?>">
            <td><input type="hidden" name="ids[]" value="<?= (int) $p['id'] ?>"><input type="checkbox" class="form-check-input voucher-check" name="active[<?= (int) $p['id'] ?>]" value="1" <?= $p['active'] ? 'checked' : '' ?> aria-label="Active"></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?php if ($p['thumbnail_url']): ?><img src="<?= $h($p['thumbnail_url']) ?>" alt="" width="28" height="28" loading="lazy" referrerpolicy="no-referrer" style="object-fit:contain"><?php endif; ?>
                <div><?= $h($p['name']) ?><br><small class="text-muted"><?= $h(trim(($p['country_name'] ? $p['country_name'] . ' · ' : '') . $p['face_value'])) ?> · <span class="font-monospace"><?= $h($p['product_code']) ?></span></small></div>
              </div>
            </td>
            <td><?= $h($label($p['category'])) ?></td>
            <td><?= $p['delivery_type'] === 'player_id' ? '<span class="badge bg-primary">' . $h($p['player_id_label']) . '</span>' : '<span class="badge bg-secondary">code / PIN</span>' ?></td>
            <td class="text-end"><?= $h($p['cost_price']) ?><?= $foreign ? ' <span class="badge bg-danger" title="Not BDT: cannot be sold">' . $h($p['currency']) . '</span>' : '' ?></td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="sell_price[<?= (int) $p['id'] ?>]" value="<?= $h($p['sell_price']) ?>" aria-label="Sell price"><?= $p['markup_pct'] !== null ? '<small class="text-muted">auto +' . $h(rtrim(rtrim($p['markup_pct'], '0'), '.')) . '%</small>' : '' ?></td>
            <td class="text-end <?= $margin < 0 ? 'text-danger fw-bold' : 'text-success' ?>"><?= $h(number_format($margin, 2)) ?></td>
            <td class="text-nowrap"><?= $p['active'] ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-light text-dark">off</span>' ?> <?= $p['in_stock'] ? '' : '<span class="badge bg-secondary">out</span>' ?><?= $p['active'] && $margin < 0 ? ' <span class="badge bg-danger">hidden: below cost</span>' : '' ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="8" class="text-center text-muted">No products match. <?= $stats['total'] ? 'Change the filter.' : 'Click "Sync from provider".' ?></td></tr><?php endif; ?>
        </tbody>
      </table>
      </div>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <?php if ($products): ?>
        <div class="d-flex gap-2 align-items-center">
          <input type="password" inputmode="numeric" class="form-control form-control-sm" style="max-width:140px" name="self_pin" placeholder="Self-PIN" required autocomplete="off">
          <button class="btn btn-success btn-sm text-nowrap"><i class="mdi mdi-content-save"></i> Save this page</button>
        </div>
        <?php endif; ?>
        <nav aria-label="Pages"><ul class="pagination pagination-sm m-0">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $h($link(array('page' => $page - 1))) ?>">&lsaquo;</a></li>
          <li class="page-item disabled"><span class="page-link">Page <?= $page ?> / <?= $pages ?> (<?= (int) $total ?>)</span></li>
          <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= $h($link(array('page' => $page + 1))) ?>">&rsaquo;</a></li>
        </ul></nav>
      </div>
    </form>
  </div></div>

  <div class="card"><div class="card-body">
    <h5>Recent orders</h5>
    <div class="table-responsive">
    <table class="table table-sm small">
      <thead><tr><th>Order</th><th>User</th><th>Product</th><th class="text-end">Price</th><th>Mode</th><th>Status</th><th>Created</th></tr></thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr><td class="font-monospace"><?= $h($o['order_no']) ?></td><td><?= $h($o['username']) ?></td><td><?= $h($o['product_name']) ?><?= $o['player_id'] ? '<br><small class="text-muted">to ' . $h($o['player_id']) . '</small>' : '' ?></td>
            <td class="text-end"><?= $h($o['price']) ?></td><td><?= $h($o['mode']) ?></td>
            <td><?= $h($o['status']) ?><?= $o['failure_reason'] ? '<br><small class="text-muted">' . $h($o['failure_reason']) . '</small>' : '' ?><?= $o['provider_order_id'] ? '<br><small class="text-muted font-monospace">' . $h($o['provider_order_id']) . '</small>' : '' ?></td><td class="text-nowrap"><?= $h($o['created_at']) ?></td></tr>
        <?php if ($o['status'] === 'processing'): ?>
        <tr><td colspan="7" class="border-0 pt-0">
          <form method="post" action="route.php?action=VoucherOrderAction" class="row g-1 align-items-center" onsubmit="var a=this.order_action.value; return a!=='refund' || confirm('Refund only after the distributor portal shows this transaction FAILED. Refund <?= $h($o['price']) ?> Tk to the customer?');">
            <?= Csrf::field() ?><input type="hidden" name="order_no" value="<?= $h($o['order_no']) ?>">
            <div class="col-auto"><select name="order_action" class="form-select form-select-sm" required>
              <option value="check">Check with provider now</option><option value="deliver">Deliver manually (code from portal)</option><option value="refund">Refund to Main wallet</option></select></div>
            <div class="col-auto"><input name="code" class="form-control form-control-sm" placeholder="Code (deliver)" autocomplete="off" maxlength="200"></div>
            <div class="col-auto"><input name="pin" class="form-control form-control-sm" placeholder="PIN (optional)" autocomplete="off" maxlength="100"></div>
            <div class="col-auto"><input type="password" inputmode="numeric" name="self_pin" class="form-control form-control-sm" placeholder="Self-PIN" required autocomplete="off"></div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Go</button></div>
          </form>
        </td></tr>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if (!$orders): ?><tr><td colspan="7" class="text-center text-muted">No orders yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
    <small class="text-muted">Voucher codes are stored encrypted and are visible only to the customer who bought them.</small>
  </div></div>
</div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var master = document.getElementById('checkAllVouchers');
  if (master) {
    master.addEventListener('change', function () {
      var checks = document.querySelectorAll('.voucher-check');
      for (var i = 0; i < checks.length; i++) { checks[i].checked = master.checked; }
    });
  }
});
</script>
<?php include "footer-data.php"; ?>
