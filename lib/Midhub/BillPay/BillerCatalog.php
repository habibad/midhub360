<?php
namespace Midhub\BillPay;

/**
 * Billers and the exact request shape the aggregator's fetch endpoint expects.
 *
 * The electricity / water / gas entries reproduce the legacy integration one-to-one (same
 * slugs, same fields), so existing bill fetches behave exactly as before. DPDC, cable TV
 * (Akash DTH) and internet billers were added for the final concept; their slugs follow the
 * aggregator's naming pattern and can be corrected in Gateway Settings > Bill Pay >
 * "Biller slug overrides" without a code change.
 *
 * Field sources: number = account/bill/meter no, contact = customer mobile, amount = amount
 * (prepaid), month_m / month_y = month and year from the "September 2025" month field.
 */
final class BillerCatalog
{
    /** @return array<string,array{category:string,types:array<string,array{slug:string,fields:array<string,string>}>,verified:bool}> */
    public static function all(): array
    {
        $acc = array('biller_acc_no' => 'number', 'biller_mobile_no' => 'contact');
        $bill = array('bill_no' => 'number', 'biller_mobile_no' => 'contact');
        $prepaidAcc = array('biller_acc_no' => 'number', 'biller_mobile_no' => 'contact', 'amount' => 'amount');
        return array(
            // ---- Electricity (legacy, verified against the existing sandbox integration)
            'BPDB' => array('category' => 'Electricity', 'verified' => true, 'types' => array(
                'Prepaid' => array('slug' => 'bpdb-prepaid', 'fields' => array('meter_no' => 'number', 'biller_mobile_no' => 'contact', 'amount' => 'amount')))),
            'Palli Bidyut' => array('category' => 'Electricity', 'verified' => true, 'types' => array(
                'Postpaid' => array('slug' => 'palli-bidyut-postpaid', 'fields' => array('bill_no' => 'number', 'bill_month' => 'month_m', 'bill_year' => 'month_y')))),
            'DESCO' => array('category' => 'Electricity', 'verified' => true, 'types' => array(
                'Postpaid' => array('slug' => 'desco-postpaid', 'fields' => $bill),
                'Prepaid' => array('slug' => 'desco-prepaid', 'fields' => $prepaidAcc))),
            'NESCO' => array('category' => 'Electricity', 'verified' => true, 'types' => array(
                'Postpaid' => array('slug' => 'nesco-postpaid', 'fields' => $bill))),
            'West Zone' => array('category' => 'Electricity', 'verified' => true, 'types' => array(
                'Postpaid' => array('slug' => 'westzonepower-postpaid', 'fields' => $acc))),
            // ---- Electricity (new)
            'DPDC' => array('category' => 'Electricity', 'verified' => false, 'types' => array(
                'Postpaid' => array('slug' => 'dpdc-postpaid', 'fields' => $bill),
                'Prepaid' => array('slug' => 'dpdc-prepaid', 'fields' => $prepaidAcc))),
            // ---- Water / Gas (legacy)
            'Dhaka WASA' => array('category' => 'Water', 'verified' => true, 'types' => array(
                'Postpaid' => array('slug' => 'dhaka-wasa', 'fields' => $bill))),
            'Jalalabad Gas' => array('category' => 'Gas', 'verified' => true, 'types' => array(
                'Postpaid' => array('slug' => 'jalalabad-gas', 'fields' => $acc))),
            'Bakhrabad Gas' => array('category' => 'Gas', 'verified' => true, 'types' => array(
                'Postpaid' => array('slug' => 'bakhrabad-gas', 'fields' => $acc))),
            'Paschimanchal Gas' => array('category' => 'Gas', 'verified' => true, 'types' => array(
                'Postpaid' => array('slug' => 'paschimanchal-gas', 'fields' => $acc))),
            // ---- Cable TV / Internet (new)
            'Akash DTH' => array('category' => 'Cable TV', 'verified' => false, 'types' => array(
                'Prepaid' => array('slug' => 'akash-dth', 'fields' => $prepaidAcc))),
            'Link3' => array('category' => 'Internet', 'verified' => false, 'types' => array(
                'Postpaid' => array('slug' => 'link3', 'fields' => $acc))),
            'Carnival Internet' => array('category' => 'Internet', 'verified' => false, 'types' => array(
                'Postpaid' => array('slug' => 'carnival-internet', 'fields' => $acc))),
            'AmberIT' => array('category' => 'Internet', 'verified' => false, 'types' => array(
                'Postpaid' => array('slug' => 'amberit', 'fields' => $acc))),
        );
    }

    /** @return string[] company names for one category (for the customer pages) */
    public static function companies(string $category): array
    {
        $out = array();
        foreach (self::all() as $name => $b) {
            if ($b['category'] === $category) {
                $out[] = $name;
            }
        }
        return $out;
    }

    /**
     * Resolves the biller; like the legacy code, a type the biller doesn't have falls back to
     * the biller's only/first type (e.g. BPDB is always the prepaid meter endpoint).
     * @return array{slug:string,fields:array<string,string>,type:string,category:string}|null
     */
    public static function resolve(string $company, string $type): ?array
    {
        $all = self::all();
        if (!isset($all[$company])) {
            return null;
        }
        $types = $all[$company]['types'];
        $t = isset($types[$type]) ? $type : (string) key($types);
        return array('slug' => $types[$t]['slug'], 'fields' => $types[$t]['fields'], 'type' => $t, 'category' => $all[$company]['category']);
    }
}
