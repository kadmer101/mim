<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\ApiKey;
use App\Models\Website;

class ApiKeyGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $apiKey;
    protected $website;
    protected $isRegenerated;

    public function __construct(ApiKey $apiKey, Website $website, $isRegenerated = false)
    {
        $this->apiKey = $apiKey;
        $this->website = $website;
        $this->isRegenerated = $isRegenerated;
    }

    public function via($notifiable)
    {
        $channels = ['mail', 'database'];
        
        // Add additional channels based on user preferences
        if ($notifiable->notification_preferences['browser'] ?? true) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $subject = $this->isRegenerated ? 
            'API Key Regenerated for ' . $this->website->name : 
            'New API Key Generated for ' . $this->website->name;

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->getActionDescription())
            ->line('**Website:** ' . $this->website->name)
            ->line('**Domain:** ' . $this->website->domain)
            ->line('**API Key ID:** ' . $this->apiKey->id);

        if ($this->isRegenerated) {
            $message->line('⚠️ **Important:** Your previous API key has been deactivated and will no longer work.')
                   ->line('Please update your website integration with the new API key as soon as possible.');
        }

        $message->action('View API Key Details', url('/dashboard/api-keys/' . $this->apiKey->id))
               ->line('**Key Details:**')
               ->line('- **Created:** ' . $this->apiKey->created_at->format('M j, Y g:i A'))
               ->line('- **Expires:** ' . ($this->apiKey->expires_at ? $this->apiKey->expires_at->format('M j, Y') : 'Never'))
               ->line('- **Rate Limit:** ' . ($this->apiKey->rate_limit ?? 'Default') . ' requests per hour')
               ->line('- **Allowed Domains:** ' . ($this->apiKey->allowed_domains ? implode(', ', $this->apiKey->allowed_domains) : 'Any'));

        if (!$this->isRegenerated) {
            $message->line('## Getting Started')
                   ->line('To start using your API key, add the following script to your website:')
                   ->line('```html')
                   ->line('<script src="' . config('webbloc.cdn.base_url') . '/webbloc.min.js" data-api-key="' . $this->apiKey->key . '"></script>')
                   ->line('```')
                   ->line('For detailed integration instructions, visit our [documentation](' . url('/dashboard/help/integration') . ').');
        }

        $message->line('If you did not request this API key generation, please contact our support team immediately.')
               ->salutation('Best regards, The ' . config('app.name') . ' Team');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->isRegenerated ? 'API Key Regenerated' : 'New API Key Generated',
            'message' => $this->getActionDescription(),
            'type' => 'api_key_generated',
            'data' => [
                'api_key_id' => $this->apiKey->id,
                'website_id' => $this->website->id,
                'website_name' => $this->website->name,
                'website_domain' => $this->website->domain,
                'is_regenerated' => $this->isRegenerated,
                'key_preview' => substr($this->apiKey->key, 0, 8) . '...',
                'expires_at' => $this->apiKey->expires_at?->toISOString(),
                'rate_limit' => $this->apiKey->rate_limit,
            ],
            'action_url' => url('/dashboard/api-keys/' . $this->apiKey->id),
            'action_text' => 'View Details',
            'created_at' => now(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'title' => $this->isRegenerated ? 'API Key Regenerated' : 'New API Key Generated',
            'message' => $this->getActionDescription(),
            'type' => 'success',
            'data' => [
                'api_key_id' => $this->apiKey->id,
                'website_name' => $this->website->name,
                'is_regenerated' => $this->isRegenerated,
            ],
        ];
    }

    protected function getActionDescription()
    {
        if ($this->isRegenerated) {
            return "Your API key for website '{$this->website->name}' has been successfully regenerated. The new key is now active and ready to use.";
        }

        return "A new API key has been generated for your website '{$this->website->name}'. You can now integrate dynamic WebBloc components into your site.";
    }

    public function shouldSend($notifiable, $channel)
    {
        // Don't send email if user has disabled API key notifications
        if ($channel === 'mail' && !($notifiable->notification_preferences['api_keys_email'] ?? true)) {
            return false;
        }

        return true;
    }
}