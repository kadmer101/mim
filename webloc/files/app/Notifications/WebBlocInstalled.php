<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Website;
use App\Models\WebBloc;
use App\Models\WebBlocInstance;

class WebBlocInstalled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $website;
    protected $webBloc;
    protected $instance;
    protected $isUpdate;

    public function __construct(Website $website, WebBloc $webBloc, WebBlocInstance $instance, $isUpdate = false)
    {
        $this->website = $website;
        $this->webBloc = $webBloc;
        $this->instance = $instance;
        $this->isUpdate = $isUpdate;
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        
        // Only send email for major installations, not updates
        if (!$this->isUpdate && ($notifiable->notification_preferences['webbloc_email'] ?? true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $componentName = ucfirst($this->webBloc->type);
        
        $message = (new MailMessage)
            ->subject($componentName . ' WebBloc Installed Successfully')
            ->greeting('Great news, ' . $notifiable->name . '!')
            ->line("The {$componentName} WebBloc has been successfully installed on your website '{$this->website->name}'.")
            ->line('**Installation Details:**')
            ->line('- **Component:** ' . $componentName . ' (' . $this->webBloc->version . ')')
            ->line('- **Website:** ' . $this->website->name)
            ->line('- **Domain:** ' . $this->website->domain)
            ->line('- **Installed:** ' . $this->instance->created_at->format('M j, Y g:i A'));

        // Add component-specific information
        $this->addComponentDetails($message);

        $message->line('## 🔗 Integration')
               ->line('To use this component on your website, add the following HTML code where you want it to appear:');

        $integrationCode = $this->generateIntegrationCode();
        $message->line('```html')
               ->line($integrationCode)
               ->line('```');

        $message->action('View Installation Details', url('/dashboard/websites/' . $this->website->id . '/webblocs'))
               ->line('## ⚙️ Configuration')
               ->line('You can customize this WebBloc\'s settings, appearance, and behavior from your dashboard.')
               ->line('Available configuration options:');

        // Add configuration options based on component type
        $this->addConfigurationOptions($message);

        $message->action('Customize Settings', url('/dashboard/websites/' . $this->website->id . '/webblocs/' . $this->instance->id . '/edit'))
               ->line('## 📊 Monitoring')
               ->line('Track usage, performance, and user engagement with detailed analytics available in your dashboard.')
               ->action('View Statistics', url('/dashboard/statistics?website=' . $this->website->id . '&component=' . $this->webBloc->type))
               ->salutation('Keep building amazing experiences! The ' . config('app.name') . ' Team');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->isUpdate ? 'WebBloc Updated' : 'WebBloc Installed',
            'message' => $this->getNotificationMessage(),
            'type' => $this->isUpdate ? 'webbloc_updated' : 'webbloc_installed',
            'data' => [
                'website_id' => $this->website->id,
                'website_name' => $this->website->name,
                'webbloc_id' => $this->webBloc->id,
                'webbloc_type' => $this->webBloc->type,
                'webbloc_version' => $this->webBloc->version,
                'instance_id' => $this->instance->id,
                'is_update' => $this->isUpdate,
                'installation_settings' => $this->instance->settings,
                'integration_code' => $this->generateIntegrationCode(),
            ],
            'action_url' => url('/dashboard/websites/' . $this->website->id . '/webblocs'),
            'action_text' => 'View WebBlocs',
            'created_at' => now(),
        ];
    }

    protected function getNotificationMessage()
    {
        $componentName = ucfirst($this->webBloc->type);
        
        if ($this->isUpdate) {
            return "The {$componentName} WebBloc on '{$this->website->name}' has been updated to version {$this->webBloc->version}.";
        }

        return "The {$componentName} WebBloc has been successfully installed on '{$this->website->name}' and is ready to use.";
    }

    protected function addComponentDetails(MailMessage $message)
    {
        switch ($this->webBloc->type) {
            case 'comments':
                $message->line('**Features Enabled:**')
                       ->line('✅ User commenting system')
                       ->line('✅ Reply threads and nested comments')
                       ->line('✅ Comment moderation and spam protection')
                       ->line('✅ Real-time updates')
                       ->line('✅ Emoji reactions and voting');
                break;

            case 'reviews':
                $message->line('**Features Enabled:**')
                       ->line('✅ Customer review collection')
                       ->line('✅ Star rating system')
                       ->line('✅ Review verification and moderation')
                       ->line('✅ Photo and video attachments')
                       ->line('✅ Review analytics and insights');
                break;

            case 'auth':
                $message->line('**Features Enabled:**')
                       ->line('✅ User registration and login')
                       ->line('✅ Password reset functionality')
                       ->line('✅ User profile management')
                       ->line('✅ Social login integration')
                       ->line('✅ Session management');
                break;

            case 'notifications':
                $message->line('**Features Enabled:**')
                       ->line('✅ Real-time notifications')
                       ->line('✅ Multiple notification types')
                       ->line('✅ Desktop and mobile support')
                       ->line('✅ Customizable notification center')
                       ->line('✅ Email and browser notifications');
                break;

            default:
                $message->line('**Component Type:** Custom WebBloc')
                       ->line('**Description:** ' . ($this->webBloc->description ?? 'No description available'));
        }
    }

    protected function addConfigurationOptions(MailMessage $message)
    {
        $settings = $this->instance->settings ?? [];
        
        switch ($this->webBloc->type) {
            case 'comments':
                $message->line('- Comment approval settings (automatic/manual/none)')
                       ->line('- Guest commenting permissions')
                       ->line('- Nested reply depth limits')
                       ->line('- Spam filtering and moderation rules')
                       ->line('- Display options and themes');
                break;

            case 'reviews':
                $message->line('- Review verification requirements')
                       ->line('- Rating scale configuration (1-5, 1-10)')
                       ->line('- Required/optional review fields')
                       ->line('- Media upload settings')
                       ->line('- Display templates and sorting options');
                break;

            case 'auth':
                $message->line('- Registration field requirements')
                       ->line('- Password complexity rules')
                       ->line('- Email verification settings')
                       ->line('- Social login providers')
                       ->line('- User profile customization');
                break;

            case 'notifications':
                $message->line('- Notification delivery methods')
                       ->line('- Auto-dismiss and persistence settings')
                       ->line('- Sound and visual preferences')
                       ->line('- Notification categories and priorities')
                       ->line('- Display position and styling');
                break;

            default:
                $message->line('- Custom component configuration available in dashboard');
        }
    }

    protected function generateIntegrationCode()
    {
        $settings = $this->instance->settings ?? [];
        $attributes = [];

        // Add common attributes
        if (!empty($settings['limit'])) {
            $attributes['limit'] = $settings['limit'];
        }

        if (!empty($settings['theme'])) {
            $attributes['theme'] = $settings['theme'];
        }

        // Add type-specific attributes
        switch ($this->webBloc->type) {
            case 'comments':
                if (!empty($settings['allow_guest'])) {
                    $attributes['guest'] = 'true';
                }
                if (!empty($settings['max_depth'])) {
                    $attributes['max-depth'] = $settings['max_depth'];
                }
                break;

            case 'reviews':
                if (!empty($settings['require_purchase'])) {
                    $attributes['verified-only'] = 'true';
                }
                if (!empty($settings['rating_scale'])) {
                    $attributes['rating-scale'] = $settings['rating_scale'];
                }
                break;

            case 'auth':
                $mode = $settings['mode'] ?? 'modal';
                $attributes['mode'] = $mode;
                if (!empty($settings['redirect_after_login'])) {
                    $attributes['redirect'] = $settings['redirect_after_login'];
                }
                break;
        }

        $attributeString = '';
        if (!empty($attributes)) {
            $attributeString = " w2030b_tags='" . json_encode($attributes) . "'";
        }

        return '<div w2030b="' . $this->webBloc->type . '"' . $attributeString . '>Loading...</div>';
    }
}