#!/usr/bin/env bash
#!/usr/bin/env bash

cd $(dirname $0)

git archive HEAD -o /tmp/typecho.zip
scp /tmp/typecho.zip self:/tmp
ssh self 'mkdir -p  /www/www.91the.top/release; mv  /www/www.91the.top/release  /www/www.91the.top/release.`date +"%Y%m%d%H%M%S"`; unzip /tmp/typecho.zip -d /www/www.91the.top/release;cp  /www/etc/typecho.ini.php /www/www.91the.top/release/config.inc.php;chown -R www /www/www.91the.top/release/'