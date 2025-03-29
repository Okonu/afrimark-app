
## Jobs TODO, setup reminder/guide

It's about:

1. notification classes using queues
2. Setting up dedicated job classes for document processing
3. Configuring the queues
4. Setting up process monitoring with Supervisor

```
QUEUE_CONNECTION=database
# or for production:
# QUEUE_CONNECTION=redis
```

For development, run:
```php artisan queue:work --queue=notifications,document-processing,default
```

For production, use Supervisor with the provided configuration file.

Update PHP settings:
```
max_execution_time = 120
memory_limit = 256M
```

Monitor the queues with:

```php artisan queue:monitor
php artisan queue:failed  # List failed jobs
```
Set up logging to track queue processing:
```
tail -f storage/logs/laravel.log
```
