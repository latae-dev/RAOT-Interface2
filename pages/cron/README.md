# Cron Jobs for Investment Budget System

## SAP Export Cron Job

### Description
Automatically export SAP Interface data to text file for approved requests with `sap_status = 'not_synced'`.

### File
`export_sap_daily.php`

---

## Usage Methods

### Method 1: Via Cron Job (Recommended for Production)

#### Setup Cron Job
```bash
# Edit crontab
crontab -e

# Add this line to run daily at 2:00 AM
0 2 * * * /usr/bin/php /Applications/XAMPP/xamppfiles/htdocs/interface/pages/cron/export_sap_daily.php >> /Applications/XAMPP/xamppfiles/htdocs/interface/logs/cron_sap_export.log 2>&1

# Alternative: Run every 6 hours
0 */6 * * * /usr/bin/php /Applications/XAMPP/xamppfiles/htdocs/interface/pages/cron/export_sap_daily.php >> /Applications/XAMPP/xamppfiles/htdocs/interface/logs/cron_sap_export.log 2>&1
```

#### Common Cron Schedules
- `0 2 * * *` - Every day at 2:00 AM
- `0 */6 * * *` - Every 6 hours
- `*/30 * * * *` - Every 30 minutes
- `0 0 * * 0` - Every Sunday at midnight

### Method 2: Via Command Line (Manual Testing)

```bash
# Run manually
php /Applications/XAMPP/xamppfiles/htdocs/interface/pages/cron/export_sap_daily.php

# Output will show in console
```

### Method 3: Via Web Browser (For Testing)

```
http://localhost/interface/pages/cron/export_sap_daily.php?key=sap_export_2025
```

**⚠️ Security Note:** Change the secret key in the script before production use!

---

## Configuration

### Change Secret Key
Edit `export_sap_daily.php` line 16:
```php
$secret_key = 'YOUR_SECURE_RANDOM_KEY_HERE';
```

### Output Location
Files are saved to: `files/investment_budget/sap/SAP_Interface_YYYY-MM-DD_HH-MM-SS.txt`

### Log Location
Logs are saved to: `logs/cron_YYYY-MM-DD.log`

---

## What It Does

1. ✅ Connects to database
2. ✅ Fetches approved requests with `sap_status = 'not_synced'` **for current month only**
3. ✅ Generates pipe-delimited text file with SAP data
4. ✅ Saves file to `files/investment_budget/sap/` folder
5. ✅ Updates `sap_status` to `'success'` for exported records
6. ✅ Logs all activities

**Note:** Only exports data from the current month (e.g., if run on October 17, 2025, it will export data from October 1-31, 2025)

---

## Monitoring

### Check Logs
```bash
# View today's cron log
tail -f /Applications/XAMPP/xamppfiles/htdocs/interface/logs/cron_$(date +%Y-%m-%d).log

# View all cron logs
ls -lh /Applications/XAMPP/xamppfiles/htdocs/interface/logs/cron_*.log
```

### Check Exported Files
```bash
# List exported files
ls -lh /Applications/XAMPP/xamppfiles/htdocs/interface/files/investment_budget/sap/

# View latest file
tail /Applications/XAMPP/xamppfiles/htdocs/interface/files/investment_budget/sap/SAP_Interface_*.txt | head -20
```

---

## Troubleshooting

### Permission Issues
```bash
# Fix folder permissions
chmod 755 /Applications/XAMPP/xamppfiles/htdocs/interface/pages/cron/export_sap_daily.php
chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/interface/files/investment_budget/sap/
chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/interface/logs/
```

### Database Connection Issues
- Verify database credentials in `configs/database.php`
- Check if PostgreSQL is running

### No Data Exported
- Check if there are approved requests with `sap_status = 'not_synced'`
- Review filters in the script

---

## Production Deployment

1. **Change secret key** to a strong random string
2. **Set up cron job** on production server
3. **Monitor logs** regularly
4. **Archive old export files** periodically
5. **Set up alerts** for failed exports (optional)

---

## File Format

### Header
```
ศูนย์เงินทุน|เงินทุน|ขอบเขตหน้าที่|รายการภาระผูกพัน|ปี|ประเภทงบ|จำนวนเงิน|รายการ
```

### Data Row Example
```
1001|B001|21401212|A123|2025|8|150000|ชื่อรายการ...
```

---

## Support

For issues or questions, contact the development team.
