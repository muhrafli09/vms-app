<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Illuminate\Support\Facades\Config;

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
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->simple()
                ->labels([
                    'admin' => 'Admin Panel',
                    'app' => 'App Dashboard',
                ])
                ->visible(fn (): bool => auth()->user()?->admin())
                ->canSwitchPanels(fn (): bool => auth()->user()?->admin())
                ->renderHook('panels::user-menu.before');
        });
        
        // Load mail settings from database
        try {
            $mailSettings = app(\App\Settings\MailSettings::class);
            
            Config::set('mail.default', $mailSettings->mail_driver);
            Config::set('mail.from.address', $mailSettings->mail_from_address);
            Config::set('mail.from.name', $mailSettings->mail_from_name);
            Config::set('mail.mailers.smtp.host', $mailSettings->mail_host);
            Config::set('mail.mailers.smtp.port', $mailSettings->mail_port);
            Config::set('mail.mailers.smtp.encryption', $mailSettings->mail_encryption);
            Config::set('mail.mailers.smtp.username', $mailSettings->mail_username);
            Config::set('mail.mailers.smtp.password', $mailSettings->mail_password);
        } catch (\Exception $e) {
            // Settings not yet configured, use .env defaults
        }
    }
}
