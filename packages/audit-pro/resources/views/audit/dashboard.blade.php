<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Dashboard - MTS Laravel Audit Pro</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: #1a1a2e; color: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .header h1 { font-size: 24px; }
        .header p { color: #888; margin-top: 5px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #666; font-size: 14px; text-transform: uppercase; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #1a1a2e; margin-top: 5px; }
        .filters { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .filters select, .filters input { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; margin-right: 10px; }
        .filters button { padding: 8px 20px; background: #1a1a2e; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .timeline { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .timeline-item { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; gap: 15px; }
        .timeline-item:last-child { border-bottom: none; }
        .timeline-time { color: #888; font-size: 12px; min-width: 140px; }
        .timeline-actor { font-weight: 600; min-width: 150px; }
        .timeline-event { padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .event-created { background: #d4edda; color: #155724; }
        .event-updated { background: #fff3cd; color: #856404; }
        .event-deleted { background: #f8d7da; color: #721c24; }
        .event-login { background: #d1ecf1; color: #0c5460; }
        .event-custom { background: #e2e3e5; color: #383d41; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Audit Dashboard</h1>
            <p>MTS Laravel Audit Pro - Enterprise Audit Trails & Compliance Logging</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Events</h3>
                <div class="value">{{ number_format($stats['total_events'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <h3>Events Today</h3>
                <div class="value">{{ number_format($stats['events_today'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <h3>Active Actors</h3>
                <div class="value">{{ number_format($stats['active_actors'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <h3>Security Events</h3>
                <div class="value">{{ number_format($stats['security_events'] ?? 0) }}</div>
            </div>
        </div>

        <div class="filters">
            <form method="GET">
                <select name="event">
                    <option value="">All Events</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                    <option value="failed_login">Failed Login</option>
                </select>
                <input type="date" name="from" placeholder="From">
                <input type="date" name="to" placeholder="To">
                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="timeline">
            <h3 style="padding: 15px 20px; border-bottom: 1px solid #eee;">Recent Activity</h3>
            @forelse($audits ?? [] as $audit)
                <div class="timeline-item">
                    <div class="timeline-time">
                        {{ $audit->created_at?->format('M d, Y H:i:s') }}
                    </div>
                    <div class="timeline-actor">
                        {{ $audit->actor_name ?? $audit->actor_type ?? 'System' }}
                    </div>
                    <div>
                        <span class="timeline-event event-{{ $audit->event }}">
                            {{ $audit->event }}
                        </span>
                        @if($audit->auditable_type)
                            <span style="color: #666; margin-left: 8px;">
                                {{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}
                            </span>
                        @endif
                    </div>
                    <div style="color: #888; margin-left: auto;">
                        {{ $audit->ip_address }}
                    </div>
                </div>
            @empty
                <div style="padding: 40px; text-align: center; color: #888;">
                    No audit records found.
                </div>
            @endforelse
        </div>

        <div class="footer">
            Developed by <a href="https://www.magetechsol.com/" target="_blank">MageTech Solutions</a>
        </div>
    </div>
</body>
</html>
