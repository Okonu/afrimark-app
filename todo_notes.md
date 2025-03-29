Supervisor COnfiguration:
```
[program:laravel-default-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=forge
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/supervisor-default-queue.log
stopwaitsecs=3600

[program:laravel-notifications-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --queue=notifications --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=forge
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/supervisor-notifications-queue.log
stopwaitsecs=3600

[program:laravel-document-processing-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --queue=document-processing --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=forge
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/supervisor-document-processing-queue.log
stopwaitsecs=3600

[group:laravel-queues]
programs=laravel-default-queue,laravel-notifications-queue,laravel-document-processing-queue
```
