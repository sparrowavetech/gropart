<div class="mb-3">
    <div class="btn-list">
        <a class="btn {{ request()->routeIs('india-sms.index') ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('india-sms.index') }}"><i class="ti ti-layout-dashboard me-1"></i>Overview</a>
        <a class="btn {{ request()->routeIs('india-sms.logs.*') ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('india-sms.logs.index') }}"><i class="ti ti-list-details me-1"></i>Logs</a>
        <a class="btn {{ request()->routeIs('india-sms.templates.*') ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('india-sms.templates.index') }}"><i class="ti ti-template me-1"></i>Templates</a>
        <a class="btn {{ request()->routeIs('india-sms.otps.*') ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('india-sms.otps.index') }}"><i class="ti ti-shield-lock me-1"></i>OTP</a>
        <a class="btn {{ request()->routeIs('india-sms.gateways.*') ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('india-sms.gateways.index') }}"><i class="ti ti-plug-connected me-1"></i>Gateways</a>
        <a class="btn {{ request()->routeIs('india-sms.admin-notifications*') ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('india-sms.admin-notifications') }}"><i class="ti ti-bell-ringing me-1"></i>Admin SMS</a>
        <a class="btn {{ request()->routeIs('india-sms.settings*') ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('india-sms.settings') }}"><i class="ti ti-settings me-1"></i>Settings</a>
        <a class="btn {{ request()->routeIs('india-sms.activation.*') ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('india-sms.activation.index') }}"><i class="ti ti-key me-1"></i>License</a>
    </div>
</div>
