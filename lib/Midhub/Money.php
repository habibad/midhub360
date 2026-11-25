<?php
namespace Midhub;

/**
 * Money helpers. Amounts travel as 2-decimal strings ("1250.50") and are compared or
 * summed as integer paisa, so nothing depends on float rounding or the bcmath extension.
 */
final class Money
{
    /**
     * Validates user input and normalizes it to a 2-decimal string.
     * Accepts "100", "100.5", "100.50". Rejects negatives, exponents and >2 decimals.
     *
     * @throws \InvalidArgumentException
     */
    public static function parse($value): string
    {
        if (is_int($value)) {
            $value = (string) $value;
        } elseif (is_float($value)) {
            $value = number_format($value, 2, '.', '');
        }
        $value = trim((string) $value);
        if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $value)) {
            throw new \InvalidArgumentException('Invalid amount.');
        }
        return self::fromPaisa(self::toPaisa($value));
    }

    public static function toPaisa($value): int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }
        $neg = false;
        if ($value[0] === '-') {
            $neg = true;
            $value = substr($value, 1);
        }
        $parts = explode('.', $value, 2);
        $whole = (int) ($parts[0] === '' ? '0' : $parts[0]);
        $frac = isset($parts[1]) ? $parts[1] : '0';
        $frac = substr(str_pad($frac, 3, '0'), 0, 3);
        // round half-up on the third decimal
        $paisa = $whole * 100 + intdiv((int) $frac + 5, 10);
        return $neg ? -$paisa : $paisa;
    }

    public static function fromPaisa(int $paisa): string
    {
        $neg = $paisa < 0;
        $paisa = abs($paisa);
        return ($neg ? '-' : '') . intdiv($paisa, 100) . '.' . str_pad((string) ($paisa % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function add(string $a, string $b): string
    {
        return self::fromPaisa(self::toPaisa($a) + self::toPaisa($b));
    }

    public static function sub(string $a, string $b): string
    {
        return self::fromPaisa(self::toPaisa($a) - self::toPaisa($b));
    }

    /** -1, 0 or 1 */
    public static function cmp(string $a, string $b): int
    {
        $x = self::toPaisa($a);
        $y = self::toPaisa($b);
        return $x <=> $y;
    }

    /** amount * percent / 100, rounded half-up to paisa. */
    public static function percent(string $amount, $percent): string
    {
        $bp = (int) round(((float) $percent) * 100); // basis points
        $p = self::toPaisa($amount) * $bp;
        return self::fromPaisa(intdiv($p + 5000, 10000));
    }

    /** amount * rate, rounded half-up to paisa (USD -> BDT conversion). */
    public static function multiply(string $amount, $rate): string
    {
        $rateMicro = (int) round(((float) $rate) * 10000); // 4-decimal rate precision
        $p = self::toPaisa($amount) * $rateMicro;
        return self::fromPaisa(intdiv($p + 5000, 10000));
    }

    /** Amount in the smallest currency unit (cents / paisa) as int, for Stripe. */
    public static function toMinorUnits(string $amount): int
    {
        return self::toPaisa($amount);
    }
}
