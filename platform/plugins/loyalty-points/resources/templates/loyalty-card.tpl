<!DOCTYPE html>
<html lang="{{ locale }}">
<head>
<meta charset="UTF-8">
<style>
@page {
    margin: 0;
    padding: 0;
    size: 85.6mm 53.98mm;
}
body {
    margin: 0;
    padding: 3mm;
    width: 85.6mm;
    height: 53.98mm;
    font-family: {{ font_family }};
    font-size: 7pt;
    color: #1e293b;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    box-sizing: border-box;
}
.card {
    width: 100%;
    height: 100%;
    position: relative;
}
.header {
    width: 100%;
    margin-bottom: 2mm;
    padding-top: 1mm;
    padding-left: 1mm;
}
.logo {
    max-height: 5mm;
    max-width: 25mm;
}
.brand-text {
    font-weight: {{ heading_weight }};
    font-size: 9pt;
    color: #0f172a;
}
.member-level {
    font-size: 5pt;
    color: #64748b;
    margin-bottom: 2mm;
}
.member-level-value {
    color: #0f172a;
    font-weight: {{ heading_weight }};
}
.content {
    width: 100%;
}
.qr-section {
    float: left;
    width: 24mm;
    text-align: center;
}
.qr-box {
    background: #fff;
    padding: 1.5mm;
    border-radius: 1.5mm;
    display: inline-block;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.qr-code {
    width: 18mm;
    height: 18mm;
}
.scan-label {
    font-size: 5pt;
    color: #64748b;
    margin-top: 1mm;
}
.info-section {
    margin-left: 26mm;
}
.member-name {
    font-weight: {{ heading_weight }};
    font-size: 10pt;
    color: #0f172a;
    margin-bottom: 0.5mm;
}
.member-since {
    font-size: 5pt;
    color: #64748b;
}
.points-row {
    width: 100%;
    margin-bottom: 1.5mm;
}
.points-box {
    display: inline-block;
    width: 48%;
}
.points-label {
    font-size: 5pt;
    color: #64748b;
}
.points-value {
    font-size: 11pt;
    font-weight: {{ heading_weight }};
    color: #0f172a;
}
.points-secondary .points-value {
    color: #64748b;
}
.member-id {
    font-size: 5pt;
    color: #64748b;
    margin-top: 1mm;
}
.footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 5pt;
    color: #94a3b8;
}
</style>
</head>
<body>
<div class="card">
    <div class="header">
        {% if logo_url %}
        <img src="{{ logo_url }}" class="logo" alt="">
        {% else %}
        <span class="brand-text">{{ site_title }}</span>
        {% endif %}
    </div>
    <div class="content">
        <div class="qr-section">
            <div class="qr-box">
                <img src="{{ qr_code_base64 }}" class="qr-code" alt="">
            </div>
            <div class="scan-label">Scan to verify</div>
        </div>
        <div class="info-section">
            <div class="member-name">{{ customer.name }}</div>
            <div class="member-since">Member since {{ customer.member_since }}</div>
            <div class="member-level">Member Level: <span class="member-level-value">{% if level %}{{ level.name }}{% else %}{{ translations.default_member }}{% endif %}</span></div>
            <div class="points-row">
                <div class="points-box">
                    <div class="points-label">{{ translations.current_balance }}</div>
                    <div class="points-value">{{ balance.total_points }}</div>
                </div>
                <div class="points-box points-secondary">
                    <div class="points-label">{{ translations.lifetime }}</div>
                    <div class="points-value">{{ balance.lifetime_points }}</div>
                </div>
            </div>
            <div class="member-id">{{ translations.member_id }}: {{ customer.member_id }}</div>
        </div>
    </div>
    <div class="footer">{{ site_title }} Loyalty Program</div>
</div>
</body>
</html>
