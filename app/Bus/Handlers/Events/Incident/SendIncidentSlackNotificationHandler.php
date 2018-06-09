<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Handlers\Events\Incident;

use CachetHQ\Cachet\Bus\Events\Incident\IncidentWasCreatedEvent;
use CachetHQ\Cachet\Integrations\Contracts\System;
use CachetHQ\Cachet\Models\Subscriber;
use Illuminate\Support\Facades\Log;
use CachetHQ\Cachet\Notifications\Incident\NewIncidentNotification;
use GuzzleHttp\Client;

class SendIncidentSlackNotificationHandler
{
    /**
     * The system instance.
     *
     * @var \CachetHQ\Cachet\Integrations\Contracts\System
     */
    protected $system;

    /**
     * The subscriber instance.
     *
     * @var \CachetHQ\Cachet\Models\Subscriber
     */
    protected $subscriber;

    /**
     * Create a new send incident email notification handler.
     *
     * @param \CachetHQ\Cachet\Integrations\Contracts\System $system
     * @param \CachetHQ\Cachet\Models\Subscriber             $subscriber
     *
     * @return void
     */
    public function __construct(System $system, Subscriber $subscriber)
    {
        $this->system = $system;
        $this->subscriber = $subscriber;
    }

    /**
     * Handle the event.
     *
     * @param \CachetHQ\Cachet\Bus\Events\Incident\IncidentWasCreatedEvent $event
     *
     * @return void
     */
    public function handle(IncidentWasCreatedEvent $event)
    {
        $incident = $event->incident;

        Log::error('In the slack new incident handler!!!!');

        $guzzle_client = new Client();
        $slack_hook = 'https://hooks.slack.com/services/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';
        $result = $guzzle_client->post($slack_hook, [
          GuzzleHttp\RequestOptions::JSON => ['text' => 'A slack post!']
        ]);

        if (!$event->notify || !$this->system->canNotifySubscribers()) {
            return false;
        }

        // Only send emails for public incidents.
        if (!$incident->visible) {
            return;
        }

        // // First notify all global subscribers.
        // $globalSubscribers = $this->subscriber->isVerified()->isGlobal()->get();
        //
        // $globalSubscribers->each(function ($subscriber) use ($incident) {
        //     $subscriber->notify(new NewIncidentNotification($incident));
        // });
        //
        // if (!$incident->component) {
        //     return;
        // }
        //
        // $notified = $globalSubscribers->pluck('id')->all();
        //
        // // Notify the remaining component specific subscribers.
        // $componentSubscribers = $this->subscriber
        //     ->isVerified()
        //     ->forComponent($incident->component->id)
        //     ->get()
        //     ->reject(function ($subscriber) use ($notified) {
        //         return in_array($subscriber->id, $notified);
        //     })->each(function ($subscriber) use ($incident) {
        //         $subscriber->notify(new NewIncidentNotification($incident));
        //     });
    }
}