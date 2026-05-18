# CryptoDash Dashlet for Nagios XI
**Version 1.0.0**

A custom Nagios XI dashlet that overlays live cryptocurrency prices (30-day history)
with your Nagios XI warning and critical alert counts on an interactive dual-axis chart.

---

## Features
- Live 30-day price chart for BTC, ETH, DOGE, SOL
- Dual Y-axis: coin price (left) + Nagios alert count (right)
- Real-time KPI strip: current price, 24h % change, warning count, critical count
- Top affected hosts list pulled directly from the XI API
- Auto-refreshes every 60 seconds
- Dark theme matching Nagios XI Neptune style

---

## Installation

### 1. Copy files
```bash
cp -r cryptodash-dashlet/ /usr/local/nagiosxi/html/includes/dashlets/
chown -R nagios:nagios /usr/local/nagiosxi/html/includes/dashlets/cryptodash-dashlet/
```

### 2. Set your API credentials
Edit **ajax.php** (lines 11–12):
```php
$apiKey  = "YOUR_NAGIOS_XI_API_KEY_HERE";
$baseUrl = "http://YOUR_NAGIOS_XI_IP/nagiosxi/api/v1/objects";
```

To find your API key: Nagios XI → Admin → API → API Key.

### 3. Register the dashlet (if not auto-detected)
Nagios XI → Admin → Manage Dashlets → click **Update Dashlets**.

### 4. Add to a Dashboard
Go to any Dashboard → Edit → Add Dashlet → search "CryptoDash".

---

## File Structure
```
cryptodash-dashlet/
├── cryptodash-dashlet.inc.php   # Dashlet registration + mode handler
├── ajax.php                     # AJAX endpoint: renders HTML + Chart.js widget
└── README.md                    # This file
```

---

## Dependencies
- **Chart.js 4.4** — loaded from CDN (cdn.jsdelivr.net) — no local install needed
- **CryptoCompare public API** — free, no key required for histoday endpoint
- **Nagios XI API** — internal, uses your XI API key

---

## Customization

| What | Where | How |
|---|---|---|
| Default coin | `ajax.php` | Change `let currentCoin = 'BTC'` |
| Default alert type | `ajax.php` | Change `let currentAlertType = 'critical'` |
| Refresh rate | `cryptodash-dashlet.inc.php` | Change `DASHLET_REFRESHRATE` (seconds) |
| Add more coins | `ajax.php` | Add entry to the `COINS` object and add a `<button>` in the HTML |
| API records limit | `ajax.php` | Change `records=5000` in the fetch URL |

---

## Troubleshooting

**Chart shows "--" for alerts**
→ Check that `$apiKey` and `$baseUrl` in ajax.php are correct.
→ Verify the XI API is accessible from the web server: `curl "http://YOUR_XI_IP/nagiosxi/api/v1/objects/servicestatus?apikey=YOUR_KEY&current_state=2"`

**Crypto prices don't load**
→ Your server may be blocking outbound requests to cryptocompare.com.  
→ Check browser console (F12 → Console) for CORS or network errors.

**Dashlet not appearing in the list**
→ Run as root: `php /usr/local/nagiosxi/html/includes/dashlets/cryptodash-dashlet/cryptodash-dashlet.inc.php`  
→ Then go to Admin → Manage Dashlets → Update Dashlets.

---

## License
MIT License — Copyright (c) 2025
