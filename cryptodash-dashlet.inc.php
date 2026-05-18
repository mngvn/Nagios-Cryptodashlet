<?php
/**
 * CryptoDash Dashlet for Nagios XI
 * Displays live cryptocurrency prices alongside Nagios XI warning/critical alert counts.
 *
 * MIT License — Copyright (c) 2025
 * Author: Custom Dashlet
 *
 * INSTALL:
 *   1. Copy this folder to:
 *        /usr/local/nagiosxi/html/includes/dashlets/cryptodash-dashlet/
 *   2. Open ajax.php and set your $apiKey and $baseUrl (lines ~40-41).
 *   3. Log into Nagios XI → Admin → Manage Dashlets → register if needed.
 *   4. Add the dashlet to any dashboard.
 */

include_once(dirname(__FILE__) . '/../dashlethelper.inc.php');

cryptodash_dashlet_init();

function cryptodash_dashlet_init()
{
    $name = "cryptodash-dashlet";

    $args = array(
        DASHLET_NAME        => $name,
        DASHLET_VERSION     => "1.0.0",
        DASHLET_DATE        => "2025-01-01",
        DASHLET_AUTHOR      => "Custom",
        DASHLET_DESCRIPTION => _("Live cryptocurrency prices overlaid with Nagios XI warning & critical alert counts."),
        DASHLET_COPYRIGHT   => "Copyright (c) 2025",
        DASHLET_LICENSE     => "MIT",
        DASHLET_HOMEPAGE    => "https://www.nagios.com",
        DASHLET_FUNCTION    => "cryptodash_dashlet_func",
        DASHLET_REFRESHRATE => 60,
        DASHLET_TITLE       => _("CryptoDash"),
        DASHLET_WIDTH       => "520",
        DASHLET_HEIGHT      => "380",
        DASHLET_OPACITY     => "1.0",
    );

    register_dashlet($name, $args);
}

function cryptodash_dashlet_func($mode = DASHLET_MODE_PREVIEW, $id = "", $args = null)
{
    $output = "";
    $base_url = get_base_url() . "includes/dashlets/cryptodash-dashlet/";

    switch ($mode) {

        case DASHLET_MODE_GETCONFIGHTML:
            // Optional future config — coin selector, alert type default, etc.
            $output = '';
            break;

        case DASHLET_MODE_OUTBOARD:
        case DASHLET_MODE_INBOARD:
            if ($id == "") {
                $id = "cryptodash_" . random_string(8);
            }

            $nsp = get_nagios_session_protector_id();

            $output .= "
            <div id='{$id}' style='width:100%;height:100%;'></div>
            <script type='text/javascript'>
                (function() {
                    function loadCryptoDash_{$id}() {
                        var url = '{$base_url}ajax.php?mode=getcontent&container={$id}&nsp={$nsp}';
                        jQuery('#{$id}').load(url);
                    }
                    loadCryptoDash_{$id}();
                    // Auto-refresh every 60 seconds (ajax.php also has its own JS interval)
                    setInterval(loadCryptoDash_{$id}, 60000);
                })();
            </script>";
            break;

        case DASHLET_MODE_PREVIEW:
            $output = "
                <div style='padding:16px; font-family:\"Courier New\",monospace; text-align:center;
                            background:linear-gradient(135deg,#0d1117 0%,#161b22 100%);
                            border-radius:8px; color:#e6edf3;'>
                    <div style='font-size:22px; font-weight:bold; color:#58a6ff; margin-bottom:6px;'>
                        ₿ CryptoDash
                    </div>
                    <div style='font-size:12px; color:#8b949e; margin-bottom:12px;'>
                        Live crypto prices vs Nagios XI alerts
                    </div>
                    <div style='display:flex; justify-content:center; gap:12px; margin-bottom:14px;'>
                        <div style='background:#21262d; padding:6px 14px; border-radius:6px; border:1px solid #30363d;'>
                            <span style='color:#f0c040;'>BTC</span> <span style='color:#58a6ff;'>$103,200</span>
                        </div>
                        <div style='background:#21262d; padding:6px 14px; border-radius:6px; border:1px solid #30363d;'>
                            <span style='color:#a371f7;'>ETH</span> <span style='color:#58a6ff;'>$3,840</span>
                        </div>
                        <div style='background:#21262d; padding:6px 14px; border-radius:6px; border:1px solid #30363d;'>
                            <span style='color:#3fb950;'>DOGE</span> <span style='color:#58a6ff;'>$0.38</span>
                        </div>
                    </div>
                    <div style='display:flex; justify-content:center; gap:12px;'>
                        <div style='background:#21262d; padding:6px 14px; border-radius:6px; border:1px solid #f0883e44;'>
                            ⚠️ <span style='color:#f0883e;'>12 Warnings</span>
                        </div>
                        <div style='background:#21262d; padding:6px 14px; border-radius:6px; border:1px solid #f8514944;'>
                            🔥 <span style='color:#f85149;'>3 Criticals</span>
                        </div>
                    </div>
                    <div style='margin-top:12px; font-size:10px; color:#484f58;'>
                        Configure API key in ajax.php
                    </div>
                </div>";
            break;
    }

    return $output;
}
?>
