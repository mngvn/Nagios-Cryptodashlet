<?php
/**
 * CryptoDash Dashlet — ajax.php
 *
 * ┌──────────────────────────────────────────────┐
 * │  CONFIGURATION — edit the two lines below    │
 * └──────────────────────────────────────────────┘
 */
$apiKey  = "i7HsNIQpBNrPVfI0IV9klJiPfJCJBFSWIGFK03tVaqXYBqa6gGWojTo0rBui7vop";
$baseUrl = "http://192.168.164.12/nagiosxi/api/v1/objects";
/*
 * No further edits needed below this line.
 */

require_once(dirname(__FILE__) . '/../../common.inc.php');

pre_init();
init_session();
grab_request_vars();
check_prereqs();
check_authentication(false);
check_nagios_session_protector();

$mode      = grab_request_var('mode', '');
$container = grab_request_var('container', 'cryptodash_container');
$container = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $container);

if ($mode !== 'getcontent') { exit; }

$js_apiKey    = json_encode($apiKey);
$js_baseUrl   = json_encode($baseUrl);
$js_container = json_encode($container);
?>
<div id="<?php echo $container; ?>_root" style="
    font-family: 'Courier New', Courier, monospace;
    background: linear-gradient(160deg, #0d1117 0%, #161b22 100%);
    color: #e6edf3;
    border-radius: 10px;
    padding: 14px 16px 10px;
    width: 100%; height: 100%;
    box-sizing: border-box;
    display: flex; flex-direction: column; gap: 8px;
    overflow: hidden;
">

    <!-- Header -->
    <div style="display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
        <div style="font-size:15px; font-weight:bold; color:#58a6ff; letter-spacing:1px;">&#x20BF; CryptoDash</div>
        <div style="display:flex; gap:6px;">
            <button onclick="cd_setCoin_<?php echo $container; ?>('BTC')"
                style="padding:3px 9px; background:#21262d; color:#f0c040; border:1px solid #f0c04066; border-radius:4px; cursor:pointer; font-size:11px; font-family:inherit;">BTC</button>
            <button onclick="cd_setCoin_<?php echo $container; ?>('ETH')"
                style="padding:3px 9px; background:#21262d; color:#a371f7; border:1px solid #a371f766; border-radius:4px; cursor:pointer; font-size:11px; font-family:inherit;">ETH</button>
            <button onclick="cd_setCoin_<?php echo $container; ?>('DOGE')"
                style="padding:3px 9px; background:#21262d; color:#3fb950; border:1px solid #3fb95066; border-radius:4px; cursor:pointer; font-size:11px; font-family:inherit;">DOGE</button>
            <button onclick="cd_setCoin_<?php echo $container; ?>('SOL')"
                style="padding:3px 9px; background:#21262d; color:#9745fe; border:1px solid #9745fe66; border-radius:4px; cursor:pointer; font-size:11px; font-family:inherit;">SOL</button>
        </div>
    </div>

    <!-- KPI strip -->
    <div style="display:flex; gap:8px; flex-shrink:0; flex-wrap:wrap;">
        <div style="flex:1; min-width:70px; background:#21262d; border:1px solid #30363d; border-radius:6px; padding:5px 8px; text-align:center;">
            <div style="font-size:9px; color:#8b949e; margin-bottom:2px;">PRICE</div>
            <div id="<?php echo $container; ?>_price" style="font-size:15px; font-weight:bold; color:#58a6ff;">--</div>
        </div>
        <div style="flex:1; min-width:70px; background:#21262d; border:1px solid #30363d; border-radius:6px; padding:5px 8px; text-align:center;">
            <div style="font-size:9px; color:#8b949e; margin-bottom:2px;">24h CHG</div>
            <div id="<?php echo $container; ?>_change" style="font-size:15px; font-weight:bold; color:#8b949e;">--</div>
        </div>
        <div onclick="cd_showTab_<?php echo $container; ?>('warning')"
             style="flex:1; min-width:70px; background:#21262d; border:1px solid #f0883e55; border-radius:6px; padding:5px 8px; text-align:center; cursor:pointer;" title="View warning services">
            <div style="font-size:9px; color:#f0883e; margin-bottom:2px;">&#9888; WARNINGS</div>
            <div id="<?php echo $container; ?>_warnings" style="font-size:15px; font-weight:bold; color:#f0883e;">--</div>
        </div>
        <div onclick="cd_showTab_<?php echo $container; ?>('critical')"
             style="flex:1; min-width:70px; background:#21262d; border:1px solid #f8514955; border-radius:6px; padding:5px 8px; text-align:center; cursor:pointer;" title="View critical services">
            <div style="font-size:9px; color:#f85149; margin-bottom:2px;">&#128293; CRITICALS</div>
            <div id="<?php echo $container; ?>_criticals" style="font-size:15px; font-weight:bold; color:#f85149;">--</div>
        </div>
    </div>

    <!-- Chart -->
    <div style="flex:1; position:relative; min-height:120px;">
        <canvas id="<?php echo $container; ?>_canvas" style="width:100%;height:100%;display:block;"></canvas>
    </div>

    <!-- Service detail panel -->
    <div style="flex-shrink:0; background:#0d1117; border-radius:6px; overflow:hidden; border:1px solid #21262d;">

        <!-- Tab bar -->
        <div style="display:flex; border-bottom:1px solid #21262d;">
            <button id="<?php echo $container; ?>_tab_warning"
                onclick="cd_showTab_<?php echo $container; ?>('warning')"
                style="flex:1; padding:5px 0; background:#0d1117; color:#f0883e;
                       border:none; border-right:1px solid #21262d; border-bottom:2px solid #f0883e;
                       cursor:pointer; font-family:'Courier New',monospace; font-size:11px; font-weight:bold;">
                &#9888; Warnings (<span id="<?php echo $container; ?>_warn_count">0</span>)
            </button>
            <button id="<?php echo $container; ?>_tab_critical"
                onclick="cd_showTab_<?php echo $container; ?>('critical')"
                style="flex:1; padding:5px 0; background:#161b22; color:#8b949e;
                       border:none; border-bottom:2px solid transparent;
                       cursor:pointer; font-family:'Courier New',monospace; font-size:11px; font-weight:bold;">
                &#128293; Criticals (<span id="<?php echo $container; ?>_crit_count">0</span>)
            </button>
        </div>

        <!-- Warning services table -->
        <div id="<?php echo $container; ?>_panel_warning" style="max-height:120px; overflow-y:auto; display:block;">
            <table style="width:100%; border-collapse:collapse; font-size:10px;">
                <thead>
                    <tr style="background:#161b22; color:#484f58; font-size:9px; text-transform:uppercase; position:sticky; top:0;">
                        <th style="padding:3px 8px; text-align:left; border-bottom:1px solid #21262d; white-space:nowrap;">Host</th>
                        <th style="padding:3px 8px; text-align:left; border-bottom:1px solid #21262d;">Service</th>
                        <th style="padding:3px 8px; text-align:left; border-bottom:1px solid #21262d; white-space:nowrap;">Since</th>
                        <th style="padding:3px 8px; text-align:left; border-bottom:1px solid #21262d;">Output</th>
                    </tr>
                </thead>
                <tbody id="<?php echo $container; ?>_warn_rows">
                    <tr><td colspan="4" style="padding:8px; color:#484f58;">Loading&hellip;</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Critical services table -->
        <div id="<?php echo $container; ?>_panel_critical" style="max-height:120px; overflow-y:auto; display:none;">
            <table style="width:100%; border-collapse:collapse; font-size:10px;">
                <thead>
                    <tr style="background:#161b22; color:#484f58; font-size:9px; text-transform:uppercase; position:sticky; top:0;">
                        <th style="padding:3px 8px; text-align:left; border-bottom:1px solid #21262d; white-space:nowrap;">Host</th>
                        <th style="padding:3px 8px; text-align:left; border-bottom:1px solid #21262d;">Service</th>
                        <th style="padding:3px 8px; text-align:left; border-bottom:1px solid #21262d; white-space:nowrap;">Since</th>
                        <th style="padding:3px 8px; text-align:left; border-bottom:1px solid #21262d;">Output</th>
                    </tr>
                </thead>
                <tbody id="<?php echo $container; ?>_crit_rows">
                    <tr><td colspan="4" style="padding:8px; color:#484f58;">Loading&hellip;</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status bar -->
    <div id="<?php echo $container; ?>_status"
         style="flex-shrink:0; font-size:10px; color:#484f58; text-align:right;">
        Initializing&hellip;
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script type="text/javascript">
(function() {
    var _apiKey  = <?php echo $js_apiKey; ?>;
    var _baseUrl = <?php echo $js_baseUrl; ?>;
    var _cid     = <?php echo $js_container; ?>;

    var COINS = {
        BTC:  { color: '#f0c040', sym: 'BTC' },
        ETH:  { color: '#a371f7', sym: 'ETH' },
        DOGE: { color: '#3fb950', sym: 'DOGE' },
        SOL:  { color: '#9745fe', sym: 'SOL' },
    };

    var currentCoin   = 'BTC';
    var chartInstance = null;

    function el(suffix) { return document.getElementById(_cid + suffix); }

    function fmtUSD(n) {
        n = parseFloat(n) || 0;
        if (n >= 1000) return '$' + n.toLocaleString('en-US', { maximumFractionDigits: 0 });
        if (n >= 1)    return '$' + n.toFixed(2);
        return '$' + n.toFixed(4);
    }

    function fmtDate(str) {
        if (!str || str.length < 10) return '-';
        var d = str.substring(5, 10).replace('-', '/');
        var t = str.substring(11, 16);
        return t ? d + ' ' + t : d;
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Safely extract a field trying multiple possible key names
    function pick(obj) {
        // arguments 1..N are candidate key names, returns first non-empty match
        for (var i = 1; i < arguments.length; i++) {
            var v = obj[arguments[i]];
            if (v !== undefined && v !== null && v !== '') return v;
        }
        return '';
    }

    async function safeJSON(url) {
        var r = await fetch(url, { credentials: 'same-origin' });
        if (!r.ok) throw new Error('HTTP ' + r.status + ' from XI API');
        var txt = await r.text();
        if (txt.trim().startsWith('<')) throw new Error('XI returned HTML — check API key and baseUrl in ajax.php');
        return JSON.parse(txt);
    }

    // Tab switcher
    function showTab(type) {
        var isWarn = (type === 'warning');
        el('_panel_warning').style.display  = isWarn ? 'block' : 'none';
        el('_panel_critical').style.display = isWarn ? 'none'  : 'block';
        var wBtn = el('_tab_warning');
        var cBtn = el('_tab_critical');
        wBtn.style.background   = isWarn ? '#0d1117' : '#161b22';
        wBtn.style.color        = isWarn ? '#f0883e' : '#8b949e';
        wBtn.style.borderBottom = isWarn ? '2px solid #f0883e' : '2px solid transparent';
        cBtn.style.background   = isWarn ? '#161b22' : '#0d1117';
        cBtn.style.color        = isWarn ? '#8b949e' : '#f85149';
        cBtn.style.borderBottom = isWarn ? '2px solid transparent' : '2px solid #f85149';
    }

    window['cd_setCoin_'  + _cid] = function(coin) { currentCoin = coin; loadAll(); };
    window['cd_showTab_'  + _cid] = showTab;

    // Build table rows from a list of service objects.
    // The XI API /servicestatus returns objects whose keys vary by XI version.
    // We try every known variant for each field.
    function buildRows(services, color) {
        if (!services.length) {
            return '<tr><td colspan="4" style="padding:8px; color:#3fb950;">&#10003; None active</td></tr>';
        }
        return services.map(function(svc) {
            // Host name
            var host = pick(svc,
                'host_name', 'hostname', 'host');

            // Service / check name
            var name = pick(svc,
                'name',                    // XI API primary key
                'service_description',     // alternate
                'display_name',
                'description');

            // Timestamp — when it entered this state
            var sinceRaw = pick(svc,
                'last_state_change',
                'last_check',
                'last_time_critical',
                'last_time_warning',
                'status_update_time');
            var since = fmtDate(sinceRaw);

            // Plugin output
            var rawOut = pick(svc,
                'status_information',      // XI API primary key
                'output',
                'plugin_output',
                'long_plugin_output');
            var output = String(rawOut);
            if (output.length > 70) output = output.substring(0, 70) + '\u2026';

            return '<tr style="border-bottom:1px solid #21262d;">'
                + '<td style="padding:3px 8px; color:#e6edf3; white-space:nowrap;">'           + esc(host)   + '</td>'
                + '<td style="padding:3px 8px; color:' + color + '; white-space:nowrap;">'     + esc(name)   + '</td>'
                + '<td style="padding:3px 8px; color:#484f58; white-space:nowrap;">'           + esc(since)  + '</td>'
                + '<td style="padding:3px 8px; color:#8b949e; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + esc(rawOut) + '">' + esc(output) + '</td>'
                + '</tr>';
        }).join('');
    }

    // Bucket services by day aligned to isoKeys array
    function bucketByDay(services, isoKeys) {
        var map = {};
        isoKeys.forEach(function(k) { map[k] = 0; });
        services.forEach(function(svc) {
            var dt = pick(svc,
                'last_state_change', 'last_check',
                'last_time_critical', 'last_time_warning', 'status_update_time');
            var iso = String(dt).substring(0, 10);
            if (map[iso] !== undefined) map[iso]++;
        });
        return isoKeys.map(function(k) { return map[k]; });
    }

    // Sort services by host name then service name
    function sortSvcs(arr) {
        return arr.slice().sort(function(a, b) {
            var ha = pick(a, 'host_name', 'hostname', 'host');
            var hb = pick(b, 'host_name', 'hostname', 'host');
            var h  = ha.localeCompare(hb);
            if (h !== 0) return h;
            var na = pick(a, 'name', 'service_description', 'display_name', 'description');
            var nb = pick(b, 'name', 'service_description', 'display_name', 'description');
            return na.localeCompare(nb);
        });
    }

    // Extract the service array from whatever shape the API returned
    function extractServices(json) {
        if (!json) return [];
        // XI wraps results: { servicestatus: [...] } or { servicestatuslist: { servicestatus: [...] } }
        if (Array.isArray(json.servicestatus))           return json.servicestatus;
        if (json.servicestatuslist && Array.isArray(json.servicestatuslist.servicestatus))
                                                         return json.servicestatuslist.servicestatus;
        // Some versions return the array at the top level
        if (Array.isArray(json))                         return json;
        // Last resort: look for any array value in the top-level object
        var keys = Object.keys(json);
        for (var i = 0; i < keys.length; i++) {
            if (Array.isArray(json[keys[i]])) return json[keys[i]];
        }
        return [];
    }

    async function loadAll() {
        el('_status').textContent = 'Refreshing...';
        try {
            // 1. Crypto 30-day history
            var cryptoObj = await safeJSON(
                'https://min-api.cryptocompare.com/data/v2/histoday'
                + '?fsym=' + encodeURIComponent(currentCoin)
                + '&tsym=USD&limit=29'
            );
            if (!cryptoObj || !cryptoObj.Data || !cryptoObj.Data.Data) {
                throw new Error('Unexpected response from CryptoCompare API');
            }

            var days      = cryptoObj.Data.Data;
            var isoKeys   = days.map(function(d) { return new Date(d.time * 1000).toISOString().split('T')[0]; });
            var prices    = days.map(function(d) { return parseFloat(d.close) || 0; });
            var latest    = prices[prices.length - 1];
            var prev      = prices[prices.length - 2] || latest;
            var pctChange = prev ? ((latest - prev) / prev * 100) : 0;

            el('_price').textContent = fmtUSD(latest);
            var chgEl = el('_change');
            chgEl.textContent = (pctChange >= 0 ? '\u25B2 ' : '\u25BC ') + Math.abs(pctChange).toFixed(2) + '%';
            chgEl.style.color = pctChange >= 0 ? '#3fb950' : '#f85149';

            // 2. Nagios XI — warning (state=1) and critical (state=2) in parallel
            var warnUrl = _baseUrl + '/servicestatus?apikey=' + encodeURIComponent(_apiKey) + '&current_state=1&records=5000';
            var critUrl = _baseUrl + '/servicestatus?apikey=' + encodeURIComponent(_apiKey) + '&current_state=2&records=5000';

            var results = await Promise.all([
                safeJSON(warnUrl).catch(function(e) { console.warn('[CryptoDash] Warning fetch failed:', e); return {}; }),
                safeJSON(critUrl).catch(function(e) { console.warn('[CryptoDash] Critical fetch failed:', e); return {}; }),
            ]);

            var warnings  = extractServices(results[0]);
            var criticals = extractServices(results[1]);

            // Log first record of each to console so field names are visible in browser DevTools
            if (warnings.length)  console.log('[CryptoDash] Sample WARNING  record:', warnings[0]);
            if (criticals.length) console.log('[CryptoDash] Sample CRITICAL record:', criticals[0]);

            // 3. Update counters
            el('_warnings').textContent   = warnings.length;
            el('_criticals').textContent  = criticals.length;
            el('_warn_count').textContent = warnings.length;
            el('_crit_count').textContent = criticals.length;

            // 4. Populate service tables
            el('_warn_rows').innerHTML = buildRows(sortSvcs(warnings),  '#f0883e');
            el('_crit_rows').innerHTML = buildRows(sortSvcs(criticals), '#f85149');

            // 5. Chart time-series
            var warnSeries = bucketByDay(warnings,  isoKeys);
            var critSeries = bucketByDay(criticals, isoKeys);
            var coin   = COINS[currentCoin] || COINS.BTC;
            var labels = isoKeys.map(function(iso) {
                var d = new Date(iso);
                return (d.getMonth() + 1) + '/' + d.getDate();
            });

            var datasets = [
                {
                    label: currentCoin + ' / USD',
                    data: prices,
                    borderColor: coin.color,
                    backgroundColor: coin.color + '1a',
                    fill: true, tension: 0.35,
                    pointRadius: 0, borderWidth: 2,
                    yAxisID: 'yPrice', order: 3,
                },
                {
                    label: 'Warnings',
                    data: warnSeries,
                    borderColor: '#f0883e',
                    backgroundColor: 'transparent',
                    borderDash: [5, 3], tension: 0.3,
                    pointRadius: 2, pointBackgroundColor: '#f0883e',
                    borderWidth: 1.5,
                    yAxisID: 'yAlert', order: 2,
                },
                {
                    label: 'Criticals',
                    data: critSeries,
                    borderColor: '#f85149',
                    backgroundColor: 'transparent',
                    borderDash: [2, 2], tension: 0.3,
                    pointRadius: 2, pointBackgroundColor: '#f85149',
                    borderWidth: 1.5,
                    yAxisID: 'yAlert', order: 1,
                },
            ];

            var ctx = el('_canvas').getContext('2d');
            if (chartInstance) {
                chartInstance.data.labels   = labels;
                chartInstance.data.datasets = datasets;
                chartInstance.options.scales.yPrice.ticks.color = coin.color;
                chartInstance.update('none');
            } else {
                chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: { labels: labels, datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                labels: { color: '#8b949e', font: { size: 10 }, boxWidth: 12, usePointStyle: true }
                            },
                            tooltip: {
                                backgroundColor: '#161b22',
                                borderColor: '#30363d', borderWidth: 1,
                                titleColor: '#e6edf3', bodyColor: '#8b949e',
                                callbacks: {
                                    label: function(context) {
                                        if (context.datasetIndex === 0) return ' ' + coin.sym + ' ' + fmtUSD(context.parsed.y);
                                        return ' ' + context.dataset.label + ': ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#484f58', maxTicksLimit: 7, font: { size: 9 } },
                                grid:  { color: '#21262d' },
                            },
                            yPrice: {
                                position: 'left',
                                ticks: { color: coin.color, font: { size: 9 }, callback: function(v) { return fmtUSD(v); } },
                                grid: { color: '#21262d' },
                            },
                            yAlert: {
                                position: 'right',
                                beginAtZero: true,
                                ticks: { color: '#8b949e', font: { size: 9 }, precision: 0 },
                                grid: { drawOnChartArea: false },
                            },
                        },
                    },
                });
            }

            el('_status').textContent = '\u2705 ' + new Date().toLocaleTimeString()
                + ' \u00B7 ' + currentCoin
                + ' \u00B7 ' + warnings.length + ' warn \u00B7 ' + criticals.length + ' crit';

        } catch (err) {
            console.error('[CryptoDash]', err);
            el('_status').textContent = '\u26A0 Error: ' + err.message;
        }
    }

    loadAll();
    setInterval(loadAll, 60000);
    window.addEventListener('resize', function() { if (chartInstance) chartInstance.resize(); });

})();
</script>
<?php // end getcontent ?>
