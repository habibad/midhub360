# Midhub360: ভাউচার কেনা, সরাসরি bKash / Nagad / Upay / UddoktaPay দিয়ে পেমেন্ট

শেষ আপডেট: ২৮ নভেম্বর ২০২৬। কোড ও টেস্ট এই ডকুমেন্টের সাথে মিলিয়ে লেখা।

## ১. পুরো প্রক্রিয়া এক নজরে

```
কাস্টমার: Digital Vouchers পেজ -> ভাউচার বাছাই -> "Buy"
   |  (Player ID / মোবাইল নম্বর লাগলে এখানেই দেয়, ভুল ফরম্যাট হলে টাকা কাটার আগেই আটকায়)
   v
"Pay with": bKash / Nagad / Upay / UddoktaPay  (প্রতিটার পাশে "You pay X Tk" চার্জসহ)
           অথবা Main wallet (PIN দিয়ে, রিসেলারদের জন্য)
   |
   v
গেটওয়ের পেমেন্ট পেজ -> কাস্টমার নিজের bKash/Nagad/Upay অ্যাকাউন্ট থেকে পে করে
   |
   v
আমাদের সার্ভার গেটওয়ের কাছে সরাসরি যাচাই করে (redirect-এর কথায় বিশ্বাস করে না)
   - টাকার অঙ্ক, ইনভয়েস, ট্রানজেকশন আইডি না মিললে: কিছু দেওয়া হয় না, "review"-এ যায়, অ্যাডমিন অ্যালার্ট
   |
   v
পেমেন্ট নিশ্চিত -> সাথে সাথে Popular Vouchers-এ অর্ডার
   (দাম পেমেন্টের সময়ের দামেই লক থাকে, পরে দাম বাড়লেও কাস্টমার বেশি দেয় না)
   |
   +-- ডেলিভারি সফল: কোড/PIN "My Vouchers"-এ (এনক্রিপ্টেড), অথবা টপ-আপ সরাসরি Player ID / নম্বরে
   +-- প্রোভাইডার নিশ্চিতভাবে ব্যর্থ: পুরো দাম Main wallet-এ ফেরত
   +-- উত্তর অনিশ্চিত (টাইমআউট ইত্যাদি): "processing", ক্রন প্রতি মিনিটে আবার জিজ্ঞেস করে
```

একই পেমেন্টে কখনো দুইবার ভাউচার কেনা হয় না:
- পেজ রিফ্রেশ, webhook আর ক্রন একসাথে চললেও না।
- প্রতিটা অর্ডার একটা Idempotency-Key দিয়ে Popular Vouchers-এ যায়। একই key আবার গেলে প্রোভাইডার আগের অর্ডারটাই ফেরত দেয়। এটা ২৮ নভেম্বর sandbox-এ যাচাই করা হয়েছে।

কাস্টমার বন্ধ করে দিলেও কাজ থামে না। ব্রাউজার বন্ধ করলে বা নেট চলে গেলেও webhook আর প্রতি মিনিটের ক্রন পেমেন্ট নিশ্চিত করে, তারপর ভাউচার কেনে।

## ২. কী কী লাগবে

| কী | কার কাছ থেকে | কেন |
|---|---|---|
| Popular Vouchers **live** API key | Popular Vouchers distributor portal | এখনকার key-টা sandbox (পরীক্ষার)। live key ছাড়া আসল ভাউচার আসবে না |
| PV distributor ব্যালেন্স | Popular Vouchers-এ টাকা জমা | প্রতিটা অর্ডারের খরচ এখান থেকে কাটে |
| সার্ভারের IP allowlist | PV portal-এ `124.6.224.116` যোগ করা | PV শুধু অনুমোদিত IP থেকে অর্ডার নেয় |
| একটা domain, HTTPS সহ (যেমন `pay.midhub360.com`) | আপনার DNS প্যানেল + certbot | bKash, Nagad, UddoktaPay পেমেন্টের পর এই ঠিকানায় ফেরত পাঠায়। IP দিয়ে চলবে না |
| UddoktaPay অ্যাকাউন্ট (API key + panel URL) | uddoktapay.com | সবচেয়ে দ্রুত চালু করা যায়। এক চেকআউটে bKash/Nagad/Rocket/Upay নেয়, আপনার নিজের নম্বরগুলো ওদের প্যানেলে যুক্ত করবেন |
| bKash Tokenized Checkout merchant credentials | bKash merchant onboarding / PGW টিম | `app_key`, `app_secret`, `username`, `password`। callback domain register করাতে হবে (নিচে দেখুন) |
| Nagad merchant credentials | Nagad merchant onboarding | Merchant ID, merchant নম্বর, আপনার merchant private key, Nagad-এর public key। সার্ভারের IP whitelist |
| Upay merchant credentials | Upay merchant টিম | merchant id/key/code/name/mobile আর ওদের integration document-এর endpoint path |

**গুরুত্বপূর্ণ:** bKash-এর public demo sandbox (`sandboxTokenizedUser02`) এখন কোনো callback URL নেয় না। ১৯টা URL দিয়ে পরীক্ষা করা হয়েছে, সবই error 2049। তাই bKash সরাসরি চালাতে হলে নিজের merchant credentials লাগবেই। ততদিন UddoktaPay দিয়েই কাস্টমার bKash থেকে পে করতে পারবে।

## ৩. কোথায় কী সেট করবেন

### ৩.১ সার্ভার (একবার)
1. Domain: DNS-এ A record দিন, যেমন `pay.midhub360.com -> 124.6.224.116`।
2. SSL: `sudo certbot --nginx -d pay.midhub360.com`
3. `.env`-এ `APP_URL=https://pay.midhub360.com`। এই ঠিকানা দিয়েই সব callback / webhook URL তৈরি হয়।
4. আপডেট: `cd /var/www/midhub360.com && sudo bash deploy/update_live.sh`। এটা migration (015 সহ), Nginx আর ক্রন সব ঠিক করে।
5. ক্রন চলছে কিনা দেখুন: `tail -n 5 /var/log/midhub-worker.log`। প্রতি মিনিটে নতুন লাইন আসার কথা।

### ৩.২ Admin > Gateway Settings
প্রতিটা গেটওয়েতে:
- Sandbox/Live credentials বসান।
- Charge %, Min/Max ঠিক করুন।
- **Test Connection** চাপুন।
- সফল হলে Status: Active করুন।

| গেটওয়ে | Test Connection সফল হলে যা দেখাবে |
|---|---|
| bKash | "Grant Token and Create Payment succeeded". শুধু token না, আসল callback URL দিয়ে একটা পেমেন্ট তৈরি করেও দেখে |
| Nagad | "Keys parsed OK; Nagad host reachable" |
| Upay | "Merchant authentication succeeded" |
| UddoktaPay | "API reachable and key accepted" |
| Popular Voucher | Account, ব্যালেন্স, environment (sandbox/live), catalog সংখ্যা |

Popular Voucher-এ live key দিলে Mode = Live করুন। Live mode-এ sandbox key থাকলে সিস্টেম নিজেই অর্ডার বন্ধ রাখে, যাতে কাস্টমার আসল টাকায় টেস্ট ভাউচার না পায়।

প্রতিটা গেটওয়ের সেটিংস পেজে Return/Callback/Webhook URL দেখানো থাকে। ওগুলো গেটওয়ের merchant প্যানেলে বসাবেন (যেমন UddoktaPay-এর webhook)।

### ৩.৩ Admin > Digital Vouchers (কোন ভাউচার বিক্রি হবে, কত দামে)
1. **Sync catalog** চাপুন। প্রায় ১৪ হাজার প্রোডাক্ট আসে, কয়েক ধাপে।
2. নতুন প্রোডাক্ট শুরুতে **Inactive** থাকে, তাই আপনার অনুমতি ছাড়া কিছু বিক্রি হয় না।
3. ফিল্টার দিয়ে বাছাই করুন: Category (gift_card / mobile_recharge ...), দেশ, বা "Free Fire" লিখে সার্চ।
4. Bulk: **Set markup %** দিন (যেমন ৫%)। বিক্রয়মূল্য = প্রোভাইডারের খরচ + markup, আর প্রতিটা sync-এ খরচের সাথে মিলিয়ে চলে।
5. Bulk: **Activate**। খরচের নিচে দামের প্রোডাক্ট কখনো activate হয় না।
6. কাস্টমারের Digital Vouchers পেজে এখন সেগুলো দেখা যাবে।

## ৪. টেস্ট করার ধাপ (live-এর আগে)
1. সব গেটওয়ে Sandbox mode-এ রাখুন, Popular Voucher-ও sandbox key দিয়ে।
2. একটা সস্তা প্রোডাক্ট activate করুন।
3. কাস্টমার হিসেবে লগইন করুন। Digital Vouchers থেকে Buy চাপুন, Pay with-এ UddoktaPay বাছুন, sandbox-এ পে করুন।
4. যা হওয়ার কথা:
   - ফেরত পেজে "Payment received. Purchase successful..." দেখাবে।
   - My Vouchers-এ অর্ডার **delivered** দেখাবে, কোড `TEST-SANDBOX-...` (sandbox-এ আসল কোড আসে না, প্রোভাইডার শুধু ডেলিভারির ভান করে)।
   - Admin > Digital Vouchers > Recent orders-এ একই অর্ডার দেখা যাবে।

## ৫. সমস্যা হলে কী হয়, আপনাকে কী করতে হবে

| পরিস্থিতি | সিস্টেম নিজে যা করে | আপনার কাজ |
|---|---|---|
| পেমেন্টের অঙ্ক / ইনভয়েস মেলে না | ক্রেডিট দেয় না, "review", critical অ্যালার্ট | গেটওয়ে প্যানেলে মিলিয়ে দেখুন |
| প্রোভাইডার অর্ডার নিশ্চিতভাবে নাকচ করে (স্টক নেই, ভুল Player ID) | দাম Main wallet-এ ফেরত, কারণসহ | কিছু না |
| প্রোভাইডারের উত্তর হারিয়ে যায় | "processing"। ক্রন একই key দিয়ে আবার পাঠায়, আগের অর্ডারটাই ফেরত আসে, নতুন কেনা হয় না | কিছু না |
| অর্ডার "completed" কিন্তু কোড আসেনি (live) | "processing" রাখে, critical অ্যালার্ট দেয়, টাকা ফেরত দেয় না | PV portal থেকে কোড নিয়ে Admin > Digital Vouchers > Recent orders > **Deliver manually** |
| ৬ ঘণ্টার বেশি processing | অ্যালার্ট | **Check with provider now**। portal-এ ব্যর্থ দেখালে **Refund** (Self-PIN লাগে, audit log-এ থাকে) |
| পেমেন্টের পর দাম খরচের নিচে নেমে গেছে / স্টোর বন্ধ | ভাউচার কেনে না, টাকা Main wallet-এ থাকে, কাস্টমারকে নোটিফিকেশন | কিছু না (কাস্টমার wallet দিয়ে আবার কিনতে পারে) |
| PV distributor ব্যালেন্স কম | ২০০০ টাকার নিচে নামলে অ্যালার্ট (Gateway Settings-এ বদলানো যায়) | PV-তে টাকা জমা দিন |

## ৬. Live চালুর চেকলিস্ট
- [ ] Domain + HTTPS + `.env`-এ `APP_URL`
- [ ] `deploy/update_live.sh` সফল, Admin-এ কোনো লাল "Database update needed" নেই
- [ ] Popular Vouchers: live key, IP allowlist, ব্যালেন্স জমা, Mode = Live, Test Connection-এ `environment: live`
- [ ] অন্তত একটা গেটওয়ে live credentials দিয়ে Test Connection সফল, Status Active
- [ ] Digital Vouchers: markup দিয়ে প্রোডাক্ট activate
- [ ] নিজে ছোট অঙ্কের একটা আসল ভাউচার কিনে কোড পাওয়া নিশ্চিত করুন
- [ ] প্রথম live অর্ডারের পর ডেভেলপারকে জানান। কোড API-তে আসছে নাকি শুধু ইমেইলে, সেটা প্রথম live অর্ডারে নিশ্চিত হবে (sandbox কোড দেখায় না)
