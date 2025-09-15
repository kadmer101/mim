# DIRECTORY_STRUCTURE.md

# Mim Platform - Complete Directory Structure

*Production-ready Laravel SaaS application file organization*

---

## 📁 Root Directory Structure

```
mim-platform/
├── app/                          # Core application logic
├── bootstrap/                    # Laravel bootstrap files
├── config/                       # Configuration files
├── database/                     # Database related files
├── public/                       # Web server document root
├── resources/                    # Views, assets, and language files
├── routes/                       # Application routes
├── storage/                      # File storage and logs
├── tests/                        # Automated tests
├── vendor/                       # Composer dependencies
├── .env                         # Environment configuration
├── .env.example                 # Environment template
├── .gitignore                   # Git ignore rules
├── artisan                      # Laravel command-line interface
├── composer.json                # PHP dependencies
├── composer.lock               # Locked dependencies
├── package.json                # Node.js dependencies
├── package-lock.json           # Locked Node dependencies
├── phpunit.xml                 # PHPUnit configuration
├── README.md                   # Project documentation
├── tailwind.config.js          # Tailwind CSS configuration
├── vite.config.js              # Vite build configuration
└── webpack.mix.js              # Laravel Mix configuration
```

---

## 🧠 App Directory Structure

### Core Application (`app/`)

```
app/
├── Console/                     # Artisan commands
│   ├── Commands/
│   │   ├── CalculateReputationCommand.php      # Daily reputation calculation
│   │   ├── ProcessSubscriptionRenewalsCommand.php # Handle subscription renewals
│   │   ├── SendRewardNotificationsCommand.php   # Reward notification system
│   │   ├── UpdateLeaderboardsCommand.php        # Update leaderboards
│   │   ├── CleanupOldLogsCommand.php           # Database cleanup
│   │   ├── SyncBinanceTransactionsCommand.php  # Sync payment status
│   │   └── GenerateStatisticsCommand.php       # Generate daily stats
│   └── Kernel.php              # Command scheduling
│
├── Events/                      # Event classes
│   ├── ChallengeSubmitted.php  # Challenge submission event
│   ├── ChallengeResolved.php   # Challenge resolution event
│   ├── RewardAwarded.php       # Reward distribution event
│   ├── UserSubscribed.php      # Subscription activation event
│   ├── ReputationUpdated.php   # Reputation change event
│   ├── BadgeEarned.php         # Achievement unlocked event
│   └── PaymentProcessed.php    # Payment completion event
│
├── Exceptions/                  # Custom exception handlers
│   ├── Handler.php             # Global exception handler
│   ├── PaymentException.php    # Payment processing errors
│   ├── SubscriptionException.php # Subscription related errors
│   ├── RewardException.php     # Reward distribution errors
│   └── ValidationException.php # Custom validation errors
│
├── Http/                       # HTTP layer
│   ├── Controllers/            # Application controllers
│   │   ├── Auth/              # Authentication controllers
│   │   │   ├── AuthenticatedSessionController.php
│   │   │   ├── ConfirmablePasswordController.php
│   │   │   ├── EmailVerificationNotificationController.php
│   │   │   ├── EmailVerificationPromptController.php
│   │   │   ├── NewPasswordController.php
│   │   │   ├── PasswordController.php
│   │   │   ├── PasswordResetLinkController.php
│   │   │   ├── RegisteredUserController.php
│   │   │   ├── TwoFactorAuthController.php
│   │   │   └── VerifyEmailController.php
│   │   │
│   │   ├── Admin/              # Admin panel controllers
│   │   │   ├── DashboardController.php         # Admin dashboard
│   │   │   ├── UserManagementController.php    # User administration
│   │   │   ├── ChallengeManagementController.php # Challenge moderation
│   │   │   ├── PaymentManagementController.php  # Payment oversight
│   │   │   ├── RewardManagementController.php   # Reward distribution
│   │   │   ├── SettingsController.php          # System settings
│   │   │   ├── AnalyticsController.php         # Platform analytics
│   │   │   ├── AuditLogController.php          # Activity monitoring
│   │   │   └── SystemHealthController.php      # System status
│   │   │
│   │   ├── Api/                # API controllers
│   │   │   ├── V1/
│   │   │   │   ├── AuthController.php          # API authentication
│   │   │   │   ├── ChallengeController.php     # Challenge API
│   │   │   │   ├── ResponseController.php      # Response API
│   │   │   │   ├── UserController.php          # User management API
│   │   │   │   ├── PaymentController.php       # Payment API
│   │   │   │   ├── ReputationController.php    # Reputation API
│   │   │   │   └── NotificationController.php  # Notification API
│   │   │   └── BaseController.php              # Base API controller
│   │   │
│   │   ├── ChallengeController.php             # Main challenge handling
│   │   ├── ResponseController.php              # Challenge responses
│   │   ├── DashboardController.php             # User dashboard
│   │   ├── ProfileController.php               # User profiles
│   │   ├── SubscriptionController.php          # Subscription management
│   │   ├── PaymentController.php               # Payment processing
│   │   ├── WalletController.php                # Wallet management
│   │   ├── RewardController.php                # Reward claims
│   │   ├── LeaderboardController.php           # Leaderboards
│   │   ├── ReputationController.php            # Reputation system
│   │   ├── NotificationController.php          # Notifications
│   │   ├── SearchController.php                # Search functionality
│   │   ├── FaqController.php                   # FAQ system
│   │   ├── PageController.php                  # Static pages
│   │   └── Controller.php                      # Base controller
│   │
│   ├── Middleware/              # Custom middleware
│   │   ├── Authenticate.php     # Laravel default
│   │   ├── EncryptCookies.php   # Laravel default
│   │   ├── PreventRequestsDuringMaintenance.php
│   │   ├── RedirectIfAuthenticated.php
│   │   ├── TrimStrings.php      # Laravel default
│   │   ├── TrustHosts.php       # Laravel default
│   │   ├── TrustProxies.php     # Laravel default
│   │   ├── ValidateSignature.php # Laravel default
│   │   ├── VerifyCsrfToken.php  # Laravel default
│   │   ├── CheckSubscription.php # Verify active subscription
│   │   ├── EnsureUserIsExpert.php # Expert role verification
│   │   ├── EnsureUserIsAdmin.php # Admin role verification
│   │   ├── RateLimitApi.php     # API rate limiting
│   │   ├── LogUserActivity.php  # Activity logging
│   │   ├── CheckMaintenanceMode.php # Maintenance mode
│   │   ├── LocalizationMiddleware.php # Language switching
│   │   └── SecurityHeaders.php  # Security headers
│   │
│   ├── Requests/                # Form request validation
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php
│   │   │   ├── RegisterRequest.php
│   │   │   └── TwoFactorRequest.php
│   │   │
│   │   ├── Challenge/
│   │   │   ├── StoreChallengeRequest.php
│   │   │   ├── UpdateChallengeRequest.php
│   │   │   └── ResolveChallengeRequest.php
│   │   │
│   │   ├── Response/
│   │   │   ├── StoreResponseRequest.php
│   │   │   └── UpdateResponseRequest.php
│   │   │
│   │   ├── Payment/
│   │   │   ├── ProcessPaymentRequest.php
│   │   │   └── ClaimRewardRequest.php
│   │   │
│   │   ├── User/
│   │   │   ├── UpdateProfileRequest.php
│   │   │   └── UpdatePreferencesRequest.php
│   │   │
│   │   └── Admin/
│   │       ├── ManageUserRequest.php
│   │       └── SystemSettingsRequest.php
│   │
│   └── Resources/               # API resources
│       ├── ChallengeResource.php # Challenge API response
│       ├── ResponseResource.php  # Response API response
│       ├── UserResource.php      # User API response
│       ├── PaymentResource.php   # Payment API response
│       ├── ReputationResource.php # Reputation API response
│       └── Collections/
│           ├── ChallengeCollection.php
│           ├── ResponseCollection.php
│           └── UserCollection.php
│
├── Jobs/                        # Queued jobs
│   ├── ProcessPaymentJob.php    # Payment processing
│   ├── SendRewardJob.php        # Reward distribution
│   ├── UpdateReputationJob.php  # Reputation calculations
│   ├── SendNotificationJob.php  # Notification delivery
│   ├── GenerateReportJob.php    # Report generation
│   ├── SyncBinanceJob.php       # Binance API sync
│   └── CleanupExpiredDataJob.php # Data cleanup
│
├── Listeners/                   # Event listeners
│   ├── UpdateUserReputation.php # Handle reputation changes
│   ├── SendChallengeNotification.php # Challenge notifications
│   ├── ProcessRewardPayment.php  # Reward processing
│   ├── UpdateLeaderboards.php    # Leaderboard updates
│   ├── AwardBadges.php          # Badge achievements
│   └── LogUserActivity.php      # Activity logging
│
├── Mail/                        # Email templates
│   ├── ChallengeSubmitted.php   # Challenge submission email
│   ├── ChallengeResolved.php    # Challenge resolution email
│   ├── RewardAwarded.php        # Reward notification email
│   ├── SubscriptionExpiring.php # Subscription reminder
│   ├── SubscriptionRenewed.php  # Renewal confirmation
│   ├── PaymentReceived.php      # Payment confirmation
│   ├── WelcomeUser.php          # Welcome email
│   └── ExpertAssignment.php     # Expert assignment email
│
├── Models/                      # Eloquent models
│   ├── User.php                 # User model
│   ├── Challenge.php            # Challenge model
│   ├── ChallengeResponse.php    # Challenge response model
│   ├── ChallengeVote.php        # Vote model
│   ├── ChallengeAssignment.php  # Expert assignment model
│   ├── ChallengeMedia.php       # Challenge media model
│   ├── Category.php             # Category model
│   ├── UserSubscription.php     # Subscription model
│   ├── WalletAddress.php        # Wallet model
│   ├── PaymentTransaction.php   # Payment transaction model
│   ├── RewardClaim.php          # Reward claim model
│   ├── ReputationPoint.php      # Reputation model
│   ├── UserReputationSummary.php # Reputation summary model
│   ├── AchievementBadge.php     # Achievement badge model
│   ├── UserAchievementBadge.php # User badge model
│   ├── Leaderboard.php          # Leaderboard model
│   ├── UserStreak.php           # Streak model
│   ├── NotificationPreference.php # Notification settings model
│   ├── PushNotificationToken.php # Push token model
│   ├── Page.php                 # Static page model
│   ├── Faq.php                  # FAQ model
│   ├── Announcement.php         # Announcement model
│   ├── UserAnnouncementDismissal.php # Dismissal model
│   ├── PlatformStatistic.php    # Statistics model
│   ├── UserActivityLog.php      # Activity log model
│   ├── ApiUsageLog.php          # API usage model
│   ├── Setting.php              # Settings model
│   └── Traits/
│       ├── HasUuid.php          # UUID trait
│       ├── HasSlug.php          # Slug generation trait
│       ├── HasVotes.php         # Voteable trait
│       ├── HasMedia.php         # Media handling trait
│       ├── HasReputation.php    # Reputation trait
│       ├── Searchable.php       # Search trait
│       └── ActivityLoggable.php # Activity logging trait
│
├── Notifications/               # Notification classes
│   ├── ChallengeSubmittedNotification.php
│   ├── ChallengeResolvedNotification.php
│   ├── ResponsePostedNotification.php
│   ├── RewardAwardedNotification.php
│   ├── SubscriptionExpiringNotification.php
│   ├── ExpertAssignmentNotification.php
│   ├── BadgeEarnedNotification.php
│   ├── ReputationMilestoneNotification.php
│   └── SystemMaintenanceNotification.php
│
├── Observers/                   # Model observers
│   ├── ChallengeObserver.php    # Challenge lifecycle events
│   ├── ResponseObserver.php     # Response events
│   ├── UserObserver.php         # User events
│   ├── PaymentObserver.php      # Payment events
│   └── VoteObserver.php         # Voting events
│
├── Policies/                    # Authorization policies
│   ├── ChallengePolicy.php      # Challenge permissions
│   ├── ResponsePolicy.php       # Response permissions
│   ├── UserPolicy.php           # User permissions
│   ├── AdminPolicy.php          # Admin permissions
│   └── PaymentPolicy.php        # Payment permissions
│
├── Providers/                   # Service providers
│   ├── AppServiceProvider.php   # Main app provider
│   ├── AuthServiceProvider.php  # Authentication provider
│   ├── BroadcastServiceProvider.php # Broadcasting provider
│   ├── EventServiceProvider.php # Event provider
│   ├── RouteServiceProvider.php # Route provider
│   ├── PaymentServiceProvider.php # Payment integration
│   ├── ReputationServiceProvider.php # Reputation system
│   └── NotificationServiceProvider.php # Notification channels
│
├── Rules/                       # Custom validation rules
│   ├── ValidBinanceAddress.php  # Binance wallet validation
│   ├── ValidChallengeCategory.php # Category validation
│   ├── UniqueActiveSubscription.php # Subscription validation
│   ├── ValidRewardClaim.php     # Reward validation
│   └── StrongPassword.php       # Password strength
│
└── Services/                    # Business logic services
    ├── PaymentService.php       # Payment processing logic
    ├── BinanceApiService.php    # Binance API integration
    ├── ReputationService.php    # Reputation calculations
    ├── RewardService.php        # Reward distribution
    ├── ChallengeService.php     # Challenge management
    ├── NotificationService.php  # Notification handling
    ├── AnalyticsService.php     # Analytics processing
    ├── SearchService.php        # Search functionality
    ├── ExportService.php        # Data export
    ├── ImportService.php        # Data import
    ├── BackupService.php        # Backup management
    └── SecurityService.php      # Security utilities
```

---

## ⚙️ Configuration Directory (`config/`)

```
config/
├── app.php                      # Main application config
├── auth.php                     # Authentication configuration
├── broadcasting.php             # Broadcasting settings
├── cache.php                    # Cache configuration
├── cors.php                     # CORS settings
├── database.php                 # Database connections
├── filesystems.php              # File storage config
├── hashing.php                  # Hashing configuration
├── logging.php                  # Logging settings
├── mail.php                     # Mail configuration
├── queue.php                    # Queue settings
├── sanctum.php                  # API sanctum config
├── services.php                 # Third-party services
├── session.php                  # Session configuration
├── view.php                     # View settings
├── binance.php                  # Binance API configuration
├── reputation.php               # Reputation system settings
├── gamification.php             # Gamification rules
├── payment.php                  # Payment processing config
├── notification.php             # Notification channels
├── analytics.php                # Analytics configuration
├── security.php                 # Security settings
└── mim.php                      # Platform-specific config
```

---

## 🗄️ Database Directory (`database/`)

```
database/
├── factories/                   # Model factories
│   ├── UserFactory.php         # User factory
│   ├── ChallengeFactory.php    # Challenge factory
│   ├── ResponseFactory.php     # Response factory
│   ├── CategoryFactory.php     # Category factory
│   ├── PaymentFactory.php      # Payment factory
│   └── ReputationFactory.php   # Reputation factory
│
├── migrations/                  # Database migrations
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2024_01_15_000001_create_user_profiles_table.php
│   ├── 2024_01_15_000002_create_categories_table.php
│   ├── 2024_01_15_000003_create_challenges_table.php
│   ├── 2024_01_15_000004_create_challenge_responses_table.php
│   ├── 2024_01_15_000005_create_challenge_votes_table.php
│   ├── 2024_01_15_000006_create_challenge_assignments_table.php
│   ├── 2024_01_15_000007_create_challenge_media_table.php
│   ├── 2024_01_15_000008_create_user_subscriptions_table.php
│   ├── 2024_01_15_000009_create_wallet_addresses_table.php
│   ├── 2024_01_15_000010_create_payment_transactions_table.php
│   ├── 2024_01_15_000011_create_reward_claims_table.php
│   ├── 2024_01_15_000012_create_reputation_points_table.php
│   ├── 2024_01_15_000013_create_user_reputation_summary_table.php
│   ├── 2024_01_15_000014_create_achievement_badges_table.php
│   ├── 2024_01_15_000015_create_user_achievement_badges_table.php
│   ├── 2024_01_15_000016_create_leaderboards_table.php
│   ├── 2024_01_15_000017_create_user_streaks_table.php
│   ├── 2024_01_15_000018_create_notification_preferences_table.php
│   ├── 2024_01_15_000019_create_push_notification_tokens_table.php
│   ├── 2024_01_15_000020_create_pages_table.php
│   ├── 2024_01_15_000021_create_faqs_table.php
│   ├── 2024_01_15_000022_create_announcements_table.php
│   ├── 2024_01_15_000023_create_user_announcement_dismissals_table.php
│   ├── 2024_01_15_000024_create_platform_statistics_table.php
│   ├── 2024_01_15_000025_create_user_activity_logs_table.php
│   ├── 2024_01_15_000026_create_api_usage_logs_table.php
│   ├── 2024_01_15_000027_create_settings_table.php
│   ├── 2024_01_15_000028_create_permission_tables.php
│   ├── 2024_01_15_000029_create_notifications_table.php
│   └── 2024_01_15_000030_create_personal_access_tokens_table.php
│
└── seeders/                     # Database seeders
    ├── DatabaseSeeder.php       # Main seeder
    ├── UserRolesSeeder.php      # User roles seeder
    ├── PermissionsSeeder.php    # Permissions seeder
    ├── AdminUserSeeder.php      # Admin user seeder
    ├── CategoriesSeeder.php     # Categories seeder
    ├── AchievementBadgesSeeder.php # Badges seeder
    ├── SettingsSeeder.php       # Settings seeder
    ├── PagesSeeder.php          # Pages seeder
    ├── FaqsSeeder.php           # FAQ seeder
    └── DemoDataSeeder.php       # Demo data seeder
```

---

## 🎨 Resources Directory (`resources/`)

```
resources/
├── css/                         # CSS source files
│   ├── app.css                  # Main application styles
│   ├── admin.css                # Admin panel styles
│   └── components/              # Component-specific styles
│       ├── buttons.css
│       ├── forms.css
│       ├── modals.css
│       ├── cards.css
│       ├── leaderboard.css
│       └── challenges.css
│
├── js/                          # JavaScript source files
│   ├── app.js                   # Main application JS
│   ├── bootstrap.js             # Bootstrap JS configuration
│   └── components/              # Vue/Alpine components
│       ├── ChallengeForm.js
│       ├── ResponseEditor.js
│       ├── VoteButtons.js
│       ├── PaymentModal.js
│       ├── NotificationBell.js
│       ├── SearchBox.js
│       ├── ReputationBar.js
│       └── LeaderboardTable.js
│
├── lang/                        # Localization files
│   ├── en/                      # English translations
│   │   ├── auth.php
│   │   ├── pagination.php
│   │   ├── passwords.php
│   │   ├── validation.php
│   │   ├── challenges.php
│   │   ├── payments.php
│   │   ├── reputation.php
│   │   ├── gamification.php
│   │   └── messages.php
│   │
│   ├── ar/                      # Arabic translations (RTL)
│   │   ├── auth.php
│   │   ├── pagination.php
│   │   ├── passwords.php
│   │   ├── validation.php
│   │   ├── challenges.php
│   │   ├── payments.php
│   │   ├── reputation.php
│   │   ├── gamification.php
│   │   └── messages.php
│   │
│   └── fr/                      # French translations
│       ├── auth.php
│       ├── pagination.php
│       ├── passwords.php
│       ├── validation.php
│       ├── challenges.php
│       ├── payments.php
│       ├── reputation.php
│       ├── gamification.php
│       └── messages.php
│
└── views/                       # Blade templates
    ├── layouts/                 # Layout templates
    │   ├── app.blade.php        # Main application layout
    │   ├── admin.blade.php      # Admin panel layout
    │   ├── auth.blade.php       # Authentication layout
    │   ├── guest.blade.php      # Guest layout
    │   └── email.blade.php      # Email layout
    │
    ├── components/              # Reusable components
    │   ├── alert.blade.php      # Alert component
    │   ├── button.blade.php     # Button component
    │   ├── card.blade.php       # Card component
    │   ├── modal.blade.php      # Modal component
    │   ├── pagination.blade.php # Pagination component
    │   ├── navbar.blade.php     # Navigation bar
    │   ├── sidebar.blade.php    # Sidebar navigation
    │   ├── footer.blade.php     # Footer component
    │   ├── breadcrumb.blade.php # Breadcrumb navigation
    │   ├── search-box.blade.php # Search component
    │   ├── vote-buttons.blade.php # Voting buttons
    │   ├── reputation-badge.blade.php # Reputation display
    │   ├── achievement-badge.blade.php # Achievement badges
    │   └── loading-spinner.blade.php # Loading indicator
    │
    ├── auth/                    # Authentication views
    │   ├── confirm-password.blade.php
    │   ├── forgot-password.blade.php
    │   ├── login.blade.php
    │   ├── register.blade.php
    │   ├── reset-password.blade.php
    │   ├── verify-email.blade.php
    │   └── two-factor-challenge.blade.php
    │
    ├── admin/                   # Admin panel views
    │   ├── dashboard.blade.php  # Admin dashboard
    │   ├── users/               # User management
    │   │   ├── index.blade.php
    │   │   ├── show.blade.php
    │   │   ├── edit.blade.php
    │   │   └── create.blade.php
    │   ├── challenges/          # Challenge management
    │   │   ├── index.blade.php
    │   │   ├── show.blade.php
    │   │   ├── edit.blade.php
    │   │   └── moderate.blade.php
    │   ├── payments/            # Payment management
    │   │   ├── index.blade.php
    │   │   ├── show.blade.php
    │   │   └── rewards.blade.php
    │   ├── analytics/           # Analytics views
    │   │   ├── dashboard.blade.php
    │   │   ├── users.blade.php
    │   │   ├── challenges.blade.php
    │   │   └── revenue.blade.php
    │   └── settings/            # System settings
    │       ├── general.blade.php
    │       ├── payment.blade.php
    │       ├── email.blade.php
    │       └── security.blade.php
    │
    ├── challenges/              # Challenge-related views
    │   ├── index.blade.php      # Challenge listing
    │   ├── show.blade.php       # Challenge details
    │   ├── create.blade.php     # Challenge submission
    │   ├── edit.blade.php       # Challenge editing
    │   └── partials/
    │       ├── challenge-card.blade.php
    │       ├── response-thread.blade.php
    │       ├── vote-section.blade.php
    │       └── challenge-meta.blade.php
    │
    ├── responses/               # Response views
    │   ├── create.blade.php     # Response creation
    │   ├── edit.blade.php       # Response editing
    │   └── show.blade.php       # Response display
    │
    ├── dashboard/               # User dashboard
    │   ├── index.blade.php      # Main dashboard
    │   ├── challenges.blade.php # User's challenges
    │   ├── responses.blade.php  # User's responses
    │   ├── reputation.blade.php # Reputation overview
    │   ├── achievements.blade.php # Badges & achievements
    │   └── activity.blade.php   # Activity history
    │
    ├── profile/                 # User profile
    │   ├── edit.blade.php       # Profile editing
    │   ├── show.blade.php       # Profile display
    │   ├── partials/
    │   │   ├── delete-user-form.blade.php
    │   │   ├── update-password-form.blade.php
    │   │   ├── update-profile-information-form.blade.php
    │   │   └── two-factor-authentication-form.blade.php
    │   └── settings/
    │       ├── account.blade.php
    │       ├── privacy.blade.php
    │       ├── notifications.blade.php
    │       └── preferences.blade.php
    │
    ├── subscription/            # Subscription management
    │   ├── index.blade.php      # Subscription overview
    │   ├── plans.blade.php      # Available plans
    │   ├── checkout.blade.php   # Checkout process
    │   ├── success.blade.php    # Payment success
    │   └── cancelled.blade.php  # Payment cancelled
    │
    ├── wallet/                  # Wallet management
    │   ├── index.blade.php      # Wallet overview
    │   ├── addresses.blade.php  # Wallet addresses
    │   ├── transactions.blade.php # Transaction history
    │   └── rewards.blade.php    # Reward claims
    │
    ├── leaderboard/             # Leaderboard views
    │   ├── index.blade.php      # Main leaderboard
    │   ├── reputation.blade.php # Reputation leaders
    │   ├── challenges.blade.php # Challenge leaders
    │   └── monthly.blade.php    # Monthly rankings
    │
    ├── search/                  # Search results
    │   ├── index.blade.php      # Search page
    │   └── results.blade.php    # Search results
    │
    ├── pages/                   # Static pages
    │   ├── home.blade.php       # Homepage
    │   ├── about.blade.php      # About page
    │   ├── how-it-works.blade.php # How it works
    │   ├── terms.blade.php      # Terms of service
    │   ├── privacy.blade.php    # Privacy policy
    │   ├── faq.blade.php        # FAQ page
    │   ├── contact.blade.php    # Contact page
    │   └── maintenance.blade.php # Maintenance mode
    │
    ├── notifications/           # Notification views
    │   ├── index.blade.php      # All notifications
    │   └── partials/
    │       ├── notification-item.blade.php
    │       └── notification-bell.blade.php
    │
    ├── emails/                  # Email templates
    │   ├── challenge/
    │   │   ├── submitted.blade.php
    │   │   ├── resolved.blade.php
    │   │   └── expert-assigned.blade.php
    │   ├── payment/
    │   │   ├── received.blade.php
    │   │   ├── reward-awarded.blade.php
    │   │   └── subscription-expiring.blade.php
    │   ├── user/
    │   │   ├── welcome.blade.php
    │   │   ├── account-created.blade.php
    │   │   └── password-reset.blade.php
    │   └── system/
    │       ├── maintenance.blade.php
    │       └── announcement.blade.php
    │
    └── errors/                  # Error pages
        ├── 401.blade.php        # Unauthorized
        ├── 403.blade.php        # Forbidden
        ├── 404.blade.php        # Not found
        ├── 419.blade.php        # Page expired
        ├── 429.blade.php        # Too many requests
        ├── 500.blade.php        # Server error
        └── 503.blade.php        # Service unavailable
```

---

## 🛣️ Routes Directory (`routes/`)

```
routes/
├── web.php                      # Web routes
├── api.php                      # API routes
├── console.php                  # Artisan commands
├── channels.php                 # Broadcast channels
├── admin.php                    # Admin routes (custom)
└── auth.php                     # Authentication routes
```

---

## 🗃️ Storage Directory (`storage/`)

```
storage/
├── app/                         # Application files
│   ├── public/                  # Public files
│   │   ├── avatars/             # User avatars
│   │   ├── challenges/          # Challenge attachments
│   │   ├── documents/           # Document uploads
│   │   └── exports/             # Export files
│   ├── challenges/              # Challenge files
│   ├── payments/                # Payment receipts
│   └── backups/                 # Database backups
│
├── framework/                   # Laravel framework files
│   ├── cache/
│   │   └── data/
│   ├── sessions/
│   ├── testing/
│   └── views/
│
└── logs/                        # Application logs
    ├── laravel.log              # Main application log
    ├── payments.log             # Payment processing log
    ├── binance.log              # Binance API log
    ├── reputation.log           # Reputation system log
    └── security.log             # Security events log
```

---

## 🌐 Public Directory (`public/`)

```
public/
├── index.php                    # Entry point
├── .htaccess                    # Apache rules
├── robots.txt                   # SEO robots file
├── favicon.ico                  # Site favicon
├── manifest.json                # PWA manifest
├── sw.js                        # Service worker
├── assets/                      # Compiled assets
│   ├── css/
│   │   ├── app.css             # Main CSS
│   │   └── admin.css           # Admin CSS
│   ├── js/
│   │   ├── app.js              # Main JS
│   │   └── admin.js            # Admin JS
│   └── images/
│       ├── logo.png            # Platform logo
│       ├── hero-bg.jpg         # Hero background
│       ├── icons/              # Icon files
│       ├── badges/             # Achievement badges
│       └── avatars/            # Default avatars
│
├── storage/                     # Symlinked storage
├── uploads/                     # User uploads
│   ├── challenges/             # Challenge attachments
│   ├── avatars/                # User avatars
│   └── documents/              # Document uploads
│
└── docs/                        # API documentation
    ├── api-docs.html           # API documentation
    └── postman/                # Postman collections
        └── Mim-API.postman_collection.json
```

---

## 🧪 Tests Directory (`tests/`)

```
tests/
├── Feature/                     # Feature tests
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   ├── EmailVerificationTest.php
│   │   ├── PasswordConfirmationTest.php
│   │   ├── PasswordResetTest.php
│   │   ├── RegistrationTest.php
│   │   └── TwoFactorAuthTest.php
│   │
│   ├── Challenge/
│   │   ├── ChallengeSubmissionTest.php
│   │   ├── ChallengeResolutionTest.php
│   │   ├── ChallengeVotingTest.php
│   │   └── ChallengeSearchTest.php
│   │
│   ├── Payment/
│   │   ├── SubscriptionTest.php
│   │   ├── PaymentProcessingTest.php
│   │   ├── RewardDistributionTest.php
│   │   └── WalletManagementTest.php
│   │
│   ├── Reputation/
│   │   ├── ReputationCalculationTest.php
│   │   ├── LeaderboardTest.php
│   │   ├── BadgeAwardingTest.php
│   │   └── StreakTrackingTest.php
│   │
│   └── Admin/
│       ├── UserManagementTest.php
│       ├── ChallengeManagementTest.php
│       ├── PaymentManagementTest.php
│       └── AnalyticsTest.php
│
├── Unit/                        # Unit tests
│   ├── Models/
│   │   ├── UserTest.php
│   │   ├── ChallengeTest.php
│   │   ├── ResponseTest.php
│   │   └── PaymentTest.php
│   │
│   ├── Services/
│   │   ├── PaymentServiceTest.php
│   │   ├── ReputationServiceTest.php
│   │   ├── BinanceServiceTest.php
│   │   └── RewardServiceTest.php
│   │
│   └── Utilities/
│       ├── HelperTest.php
│       ├── ValidationTest.php
│       └── SecurityTest.php
│
├── CreatesApplication.php       # Test setup
└── TestCase.php                 # Base test class
```

---

## 🔧 Additional Configuration Files

### Bootstrap Directory (`bootstrap/`)

```
bootstrap/
├── app.php                      # Application bootstrapping
├── cache/                       # Bootstrap cache
│   ├── config.php              # Config cache
│   ├── packages.php            # Package cache
│   ├── routes-v7.php           # Route cache
│   └── services.php            # Service cache
└── providers.php                # Service providers
```

### Vendor Directory (Third-party packages)

```
vendor/                          # Composer dependencies
├── laravel/framework/           # Laravel framework
├── spatie/laravel-permission/   # Role & permission management
├── laravel/cashier/            # Subscription billing
├── spatie/laravel-activitylog/ # Activity logging
├── spatie/laravel-medialibrary/ # Media management
├── laravel/telescope/          # Debugging assistant
├── laravel/scout/              # Full-text search
├── pusher/pusher-php-server/   # Real-time notifications
├── guzzlehttp/guzzle/          # HTTP client
└── phpunit/phpunit/            # Testing framework
```

---

## 📋 File Descriptions Summary

### Key Application Files

**Models** (35+ files)

- Core business logic and database interactions
- Relationships and query scopes
- Model observers and events

**Controllers** (40+ files)

- HTTP request handling
- Business logic coordination
- API endpoints and web routes

**Services** (12+ files)

- Complex business logic
- Third-party API integrations
- Reusable application services

**Views** (80+ files)

- User interface templates
- Email templates
- Component partials

**Migrations** (30+ files)

- Database structure definitions
- Schema modifications
- Index optimizations

**Configuration** (20+ files)

- Application settings
- Service configurations
- Environment-specific configs

### Development Files

**Tests** (50+ files)

- Feature testing
- Unit testing
- Integration testing

**Factories & Seeders** (15+ files)

- Test data generation
- Database seeding
- Demo content

---

## 🚀 Production Deployment Structure

### Additional Production Files

```
├── .env.production              # Production environment
├── .env.staging                 # Staging environment
├── docker-compose.yml           # Docker configuration
├── Dockerfile                   # Docker container
├── nginx.conf                   # Nginx configuration
├── supervisor.conf              # Queue worker supervision
├── backup-script.sh             # Automated backups
├── deploy-script.sh             # Deployment script
└── monitoring/
    ├── health-check.php         # Health monitoring
    └── performance-monitor.php  # Performance tracking
```

### File Counts by Directory

- **Total Files**: ~500+
- **PHP Files**: ~350
- **Blade Templates**: ~80
- **JavaScript Files**: ~20
- **CSS Files**: ~15
- **Configuration Files**: ~25
- **Test Files**: ~50

---

*This comprehensive directory structure provides a scalable, maintainable foundation for the Mim platform while following Laravel best practices and supporting all required SaaS features.*