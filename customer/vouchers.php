<?php
include "login.check.php";
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Db;
use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayException;
use Midhub\Payment\DepositService;
use Midhub\Security\Csrf;
use Midhub\Security\PinVerifier;
use Midhub\Voucher\PopularVoucherService;
use Midhub\Voucher\VoucherService;

$userId = (int) $us_id;

/** BDT deposit gateways a voucher can be paid with directly (USD/Stripe excluded on purpose). */
$payGateways = array();
foreach (GatewayConfig::activeDepositGateways() as $g) {
    if ($g->definition['currency'] === 'BDT') {
        $payGateways[$g->slug] = $g;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::requireValidPost();
    $productId = (int) (isset($_POST['product_id']) ? $_POST['product_id'] : 0);
    $playerId = trim((string) (isset($_POST['player_id']) ? $_POST['player_id'] : ''));
    $nonce = (string) (isset($_POST['nonce']) ? $_POST['nonce'] : '');
    $method = (string) (isset($_POST['pay_method']) ? $_POST['pay_method'] : 'wallet');
    try {
        if ($method === 'wallet') {
            $pin = PinVerifier::verify($userId, isset($_POST['pincode']) ? trim((string) $_POST['pincode']) : '');
            if (!$pin['ok']) {
                $_SESSION['vo_flash'] = array('error', $pin['error']);
            } else {
                $r = VoucherService::purchase($userId, $productId, $nonce, $playerId);
                $_SESSION['vo_flash'] = array($r['ok'] ? 'success' : 'error', $r['message']);
            }
        } elseif (isset($payGateways[$method])) {
            // Pay with bKash / Nagad / ...: the order is placed automatically once the payment is confirmed.
            $chk = VoucherService::precheck($productId, $playerId);
            if (!$chk['ok']) {
                $_SESSION['vo_flash'] = array('error', $chk['message']);
            } else {
                $res = DepositService::create($userId, $method, 'balance', $chk['price'], $nonce,
                    array('type' => 'voucher', 'product_id' => $productId, 'player_id' => $playerId));
                header('Location: ' . $res['redirect_url']);
                exit();
            }
        } else {
            $_SESSION['vo_flash'] = array('error', 'Choose a payment method.');
        }
    } catch (\InvalidArgumentException $e) {
        $_SESSION['vo_flash'] = array('error', $e->getMessage());
    } catch (GatewayException $e) {
        $_SESSION['vo_flash'] = array('error', $e->userMessage);
    } catch (\Throwable $e) {
        error_log('voucher purchase: ' . $e->getMessage());
        $_SESSION['vo_flash'] = array('error', 'The purchase could not be completed. If your balance was deducted, it will be refunded or delivered automatically.');
    }
    header('Location: vouchers.php');
    exit();
}

include "header.php";

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$flash = isset($_SESSION['vo_flash']) ? $_SESSION['vo_flash'] : null;
unset($_SESSION['vo_flash']);
$svc = PopularVoucherService::load();
$open = $svc->config()->isActive();
$catFilter = isset($_GET['category']) ? substr(trim((string) $_GET['category']), 0, 60) : '';
$q = isset($_GET['q']) ? substr(trim((string) $_GET['q']), 0, 60) : '';
$pageNo = max(1, isset($_GET['p']) ? (int) $_GET['p'] : 1);
$perPage = 48;
$store = $open ? VoucherService::storefront(array('category' => $catFilter, 'q' => $q), $perPage, ($pageNo - 1) * $perPage) : array('items' => array(), 'total' => 0, 'categories' => array());
$products = $store['items'];
$pages = max(1, (int) ceil($store['total'] / $perPage));
$byCat = array();
foreach ($products as $p) {
    $byCat[$p['category']][] = $p;
}
$catLabel = function ($c) {
    $map = array('gift_card' => 'Gift Cards & Games', 'mobile_recharge' => 'International Mobile Recharge', 'data' => 'Data Bundles', 'bill_pay' => 'International Bill Pay');
    return isset($map[$c]) ? $map[$c] : ucwords(str_replace(array('_', '-'), ' ', (string) $c));
};
$storeUrl = function (array $over) use ($catFilter, $q) {
    $qs = array_filter(array_merge(array('category' => $catFilter, 'q' => $q), $over), function ($v) {
        return $v !== '' && $v !== null && $v !== 1;
    });
    return 'vouchers.php' . ($qs ? '?' . http_build_query($qs) : '');
};
$vault = VoucherService::vault($userId);
$mainBal = (string) Db::scalar('SELECT balance FROM user WHERE id = ?', 'i', array($userId));
$nonce = bin2hex(random_bytes(16));
$st = array('delivered' => 'success', 'processing' => 'warning', 'refunded' => 'secondary', 'failed' => 'danger');
?>
<style>
  .vo-card { border:1px solid rgba(128,128,128,.3); border-radius:.5rem; padding:.9rem; height:100%; display:flex; flex-direction:column; justify-content:space-between; }
  .vo-price { font-size:1.15rem; font-weight:600; font-variant-numeric: tabular-nums; }
  .vo-thumb { width:40px; height:40px; object-fit:contain; flex:none; border-radius:.35rem; background:rgba(128,128,128,.08); }
  .vo-name { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; overflow-wrap:anywhere; }
  .min-w-0 { min-width:0; }
  .vo-chips { display:flex; gap:.4rem; overflow-x:auto; padding-bottom:.25rem; -webkit-overflow-scrolling:touch; }
  .vo-chips .btn { white-space:nowrap; flex:none; }
  .vo-card .btn { min-height:36px; }
  .vo-code { font-family: monospace; letter-spacing:.04em; }
  .vo-mask { filter: blur(5px); cursor:pointer; user-select:none; }
  .vo-pay label { display:block; border:1px solid rgba(128,128,128,.35); border-radius:.4rem; padding:.5rem .75rem; cursor:pointer; margin-bottom:.4rem; }
  .vo-pay input:checked + label { border-color:#1abc9c; box-shadow:0 0 0 2px rgba(26,188,156,.3); }
  .vo-pay input { position:absolute; opacity:0; }
  .vo-pay input:focus-visible + label { outline:2px solid #1abc9c; }
</style>
<div class="content-page"><div class="content"><div class="container-fluid">
  <div class="row"><div class="col-12"><div class="page-title-box">
    <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="home.php">Home</a></li><li class="breadcrumb-item active">Digital Vouchers</li></ol></div>
    <h4 class="page-title">Digital Vouchers &amp; Game Top-up</h4>
  </div>
  <?php if ($flash): ?><div class="alert alert-<?= $flash[0] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show"><?= $h($flash[1]) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  <?php if ($open && !$svc->config()->isLive()): ?><div class="alert alert-info py-2">Test mode: vouchers delivered here are <b>TEST codes</b> and cannot be redeemed.</div><?php endif; ?>
  </div></div>

  <div class="card"><div class="card-body">
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
      <h5 class="m-0">Store</h5><span class="text-muted">Main wallet: <b><?= $h($mainBal) ?> Tk</b></span>
    </div>
    <?php if (!$open): ?>
      <p class="text-muted mb-0">The voucher store is closed right now.</p>
    <?php else: ?>
      <form method="get" class="vo-search mb-2" role="search">
        <?php if ($catFilter !== ''): ?><input type="hidden" name="category" value="<?= $h($catFilter) ?>"><?php endif; ?>
        <div class="input-group">
          <input type="search" name="q" value="<?= $h($q) ?>" class="form-control" placeholder="Search: Free Fire, PUBG, Google Play, Netflix..." aria-label="Search vouchers" enterkeyhint="search">
          <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i><span class="visually-hidden">Search</span></button>
        </div>
      </form>
      <?php if (count($store['categories']) > 1): ?>
      <div class="vo-chips mb-2">
        <a href="<?= $h($storeUrl(array('category' => '', 'p' => ''))) ?>" class="btn btn-sm <?= $catFilter === '' ? 'btn-dark' : 'btn-outline-secondary' ?>">All</a>
        <?php foreach ($store['categories'] as $cat): ?>
          <a href="<?= $h($storeUrl(array('category' => $cat['category'], 'p' => ''))) ?>" class="btn btn-sm <?= $catFilter === $cat['category'] ? 'btn-dark' : 'btn-outline-secondary' ?>"><?= $h($catLabel($cat['category'])) ?> <span class="opacity-75">(<?= (int) $cat['n'] ?>)</span></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if (!$products): ?><p class="text-muted mb-0"><?= ($q !== '' || $catFilter !== '') ? 'Nothing matches your search.' : 'No vouchers available at the moment.' ?></p><?php endif; ?>
    <?php endif; ?>
    <?php foreach ($byCat as $cat => $items): ?>
      <h6 class="text-uppercase text-muted mt-3"><?= $h($catLabel($cat)) ?></h6>
      <div class="row g-2">
        <?php foreach ($items as $p):
            $direct = $p['delivery_type'] === 'player_id';
            $schema = $p['delivery_schema'] ? json_decode((string) $p['delivery_schema'], true) : array();
            $f0 = is_array($schema) && isset($schema[0]) ? $schema[0] : array();
            $idType = isset($f0['type']) && $f0['type'] === 'tel' ? 'tel' : (isset($f0['type']) && $f0['type'] === 'number' ? 'number' : (($f0 === array() && $direct) ? 'number' : 'text'));
            $idMax = isset($f0['max_length']) ? (int) $f0['max_length'] : 64;
        ?>
          <div class="col-6 col-md-4 col-xl-3">
            <div class="vo-card">
              <div class="d-flex gap-2">
                <?php if (!empty($p['thumbnail_url'])): ?><img src="<?= $h($p['thumbnail_url']) ?>" alt="" class="vo-thumb" loading="lazy" decoding="async" referrerpolicy="no-referrer"><?php endif; ?>
                <div class="min-w-0"><b class="vo-name"><?= $h($p['name']) ?></b><small class="text-muted d-block"><?= $h(trim(($p['country_name'] ? $p['country_name'] . ' · ' : '') . $p['face_value'])) ?></small>
                  <small class="text-muted"><?= $direct ? 'Instant to your ' . $h($p['player_id_label']) : 'Code / PIN, kept in My Vouchers' ?></small></div>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="vo-price"><?= $h($p['sell_price']) ?> Tk</span>
                <button type="button" class="btn btn-sm btn-primary vo-buy" data-id="<?= (int) $p['id'] ?>" data-name="<?= $h($p['name']) ?>" data-price="<?= $h($p['sell_price']) ?>"
                        data-direct="<?= $direct ? '1' : '0' ?>" data-idlabel="<?= $h($p['player_id_label']) ?>" data-idtype="<?= $h($idType) ?>" data-idmax="<?= $idMax ?>" data-bs-toggle="modal" data-bs-target="#vo-modal">Buy</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <?php if ($pages > 1): ?>
      <nav class="mt-3" aria-label="Store pages"><ul class="pagination pagination-sm justify-content-center flex-wrap m-0">
        <li class="page-item <?= $pageNo <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $h($storeUrl(array('p' => $pageNo - 1))) ?>">&lsaquo; Prev</a></li>
        <li class="page-item disabled"><span class="page-link"><?= $pageNo ?> / <?= $pages ?></span></li>
        <li class="page-item <?= $pageNo >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= $h($storeUrl(array('p' => $pageNo + 1))) ?>">Next &rsaquo;</a></li>
      </ul></nav>
    <?php endif; ?>
  </div></div>
  <div class="card"><div class="card-body">
    <h5 class="mt-0">My Vouchers &amp; Top-ups</h5>
    <div class="table-responsive">
    <table class="table table-sm small align-middle mb-0">
      <thead><tr><th>Order</th><th>Product</th><th class="text-end">Price</th><th>Status</th><th>Code / PIN / Delivery</th></tr></thead>
      <tbody>
      <?php foreach ($vault as $o): ?>
        <tr>
          <td class="font-monospace"><?= $h($o['order_no']) ?><br><small class="text-muted"><?= $h($o['created_at']) ?></small></td>
          <td><?= $h($o['product_name']) ?><?= $o['mode'] === 'sandbox' ? ' <span class="badge bg-info">TEST</span>' : '' ?></td>
          <td class="text-end"><?= $h($o['price']) ?></td>
          <td><span class="badge bg-<?= $h(isset($st[$o['status']]) ? $st[$o['status']] : 'secondary') ?>"><?= $h($o['status']) ?></span></td>
          <td>
            <?php if ($o['status'] === 'delivered'): ?>
              <?php if (!empty($o['player_id'])): ?><div>Delivered to Player ID <b class="vo-code"><?= $h($o['player_id']) ?></b></div><?php endif; ?>
              <?php if ($o['voucher_code']): ?><div>Code: <span class="vo-code vo-mask" title="Click to reveal"><?= $h($o['voucher_code']) ?></span></div><?php endif; ?>
              <?php if ($o['voucher_pin']): ?><div>PIN: <span class="vo-code vo-mask" title="Click to reveal"><?= $h($o['voucher_pin']) ?></span></div><?php endif; ?>
              <?php if ($o['voucher_serial']): ?><div class="text-muted">Serial: <span class="vo-code"><?= $h($o['voucher_serial']) ?></span></div><?php endif; ?>
              <?php if ($o['expires_on']): ?><div class="text-muted">Valid till <?= $h($o['expires_on']) ?></div><?php endif; ?>
            <?php elseif ($o['status'] === 'refunded'): ?>
              <span class="text-muted">Refunded to Main wallet<?= $o['failure_reason'] ? ' (' . $h($o['failure_reason']) . ')' : '' ?></span>
            <?php else: ?>
              <span class="text-muted">Processing...</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$vault): ?><tr><td colspan="5" class="text-center text-muted">No vouchers purchased yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div></div>
</div></div>

<div class="modal fade" id="vo-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form method="post" autocomplete="off" id="vo-form">
      <?= Csrf::field() ?>
      <input type="hidden" name="nonce" value="<?= $h($nonce) ?>">
      <input type="hidden" name="product_id" id="vo-pid" value="">
      <div class="modal-header"><h5 class="modal-title">Confirm purchase</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="mb-2"><b id="vo-name"></b> &middot; <b id="vo-price"></b> Tk</p>
        <div class="mb-3" id="vo-pidwrap" style="display:none">
          <label class="form-label" for="vo-playerid" id="vo-idlabel">Player ID</label>
          <input type="text" inputmode="numeric" maxlength="128" class="form-control" name="player_id" id="vo-playerid" autocomplete="off" spellcheck="false">
          <small class="text-muted">Double-check it: the top-up is sent straight to this ID / number. If the provider rejects it, you are refunded automatically.</small>
        </div>
        <label class="form-label">Pay with</label>
        <div class="vo-pay">
          <input type="radio" name="pay_method" id="pm-wallet" value="wallet" checked><label for="pm-wallet"><b>Main wallet</b> <small class="text-muted">(<?= $h($mainBal) ?> Tk, PIN required)</small></label>
          <?php foreach ($payGateways as $slug => $g): ?>
            <input type="radio" name="pay_method" id="pm-<?= $h($slug) ?>" value="<?= $h($slug) ?>"><label for="pm-<?= $h($slug) ?>"><i class="mdi <?= $h($g->definition['icon']) ?>"></i> <b><?= $h($g->definition['name']) ?></b><?= (float) $g->chargePercentage > 0 ? ' <small class="text-muted">+' . $h($g->chargePercentage) . '% charge</small>' : '' ?><?= !$g->isLive() ? ' <span class="badge bg-info">TEST</span>' : '' ?></label>
          <?php endforeach; ?>
        </div>
        <div class="mt-2" id="vo-pinwrap">
          <label class="form-label" for="vo-pin">Your PIN</label>
          <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="8" class="form-control" name="pincode" id="vo-pin">
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success" id="vo-submit">Buy now</button></div>
    </form>
  </div></div>
</div>
<script>
(function () {
  var pidWrap = document.getElementById('vo-pidwrap'), pidIn = document.getElementById('vo-playerid');
  var pinWrap = document.getElementById('vo-pinwrap'), pinIn = document.getElementById('vo-pin');
  function syncPay() {
    var m = document.querySelector('input[name=pay_method]:checked');
    var wallet = !m || m.value === 'wallet';
    pinWrap.style.display = wallet ? '' : 'none'; pinIn.required = wallet;
    document.getElementById('vo-submit').textContent = wallet ? 'Buy now' : 'Continue to payment';
  }
  document.querySelectorAll('input[name=pay_method]').forEach(function (r) { r.addEventListener('change', syncPay); });
  document.querySelectorAll('.vo-buy').forEach(function (b) {
    b.addEventListener('click', function () {
      document.getElementById('vo-pid').value = b.dataset.id;
      document.getElementById('vo-name').textContent = b.dataset.name;
      document.getElementById('vo-price').textContent = b.dataset.price;
      var direct = b.dataset.direct === '1';
      pidWrap.style.display = direct ? '' : 'none'; pidIn.required = direct; pidIn.value = '';
      var t = b.dataset.idtype || 'text';
      pidIn.type = t === 'tel' ? 'tel' : 'text';
      pidIn.inputMode = t === 'tel' ? 'tel' : (t === 'number' ? 'numeric' : 'text');
      pidIn.maxLength = parseInt(b.dataset.idmax || '64', 10);
      pidIn.autocapitalize = 'off';
      document.getElementById('vo-idlabel').textContent = b.dataset.idlabel || 'Player ID';
      syncPay();
    });
  });
  document.querySelectorAll('.vo-mask').forEach(function (s) { s.addEventListener('click', function () { s.classList.remove('vo-mask'); }); });
  var f = document.getElementById('vo-form');
  if (f) f.addEventListener('submit', function () { document.getElementById('vo-submit').disabled = true; });
})();
</script>
<?php include "footer-data.php"; ?>
