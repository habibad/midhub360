# Midhub360 Codebase Audit & API Integration Report

**Date:** November 8, 2026  
**Project:** Midhub360.com (Aanonda Telecom & Reseller Platform)  
**Scope of Review:** MFS, Mobile Recharge/Topup, and Utility Bill Payment features  

---

## 1. Executive Summary

A comprehensive architectural and code-level audit was conducted across the entire codebase (`midhub360.com`), analyzing all backend PHP classes, routing controllers, frontend customer pages, administrative modules, and MySQL database schemas (`aanonda_software_new.sql`).

### Summary of Findings:
- **Client Assertion vs Reality:** The client stated that these features are already implemented in the existing project. The audit reveals that **the frontend user forms, multi-level reseller logic, balance deduction rules, and database tables are in place**, but **automated third-party APIs for live financial execution are either non-existent (manual only), configured with mock/sandbox endpoints, or expired.**
- **MFS (bKash, Nagad, Rocket):** **0% automated API.** Fully functional as a **manual queue** (deducts wallet balance and logs into database for an admin to execute manually via phone/SIM).
- **Mobile Recharge (All operators):** **85% functional architecture.** `eFlexiload` and `Paywell` integration stubs exist, but Paywell is not connected to the recharge execution pipeline, and the Android SIM modem bridge (`apimobile/`) is permanently disabled due to a hardcoded expiration timestamp.
- **Utility Bills Pay:**
  - **Electricity, WASA, Gas:** Integrated only with `shl.com.bd` **Sandbox Fetch** with a hardcoded mock token. The actual bill payment execution API is **completely missing** (it only updates the local database and deducts local balance).
  - **Cable Bill & Internet Bill:** **0% implemented.** Links on the dashboard point to `#`.

---

## 2. Feature Availability & Gap Analysis Matrix

| Feature / Service | Sub-Services | UI & Form Status | DB Schema & Balance Flow | Live API Integration | Overall Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **1. MFS (Mobile Banking)** | bKash, Nagad, Rocket, Upay | Complete | Complete (`bkash_send`) | **No Automated API** (Manual Admin Processing) | **Manual Only** |
| **2. Mobile Recharge / Topup** | GP, Banglalink, Robi, Airtel, Teletalk | Complete | Complete (`send_query`) | `eFlexiload` exists; `Paywell` unconnected; `apimobile` expired | **Needs Gateway Hookup** |
| **3. Electric Bill** | Palli Bidyut, BPDB, DESCO, NESCO, West Zone | Complete | Complete (`billpay_request`) | Sandbox Fetch only (`shl.com.bd`); Payment API missing | **Half-Baked (Mock)** |
| **4. WASA Bill (Water)** | Dhaka, Khulna, Rajshahi WASA | Partial (Dhaka only) | Complete (`billpay_request`) | Sandbox Fetch only (Dhaka); Payment API missing | **Half-Baked (Mock)** |
| **5. Gas Bill** | Jalalabad, Bakhrabad, Paschimanchal Gas | Partial (3 providers) | Complete (`billpay_request`) | Sandbox Fetch only; Payment API missing; Titas disabled | **Half-Baked (Mock)** |
| **6. Cable Bill** | Cable TV Providers | Not Implemented | No Schema | None | **Not Available (0%)** |
| **7. Internet Bill** | ISP / Broadband Providers | Not Implemented | No Schema | None | **Not Available (0%)** |

---

## 3. Deep-Dive Technical Review by Module

### 3.1 Mobile Financial Services (MFS) - bKash, Nagad, Rocket, Upay

#### Existing Code Assets:
- **Customer Pages:** 
  - `customer/send_bkash.php`
  - `customer/send_nagad.php`
  - `customer/send_rocket.php`
  - `customer/send_upay.php`
- **Controller & Model:** 
  - `customer/route.php` (`action=requestMBSend`)
  - `customer/class/Request.php` -> `make_m_banking()`
- **Database Table:** `bkash_send`

#### Workflow & Code Analysis:
When a customer enters an account number, amount, transaction type (`CashIn`, `CashOut`, `SendMoney`, `Payment`), and their PIN:
1. Validates input and PIN hash against the user record.
2. Checks user's `bkash_balance` and minimum balance limit.
3. Deducts `amount` from `user.bkash_balance`.
4. Executes `INSERT INTO bkash_send (...) VALUES (..., status='0', ...)` with `status='0'` (Pending).
5. **No outbound API call is made.**

#### Admin Workflow:
In `admin/mbank_history.php`, the admin sees all pending requests and must manually dispatch the money via their physical agent SIM/app and manually mark the transaction as Success (`1`) or Cancelled (`2`).

**What is needed for Workable MFS:**
If the client expects automated transactions without manual admin intervention, an official **B2C Disbursement API** (bKash Payout API, Nagad Disbursement API, Rocket Corporate API) or an aggregator API (e.g. UddoktaPay / Shurjopay Payout API) must be integrated into `Request::make_m_banking()`.

---

### 3.2 Mobile Recharge / Topup (GP, Banglalink, Robi, Airtel, Teletalk)

#### Existing Code Assets:
- **Customer Pages:** 
  - `customer/send_recharge.php`
  - `customer/send_mnp.php`
- **Execution Engine:** 
  - `customer/class/Request.php` -> `make_flexiload()`
- **API Gateways Present:**
  - `customer/class/Api.php`: `Api::apirequest(...)` (eFlexiload gateway)
  - `customer/class/Paywell.php`: `Paywell::paywell_recharge(...)` (Paywell gateway)
  - `apimobile/`: Android SIM Modem polling bridge
- **Database Table:** `send_query`

#### Current Logic:
- Automatic detection of operator by phone prefix:
  - GP: `017`, `013`
  - Banglalink: `019`, `014`
  - Robi: `018`
  - Airtel: `016`
  - Teletalk: `015`
- Handles prepaid/postpaid, multi-level reseller commissions across up to 5 upline tiers (Retailer, Dealer, DGM, Distributor, Subadmin).
- Deducts amount from `user.balance2` (Recharge balance).

#### Critical Deficiencies & Blockers:
1. **Paywell Disconnected:** While `Paywell.php` has code for `getToken` and `singleTopup`, `Request::make_flexiload()` only checks `Api::apistatus("1", "eFlexiload", "Recharge")`. Paywell is **never invoked** for customer recharges.
2. **Expired Android SIM Gateway (`apimobile/`):** All files in `apimobile/` contain a hardcoded kill switch:
   ```php
   if(time()>1675101600||time()<1660451071) die('<b>This script expired on '.date('d M Y',1675101600).'.</b>');
   ```
   This script officially expired on **January 30, 2023**. Any mobile app attempting to pull pending recharges or post SMS confirmation messages is terminated immediately.

---

### 3.3 Utility Bills Payment (Electric, WASA, Gas, Cable, Internet)

#### Existing Code Assets:
- `customer/send_bill.php` (Service Selector)
- `customer/bill_electricity.php`
- `customer/bill_water.php`
- `customer/bill_gas.php`
- `customer/process_bill_payment.php` (AJAX Backend)
- **Database Table:** `billpay_request`

#### 1. Electric Bill
- **Companies configured:** BPDB, Palli Bidyut, DESCO (Prepaid & Postpaid), NESCO, West Zone.
- **Fetch Mechanism:** Calls `https://shl.com.bd/api/appapi/billpay/fetch/sandbox/...` with a hardcoded mock token (`token: randomToken`).
- **Payment Mechanism:** When clicking Pay Bill, `process_bill_payment.php` executes:
  ```php
  case "bill_payment_request":
      $query = "UPDATE user set bkash_balance=bkash_balance-'$bill_total_amount' where id='$us_id'";
      $query = "UPDATE billpay_request set trx_status = 2, is_bill_paid = 'Y', status = 1 where bill_payment_id='$bill_payment_id'";
  ```
  **No external payment API is contacted.** The bill is never actually paid to the utility provider.

#### 2. WASA (Water) Bill
- **Companies in dropdown:** Dhaka WASA, Khulna WASA, Rajshahi WASA.
- **Backend support:** Only **Dhaka WASA** is handled in `process_bill_payment.php`. Khulna and Rajshahi WASA are completely ignored.
- **Payment Mechanism:** Same dummy local DB update; no live payout.

#### 3. Gas Bill
- **Companies configured:** Jalalabad Gas, Bakhrabad Gas, Paschimanchal Gas. (Titas Gas is commented out).
- **Backend support:** Sandbox fetch exists for the 3 active providers.
- **Payment Mechanism:** Same dummy local DB update; no live payout.

#### 4. Cable TV Bill
- **Status:** **0% Implemented.**
- `customer/send_bill.php` has `<a href="#">` with a TV icon. No forms, no controller, no DB logic.

#### 5. Internet Bill (ISP Broadband)
- **Status:** **0% Implemented.**
- `customer/send_bill.php` has `<a href="#">` with an internet icon.
- *(Note: `customer/package.php` handles mobile operator data packages such as GP 1GB/30 Days, not broadband ISP bill payments).*

---

### 3.4 Payment Gateway & Add Balance (Deposit) Review

In addition to the requested services, the wallet funding mechanism was reviewed:
- **Manual Deposits:** Users submit Bank / MFS transaction IDs in `customer/send_add_balance.php`.
- **Online Payment Gateway:** In `customer/class/Payment.php`, there is a test call to `PayStation` Sandbox (`https://sandbox.paystation.com.bd/initiate-payment`) with hardcoded test credentials:
  - `merchantId`: `104-1653730183`
  - `password`: `gamecoderstorepass`
  - The callback file `customer/payment_gateway_callback.php` is completely empty (0 bytes).
- **UddoktaPay Archive:** The `payment/` folder contains `UddoktaPay.zip` and an unconfigured installer that requires PHP 7.4 + ionCube Loader 12.0.

---

## 4. Key Questions & Clarifications Needed From Client

To make these services genuinely workable, the client must clarify and provide the following:

1. **For MFS (bKash, Nagad, Rocket):**
   - Does the client possess an official **B2C Disbursement Merchant Account** with bKash, Nagad, or Rocket?
   - Or do they want to use an automated SIM gateway (Android app with agent SIMs) or a third-party aggregator (like UddoktaPay Payout / Shurjopay Payout)?

2. **For Mobile Recharge / Topup:**
   - Which provider does the client hold an active contract with? **Paywell**, **eFlexiload**, or physical SIM modems?
   - Live API credentials (Username, Password, API Key, Base URL) must be provided.

3. **For Utility Bills Pay:**
   - Is `shl.com.bd` their chosen bill pay aggregator?
   - If yes, their **Production API Token** and documentation for the bill payment endpoint (beyond the sandbox fetch) are needed.
   - For Cable & Internet bill pay, which billers (e.g. AmberIT, Carnival, Link3, Akash DTH) do they need integrated?

---

## 5. Technical Implementation Roadmap

1. **Fix Core Blockers:**
   - Clean & remove timestamp expiration in `apimobile/`.
   - Unify API configuration table in database.

2. **Mobile Recharge Integration:**
   - Configure eFlexiload or complete Paywell router in `Request::make_flexiload`.
   - Implement real-time transaction status callback/webhook.

3. **Utility Bill Pay Modernization:**
   - Replace `shl.com.bd` sandbox fetch endpoints with live production credentials.
   - Implement the missing live payment submission API in `process_bill_payment.php`.
   - Create dedicated pages for Cable TV & ISP Internet bills.

4. **MFS Automation:**
   - Implement API cURL handler for bKash/Nagad/Rocket disbursement in `Request::make_m_banking`.
   - Add webhook listener to update `bkash_send` status upon transaction confirmation.

