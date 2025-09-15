<?php

namespace App\Providers;

use App\Exceptions\GeneralException;
use App\Helpers\Profiler\DbLogger;
use App\Models\Task;
use App\Policies\TaskPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the database logger
        $this->app->scoped(DbLogger::class, function ($app) {
            return new DbLogger;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $isProduction = app()->isProduction();

        // Prohibit destructive commands
        DB::prohibitDestructiveCommands($isProduction);

        if (env('LOG_SQL', false) && ! app()->runningInConsole() && ! $isProduction) {
            (new DbLogger)->record();
        }

        // Prohibit lazy loading
        Model::shouldBeStrict(! $isProduction);
        Model::unguard();
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
            $class = $model::class;

            throw new GeneralException("Attempted to lazy load [{$relation}] on model [{$class}].");
        });

        // Rate Limiting
        RateLimiter::for('auth', function () {
            return Limit::perMinute(30);
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        // Gate policies
        Gate::policy(Task::class, TaskPolicy::class);
    }
}
