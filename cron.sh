# Development with logging
#!/bin/bash
cd /home/u341967117/domains/mondiison.16mb.com/public_html/smartrent || exit
/usr/bin/php artisan schedule:run >> storage/logs/scheduler.log 2>&1

# # Production
# #!/bin/bash
# cd /home/u341967117/domains/mondiison.16mb.com/public_html/smartrent || exit
# /usr/bin/php artisan schedule:run >> /dev/null 2>&1