<?php
namespace Midhub\Gateway;

use Midhub\Crypto;
use Midhub\Db;

/**
 * Runtime configuration of one provider, resolved for its ACTIVE mode.
 *
 * The admin's Sandbox/Live switch is the only thing that decides which base URL and
 * which credential set the integration uses; nothing is hardcoded in the services.
 */
final class GatewayConfig
{
    /** @var string */ public $slug;
    /** @var string */ public $name;
    /** @var string */ public $category;
    /** @var string active|inactive */ public $status;
    /** @var string sandbox|live */ public $mode;
    /** @var string */ public $baseUrl;
    /** @var array<string,string> credentials of the active mode */ public $credentials = array();
    /** @var array<string,string> */ public $extra = array();
    /** @var string */ public $chargePercentage;
    /** @var string */ public $minAmount;
    /** @var string */ public $maxAmount;
    /** @var string */ public $exchangeRate;
    /** @var array<string,mixed> registry definition */ public $definition;

    /** @var array<string,self> per-request cache */
    private static $cache = array();

    public static function load(string $slug, bool $fresh = false): self
    {
        if (!$fresh && isset(self::$cache[$slug])) {
            return self::$cache[$slug];
        }
        $def = GatewayRegistry::get($slug);
        $row = Db::one('SELECT * FROM payment_gateways WHERE gateway_slug = ? LIMIT 1', 's', array($slug));
        if (!$row) {
            throw new GatewayException("Gateway '$slug' is not configured (run database/migrate.php).", 'This payment method is not available.');
        }
        $c = new self();
        $c->slug = $slug;
        $c->definition = $def;
        $c->name = (string) $row['gateway_name'];
        $c->category = (string) $row['category'];
        $c->status = (string) $row['status'];
        $c->mode = $row['mode'] === 'live' ? 'live' : 'sandbox';
        $c->baseUrl = rtrim((string) ($c->mode === 'live' ? $row['live_base_url'] : $row['sandbox_base_url']), '/');
        $c->credentials = Crypto::decryptJson($c->mode === 'live' ? $row['live_credentials'] : $row['sandbox_credentials']);
        $extra = json_decode((string) $row['extra_config'], true);
        $c->extra = is_array($extra) ? $extra : array();
        $c->chargePercentage = (string) $row['charge_percentage'];
        $c->minAmount = (string) $row['min_amount'];
        $c->maxAmount = (string) $row['max_amount'];
        $c->exchangeRate = (string) $row['exchange_rate'];
        return self::$cache[$slug] = $c;
    }

    public static function clearCache(): void
    {
        self::$cache = array();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLive(): bool
    {
        return $this->mode === 'live';
    }

    /** Credential value for the active mode; throws if a required one is missing. */
    public function cred(string $key, bool $required = true): string
    {
        $v = isset($this->credentials[$key]) ? trim((string) $this->credentials[$key]) : '';
        if ($v === '' && isset($this->definition['fields'][$key]['default'])) {
            $v = (string) $this->definition['fields'][$key]['default'];
        }
        if ($required && $v === '') {
            throw new GatewayException("{$this->name}: '$key' is not set for {$this->mode} mode.", 'This payment method is not configured yet.');
        }
        return $v;
    }

    /** Non-secret setting, falling back to the registry default. */
    public function extra(string $key): string
    {
        if (isset($this->extra[$key]) && trim((string) $this->extra[$key]) !== '') {
            return trim((string) $this->extra[$key]);
        }
        return isset($this->definition['extra'][$key]['default']) ? (string) $this->definition['extra'][$key]['default'] : '';
    }

    public function url(string $path): string
    {
        if ($this->baseUrl === '') {
            throw new GatewayException("{$this->name}: base URL is empty for {$this->mode} mode.", 'This payment method is not configured yet.');
        }
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    /** True when every required credential for the given mode is filled in. */
    public static function credentialsComplete(array $def, array $creds): bool
    {
        foreach ($def['fields'] as $key => $f) {
            if (!empty($f['required']) && trim((string) (isset($creds[$key]) ? $creds[$key] : '')) === '') {
                return false;
            }
        }
        return true;
    }

    /** Active deposit gateways for the customer Add Money page. @return self[] */
    public static function activeDepositGateways(): array
    {
        $out = array();
        foreach (Db::all("SELECT gateway_slug FROM payment_gateways WHERE status = 'active' AND category = 'deposit' ORDER BY sort_order") as $r) {
            // gateways paused by the circuit breaker are hidden until they recover
            if (GatewayRegistry::exists($r['gateway_slug']) && !\Midhub\Ops\CircuitBreaker::isOpen('deposit:' . $r['gateway_slug'])) {
                $out[] = self::load($r['gateway_slug']);
            }
        }
        return $out;
    }

    /** Public URL for a gateway entry point, e.g. publicUrl('gateway/webhook.php?gw=stripe'). */
    public static function publicUrl(string $path): string
    {
        return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
    }
}
