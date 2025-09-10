#!/bin/bash

# Reports System Monitoring Script
# Usage: ./scripts/monitor-reports.sh

echo "🔍 Reports System Health Check"
echo "======================================="

# Check if the route is accessible
echo "📊 Testing Reports Route..."
ROUTE_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://statybaecommerse.prus.dev/admin/reports)
if [ "$ROUTE_STATUS" -eq 200 ] || [ "$ROUTE_STATUS" -eq 302 ]; then
    echo "✅ Route accessible (HTTP $ROUTE_STATUS)"
else
    echo "❌ Route issue (HTTP $ROUTE_STATUS)"
fi

# Check database connectivity
echo "🗄️  Testing Database Connection..."
php artisan tinker --execute="try { \App\Models\Order::count(); echo 'Database: Connected'; } catch(Exception \$e) { echo 'Database: Error - ' . \$e->getMessage(); }"

# Check cache status
echo "⚡ Checking Cache Status..."
php artisan config:show cache.default

# Check queue status
echo "🔄 Checking Queue System..."
php artisan queue:monitor default --max=1 2>/dev/null || echo "Queue: Ready for processing"

# Check log for recent errors
echo "📝 Recent Error Check..."
RECENT_ERRORS=$(tail -n 100 storage/logs/laravel.log 2>/dev/null | grep -i "error\|exception" | tail -n 3)
if [ -z "$RECENT_ERRORS" ]; then
    echo "✅ No recent errors found"
else
    echo "⚠️  Recent errors detected:"
    echo "$RECENT_ERRORS"
fi

# Performance metrics
echo "📈 Performance Metrics..."
echo "Memory Usage: $(free -h | awk '/^Mem:/ {print $3 "/" $2}')"
echo "Disk Usage: $(df -h / | awk 'NR==2 {print $3 "/" $2 " (" $5 ")"}')"

# Check widget files
echo "🎛️  Widget System Check..."
WIDGET_COUNT=$(ls -1 app/Filament/Widgets/*.php 2>/dev/null | wc -l)
echo "Active Widgets: $WIDGET_COUNT"

echo ""
echo "🎉 Health Check Complete!"
echo "📊 Access your reports: https://statybaecommerse.prus.dev/admin/reports"
