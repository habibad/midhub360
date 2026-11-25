<?php
include "login.check.php";
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Db;
use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayException;
use Midhub\Payment\DepositService;
use Midhub\Security\Csrf;
use Midhub\Wallet\Wallet;

$userId = (int) $us_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::requireValidPost();
    try {
        $res = DepositService::create(
            $userId,
            (string) (isset($_POST['gateway']) ? $_POST['gateway'] : ''),
            (string) (isset($_POST['wallet']) ? $_POST['wallet'] : ''),
            (string) (isset($_POST['amount']) ? $_POST['amount'] : ''),
            (string) (isset($_POST['nonce']) ? $_POST['nonce'] : '')
        );
        $url = $res['redirect_url'];
        if (!preg_match('#^https?://#i', $url)) {
            throw new GatewayException('Unexpected checkout URL', 'Could not open the payment page.');
        }
        header('Location: ' . $url);
        exit();
    } catch (\InvalidArgumentException $e) {
        $_SESSION['am_flash'] = array('error', $e->getMessage());
    } catch (GatewayException $e) {
        error_log('add_money: ' . $e->getMessage());
        $_SESSION['am_flash'] = array('error', $e->userMessage);
    } catch (\Throwable $e) {
        error_log('add_money: ' . $e->getMessage());
        $_SESSION['am_flash'] = array('error', 'Could not start the payment. Please try again later.');
    }
    header('Location: add_money.php');
    exit();
}

include "header.php";

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$flash = isset($_SESSION['am_flash']) ? $_SESSION['am_flash'] : null;
unset($_SESSION['am_flash']);
$gateways = GatewayConfig::activeDepositGateways();
$nonce = bin2hex(random_bytes(16));
$recent = Db::all('SELECT invoice_no, gateway_slug, wallet, credit_amount, payable_amount, currency, status, created_at FROM gateway_transactions WHERE user_id = ? ORDER BY id DESC LIMIT 15', 'i', array($userId));
$bal = Db::one('SELECT balance, balance2, bkash_balance FROM user WHERE id = ?', 'i', array($userId));
$statusBadge = array('completed' => 'success', 'pending' => 'warning', 'initiated' => 'secondary', 'failed' => 'danger', 'cancelled' => 'secondary', 'review' => 'info');
?>
<style>
  .am-gw input { position:absolute; opacity:0; }
  .am-gw label { display:block; border:1px solid rgba(128,128,128,.35); border-radius:.5rem; padding:.75rem; cursor:pointer; height:100%; }
  .am-gw input:checked + label { border-color:#1abc9c; box-shadow:0 0 0 2px rgba(26,188,156,.35); }
  .am-gw input:focus-visible + label { outline:2px solid #1abc9c; outline-offset:2px; }
  .am-summary td { padding:.25rem 0; } .am-summary td:last-child { text-align:right; font-variant-numeric: tabular-nums; }
</style>
<div class="content-page"><div class="content"><div class="container-fluid">
  <div class="row"><div class="col-12"><div class="page-title-box">
    <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="home.php">Home</a></li><li class="breadcrumb-item active">Add Money</li></ol></div>
    <h4 class="page-title">Add Money Online</h4>
  </div>
  <?php if ($flash): ?><div class="alert alert-<?= $flash[0] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show"><?= $h($flash[1]) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
  </div></div>

  <div class="row">
    <div class="col-lg-7">
      <div class="card"><div class="card-body">
      <?php if (!$gateways): ?>
        <div class="alert alert-warning mb-0">Online payment is not available right now. You can still <a href="send_add_balance.php">request balance manually</a>.</div>
      <?php else: ?>
        <form method="post" id="am-form" autocomplete="off">
          <?= Csrf::field() ?>
          <input type="hidden" name="nonce" value="<?= $h($nonce) ?>">

          <label class="form-label fw-semibold">1. Credit to wallet</label>
          <select name="wallet" class="form-select mb-3" required>
            <?php foreach (Wallet::labels() as $col => $label): ?>
              <option value="<?= $h($col) ?>" <?= $col === 'balance2' ? 'selected' : '' ?>><?= $h($label) ?> (<?= $h($bal ? $bal[$col] : '0.00') ?> Tk)</option>
            <?php endforeach; ?>
          </select>

          <label class="form-label fw-semibold">2. Pay with</label>
          <div class="row g-2 mb-3">
            <?php foreach ($gateways as $i => $g): ?>
              <div class="col-sm-6 am-gw">
                <input type="radio" name="gateway" id="gw-<?= $h($g->slug) ?>" value="<?= $h($g->slug) ?>" <?= $i === 0 ? 'checked' : '' ?>
                       data-currency="<?= $h($g->definition['currency']) ?>" data-min="<?= $h($g->minAmount) ?>" data-max="<?= $h($g->maxAmount) ?>"
                       data-charge="<?= $h($g->chargePercentage) ?>" data-rate="<?= $h($g->definition['currency'] === 'USD' ? \Midhub\Payment\DepositService::creditRate($g) : '1') ?>">
                <label for="gw-<?= $h($g->slug) ?>">
                  <i class="mdi <?= $h($g->definition['icon']) ?> me-1"></i><b><?= $h($g->definition['name']) ?></b>
                  <?php if (!$g->isLive()): ?><span class="badge bg-info ms-1">TEST</span><?php endif; ?><br>
                  <small class="text-muted"><?= $h($g->minAmount . ' to ' . $g->maxAmount . ' ' . $g->definition['currency']) ?><?= (float) $g->chargePercentage > 0 ? ' &middot; ' . $h($g->chargePercentage) . '% charge' : '' ?><?= $g->definition['currency'] === 'USD' ? '<br><b>1 USD = ' . $h(number_format((float) \Midhub\Payment\DepositService::creditRate($g), 2)) . ' BDT</b> credited' : '' ?></small>
                </label>
              </div>
            <?php endforeach; ?>
          </div>

          <label class="form-label fw-semibold" for="am-amount">3. Amount (<span id="am-cur">BDT</span>)</label>
          <input type="number" class="form-control mb-2" name="amount" id="am-amount" step="0.01" min="0.01" required inputmode="decimal">
          <table class="w-100 small am-summary mb-3">
            <tr><td>Gateway charge</td><td id="am-charge">0.00</td></tr>
            <tr><td><b>You pay</b></td><td><b id="am-pay">0.00</b></td></tr>
            <tr><td>Wallet credit</td><td id="am-credit">0.00 BDT</td></tr>
          </table>
          <button type="submit" class="btn btn-success w-100" id="am-submit"><i class="mdi mdi-lock"></i> Continue to secure payment</button>
          <p class="text-muted small mt-2 mb-0">Your wallet is credited only after the provider confirms the payment to our server. Card details are entered on Stripe's secure form and never reach our servers.</p>
        </form>
      <?php endif; ?>
      </div></div>
    </div>

    <div class="col-lg-5">
      <div class="card"><div class="card-body">
        <h5 class="mt-0">Recent online payments</h5>
        <div class="table-responsive">
        <table class="table table-sm small mb-0">
          <thead><tr><th>Invoice</th><th>Via</th><th class="text-end">Credit</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($recent as $t): ?>
            <tr><td class="font-monospace"><?= $h($t['invoice_no']) ?><br><small class="text-muted"><?= $h($t['created_at']) ?></small></td>
                <td><?= $h($t['gateway_slug']) ?></td><td class="text-end"><?= $h($t['credit_amount']) ?></td>
                <td><span class="badge bg-<?= $h(isset($statusBadge[$t['status']]) ? $statusBadge[$t['status']] : 'secondary') ?>"><?= $h($t['status']) ?></span></td></tr>
          <?php endforeach; ?>
          <?php if (!$recent): ?><tr><td colspan="4" class="text-muted text-center">No online payments yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
      </div></div>
    </div>
  </div>
</div></div>
<script>
(function () {
  var form = document.getElementById('am-form'); if (!form) return;
  var amt = document.getElementById('am-amount');
  function paisa(x) { return Math.round(parseFloat(x || 0) * 100); }
  function fmt(p) { return (p / 100).toFixed(2); }
  function update() {
    var g = form.querySelector('input[name=gateway]:checked'); if (!g) return;
    var cur = g.dataset.currency, a = paisa(amt.value), charge = Math.round(a * parseFloat(g.dataset.charge || 0) / 100);
    document.getElementById('am-cur').textContent = cur;
    amt.min = g.dataset.min; amt.max = g.dataset.max;
    document.getElementById('am-charge').textContent = fmt(charge) + ' ' + cur;
    document.getElementById('am-pay').textContent = fmt(a + charge) + ' ' + cur;
    var credit = cur === 'USD' ? Math.round(a * parseFloat(g.dataset.rate || 0)) : a;
    document.getElementById('am-credit').textContent = fmt(credit) + ' BDT' + (cur === 'USD' ? ' (1 USD = ' + parseFloat(g.dataset.rate).toFixed(2) + ' BDT, locked when you continue)' : '');
  }
  form.addEventListener('change', update); amt.addEventListener('input', update); update();
  form.addEventListener('submit', function () { var b = document.getElementById('am-submit'); b.disabled = true; b.textContent = 'Opening payment page...'; });
})();
</script>
<?php include "footer-data.php"; ?>
