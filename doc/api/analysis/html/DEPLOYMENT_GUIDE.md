# 🚀 City Seeders Deployment Guide

## 📋 Prerequisites

Before deploying the city seeders system, ensure you have:

- **Laravel 11+** installed and configured
- **PHP 8.3+** with required extensions
- **SQLite database** (or compatible database)
- **Composer** for dependency management
- **Countries and Zones** must be seeded first

## 🔧 Installation Steps

### 1. Database Setup

Ensure your database is properly configured in `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/your/database.sqlite
```

### 2. Run Base Seeders

First, run the essential base seeders:
```bash
php artisan migrate:fresh --seed --force
```

This will create:
- Countries table and data
- Zones table and data
- Currencies and other essential data

### 3. Run City Seeders

Execute the comprehensive city seeding:
```bash
php artisan db:seed --class=Database\\Seeders\\Cities\\AllCitiesSeeder
```

## 📊 Expected Results

After successful deployment, you should have:

- **50+ countries** with comprehensive city data
- **500+ major cities** worldwide
- **Multilingual support** (Lithuanian and English)
- **Complete global coverage** for international business

## 🔍 Verification

### Check Database Content

Verify the seeding was successful:
```bash
# Check countries count
php artisan tinker
>>> App\Models\Country::count()

# Check cities count
>>> App\Models\City::count()

# Check city translations
>>> App\Models\Translations\CityTranslation::count()
```

### Test Individual Seeders

Test specific country seeders:
```bash
# Test Austria seeder
php artisan db:seed --class=Database\\Seeders\\Cities\\AustriaCitiesSeeder

# Test Japan seeder
php artisan db:seed --class=Database\\Seeders\\Cities\\JapanCitiesSeeder
```

## 🌍 Supported Countries

### European Countries (22):
- Lithuania, Latvia, Estonia
- Poland, Germany, France, United Kingdom
- Spain, Italy, Russia, Netherlands, Belgium
- Sweden, Norway, Denmark, Finland
- Austria, Switzerland, Czech Republic, Slovakia
- Hungary, Romania, Bulgaria, Croatia, Slovenia
- Serbia, Ukraine, Belarus

### Major World Countries (28):
- Australia, Japan, China, South Korea
- Brazil, India, Mexico, Turkey
- South Africa, New Zealand, Argentina, Egypt
- Indonesia, Israel, Thailand, Vietnam
- Kenya, Malaysia, Nigeria, Philippines
- Saudi Arabia, Singapore

## 🔧 Troubleshooting

### Common Issues

1. **Database Connection Errors**
   ```bash
   # Check database file permissions
   ls -la database/database.sqlite
   
   # Recreate database if corrupted
   rm database/database.sqlite
   touch database/database.sqlite
   php artisan migrate:fresh --seed --force
   ```

2. **Missing Dependencies**
   ```bash
   # Ensure countries are seeded first
   php artisan db:seed --class=Database\\Seeders\\CountrySeeder
   php artisan db:seed --class=Database\\Seeders\\ZoneSeeder
   ```

3. **Memory Issues**
   ```bash
   # Increase PHP memory limit
   php -d memory_limit=512M artisan db:seed --class=Database\\Seeders\\Cities\\AllCitiesSeeder
   ```

### Performance Optimization

For large deployments, consider:

1. **Batch Processing**
   ```bash
   # Run seeders in smaller batches
   php artisan db:seed --class=Database\\Seeders\\Cities\\AustriaCitiesSeeder
   php artisan db:seed --class=Database\\Seeders\\Cities\\SwitzerlandCitiesSeeder
   # ... continue with other countries
   ```

2. **Database Optimization**
   ```bash
   # Optimize database after seeding
   php artisan tinker
   >>> DB::statement('VACUUM;')
   ```

## 📈 Monitoring

### Check Seeding Status

Monitor the seeding process:
```bash
# Watch for errors in real-time
php artisan db:seed --class=Database\\Seeders\\Cities\\AllCitiesSeeder --verbose
```

### Database Health

Regular maintenance:
```bash
# Check database integrity
php artisan tinker
>>> DB::select('PRAGMA integrity_check;')

# Analyze database performance
>>> DB::select('PRAGMA analyze;')
```

## 🔄 Updates and Maintenance

### Adding New Countries

To add new countries:

1. Create new seeder file:
   ```bash
   php artisan make:seeder Cities/NewCountryCitiesSeeder
   ```

2. Follow the existing pattern in other seeders
3. Add to `AllCitiesSeeder.php`
4. Test the new seeder

### Updating Existing Data

To update city data:

1. Modify the specific seeder file
2. Run the seeder again (idempotent operations)
3. Verify the changes

## 🎯 Production Deployment

### Pre-deployment Checklist

- [ ] All seeders tested individually
- [ ] Database backup created
- [ ] Performance benchmarks established
- [ ] Error handling verified
- [ ] Documentation updated

### Production Commands

```bash
# Full production deployment
php artisan migrate:fresh --seed --force
php artisan db:seed --class=Database\\Seeders\\Cities\\AllCitiesSeeder

# Warm Laravel caches (ensures config/routes/views are optimized)
./scripts/deploy.sh

# Verify deployment
php artisan tinker
>>> App\Models\City::count() // Should be 500+
```

### PHP Opcache configuration

Ensure production PHP instances run with Opcache tuned for long-running processes. Recommended settings:

```
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

After updating the ini on the server, restart PHP-FPM to apply the changes.

## 📞 Support

For issues or questions:

1. Check the troubleshooting section above
2. Review the project documentation
3. Test individual seeders to isolate issues
4. Check Laravel logs for detailed error messages

## 🏆 Success Metrics

A successful deployment should show:

- ✅ **50+ countries** with city data
- ✅ **500+ cities** successfully seeded
- ✅ **100% success rate** for all seeders
- ✅ **Multilingual support** working
- ✅ **No database errors** or corruption
- ✅ **Fast performance** (< 5 seconds per seeder)

---

**🎉 Congratulations! Your global city seeding system is now ready for production use!**
