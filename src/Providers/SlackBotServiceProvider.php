<?php
declare(strict_types=1);

namespace Nwilging\LaravelSlackBot\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Config\Repository as Config;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Nwilging\LaravelSlackBot\Channels\SlackNotificationChannel;
use Nwilging\LaravelSlackBot\Contracts\Channels\SlackNotificationChannelContract;
use Nwilging\LaravelSlackBot\Contracts\SlackApiServiceContract;
use Nwilging\LaravelSlackBot\Contracts\Support\LayoutBuilder\BuilderContract;
use Nwilging\LaravelSlackBot\Services\SlackApiService;
use Nwilging\LaravelSlackBot\Support\LayoutBuilder\Builder;

class SlackBotServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/slack.php', 'slack');
    }

    public function register()
    {
        Notification::resolved(function (ChannelManager $channelManager): void {
            $channelManager->extend('slack', function (): SlackNotificationChannelContract {
                return $this->app->make(SlackNotificationChannelContract::class);
            });
        });

        $this->app->bind(ClientInterface::class, Client::class);
        $this->app->bind(SlackApiServiceContract::class, SlackApiService::class);

        $this->app->when(SlackApiService::class)->needs('$botToken')->give(function (): string {
            return $this->app->make(Config::class)->get('slack.bot_token');
        });

        $this->app->when(SlackApiService::class)->needs('$apiUrl')->give(function (): string {
            return $this->app->make(Config::class)->get('slack.api_url');
        });

        $this->app->bind(BuilderContract::class, Builder::class);

        $this->app->bind(SlackNotificationChannelContract::class, SlackNotificationChannel::class);
    }
}
