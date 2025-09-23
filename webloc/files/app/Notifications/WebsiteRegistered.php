<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Website;

class WebsiteRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    protected $website;
    protected $verificationToken;

    public function __construct(Website $website, $verificationToken = null)
    {
        $this->website = $website;
        $this->verificationToken = $verificationToken;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' - Website Registration Successful')
            ->greeting('Welcome ' . $notifiable->name . '!')
            ->line('Congratulations! Your website has been successfully registered with ' . config('app.name') . '.')
            ->line('**Website Details:**')
            ->line('- **Name:** ' . $this->website->name)
            ->line('- **Domain:** ' . $this->website->domain)
            ->line('- **Registration Date:** ' . $this->website->created_at->format('M j, Y g:i A'));

        if ($this->website->status === 'pending_verification') {
            $message->line('## 📋 Next Steps')
                   ->line('Your website is currently pending verification. To complete the setup process, you need to verify domain ownership.');
            
            if ($this->verificationToken) {
                $message->line('**Verification Options:**')
                       ->line('1. **HTML File Upload:** Download and upload the verification file to your website root directory')
                       ->action('Download Verification File', url('/dashboard/websites/' . $this->website->id . '/verification-file'))
                       ->line('2. **DNS Record:** Add the following TXT record to your domain DNS:')
                       ->line('   - **Type:** TXT')
                       ->line('   - **Name:** _webbloc-verification')
                       ->line('   - **Value:** ' . $this->verificationToken)
                       ->line('3. **Meta Tag:** Add this meta tag to your website\'s <head> section:')
                       ->line('   ```html')
                       ->line('   <meta name="webbloc-verification" content="' . $this->verificationToken . '">')
                       ->line('   ```');
            }

            $message->action('Complete Verification', url('/dashboard/websites/' . $this->website->id . '/verify'));
        } else {
            $message->line('## 🚀 Get Started')
                   ->line('Your website is verified and ready to use! Here\'s what you can do now:')
                   ->line('✅ Generate API keys for secure access')
                   ->line('✅ Install WebBloc components (comments, reviews, authentication)')
                   ->line('✅ Customize component settings and appearance')
                   ->line('✅ View real-time statistics and analytics')
                   ->action('Go to Dashboard', url('/dashboard/websites/' . $this->website->id));
        }

        $message->line('## 🎯 Available WebBloc Components')
               ->line('- **Authentication:** User login, registration, and profile management')
               ->line('- **Comments:** Interactive comment systems for your pages')
               ->line('- **Reviews:** Customer review and rating system')
               ->line('- **Notifications:** Real-time notification system')
               ->line('- **And more:** Additional components available in your dashboard')
               ->line('## 📚 Resources')
               ->line('- [Integration Guide](' . url('/dashboard/help/integration') . ')')
               ->line('- [API Documentation](' . url('/dashboard/help/api') . ')')
               ->line('- [Component Reference](' . url('/dashboard/help/components') . ')')
               ->line('- [Support & FAQ](' . url('/dashboard/help/faq') . ')')
               ->line('## 💼 Subscription Information');

        $subscription = $this->website->subscription ?? [];
        $planName = $subscription['plan'] ?? 'Free';
        $message->line('**Current Plan:** ' . $planName);
        
        if ($planName === 'Free') {
            $message->line('**Limits:** 10,000 API requests per month, 3 WebBloc components')
                   ->line('Consider upgrading to a paid plan for higher limits and premium features.')
                   ->action('View Pricing Plans', url('/pricing'));
        }

        $message->line('If you have any questions or need assistance, our support team is here to help.')
               ->salutation('Happy building! The ' . config('app.name') . ' Team');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Website Registered Successfully',
            'message' => "Your website '{$this->website->name}' has been registered and is ready for WebBloc integration.",
            'type' => 'website_registered',
            'data' => [
                'website_id' => $this->website->id,
                'website_name' => $this->website->name,
                'website_domain' => $this->website->domain,
                'status' => $this->website->status,
                'needs_verification' => $this->website->status === 'pending_verification',
                'verification_token' => $this->verificationToken,
            ],
            'action_url' => url('/dashboard/websites/' . $this->website->id),
            'action_text' => $this->website->status === 'pending_verification' ? 'Complete Verification' : 'View Website',
            'created_at' => now(),
        ];
    }
}