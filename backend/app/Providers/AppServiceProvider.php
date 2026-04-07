<?php

namespace App\Providers;

use App\Models\Club;
use App\Models\Evenement;
use App\Models\Equipe;
use App\Models\User;
use App\Policies\ClubPolicy;
use App\Policies\EvenementPolicy;
use App\Policies\EquipePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Club::class, ClubPolicy::class);
        Gate::policy(Equipe::class, EquipePolicy::class);
        Gate::policy(Evenement::class, EvenementPolicy::class);
    }
}
