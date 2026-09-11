# Spendly

Spendly is a Laravel and Livewire expense tracker.

## Recurring transaction runtime

Recurring transactions are processed by Laravel's scheduler every minute. A normal web request does not run this automation. Production must invoke `php artisan schedule:run` every minute, or run `php artisan schedule:work` under a supervised long-running process.

Recurring database notifications implement Laravel's queued notification contract, so production must also run a queue worker such as `php artisan queue:work`.

The release-readiness deployment pass must configure supervised scheduler and queue-worker processes, a safe worker restart/retry strategy, failed-job handling, and application logging/monitoring. These runtime processes are required in production but are intentionally outside the Settings implementation.

For local development, the Composer development command starts the web application, queue worker, frontend server, and scheduler together:

```shell
composer run dev
```

When the other services are already running separately, start only the scheduler with `php artisan schedule:work`.

You can inspect registered tasks with `php artisan schedule:list` and manually process due schedules with `php artisan transactions:generate-recurring`.

The recurring processor handles at most one overdue occurrence per schedule during each minute. Repeated scheduler runs catch up sequentially. This prevents an old schedule from creating an unbounded number of financial transactions in one run.
