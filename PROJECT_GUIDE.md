# PROJECT_GUIDE.md

# Mim — The Final Challenge to Islamophobia
*Laravel SaaS Platform Documentation*

## 🎯 Project Mission

**"Eliminate Islamophobia by inviting people to openly submit and resolve claimed Islamic mistakes — once and for all."**

Mim is a revolutionary crowdsourced platform that creates a transparent, global challenge system where anyone can submit what they believe are mistakes or contradictions in Islam. Through community-driven peer review and expert verification, every challenge is thoroughly investigated and publicly resolved.

---

## 🧩 Core Concept

### The Challenge System
- **Open Submission**: Any member can submit alleged "mistakes" in Islam (Qur'an, Hadith, Islamic history, interpretations)
- **Community Review**: Public peer-review process with debates, arguments, and voting
- **Expert Verification**: Verified scholars and moderators provide final resolution
- **Monetary Incentive**: If a submission is confirmed as a genuine mistake, submitter wins **999 USDT**
- **Paid Membership**: Members pay **99 USDT/month** subscription to participate actively
- **Permanent Archive**: All resolved challenges become public reference material

### Platform Goals
1. **Transparency**: Every possible challenge against Islam is addressed openly
2. **Education**: Build comprehensive knowledge base of resolved Islamic questions
3. **Community**: Foster respectful dialogue between different perspectives
4. **Accountability**: Create financial incentive for serious, well-researched submissions

---

## ⚙️ Platform Architecture

### System Overview
- **Laravel 12+ SaaS Application** with modern PHP 8.2+ features
- **Multi-tenant membership system** with role-based access control
- **Cryptocurrency payment integration** via Binance API
- **Real-time collaboration** with live notifications and updates
- **Gamification engine** with reputation, badges, and leaderboards
- **Multilingual support** (English, Arabic RTL, French)

### Core Modules
1. **Authentication & Authorization** (Laravel Breeze + custom roles)
2. **Membership & Subscription Management**
3. **Challenge Submission & Review System**
4. **Payment & Crypto Wallet Integration**
5. **Gamification & Reputation Engine**
6. **Real-time Notifications & Activity Feeds**
7. **Admin Dashboard & Analytics**
8. **Public Archive & Transparency Module**

---

## 🧠 User Roles & Permissions

### 👁️ Visitor / Guest
**Access Level**: Read-only public content
- Browse public challenges and resolutions
- View leaderboards and platform statistics
- Read archived Q&A library
- Access transparency dashboard
- **Cannot**: Submit challenges, vote, comment, or access member features

### 💰 Member / Challenger (Paid Subscribers)
**Access Level**: Full platform participation
- Submit new challenges with supporting evidence
- Participate in debates and discussions
- Vote on arguments and counterarguments
- Earn reputation points and badges
- Access member-only content and features
- Receive notifications and updates
- **Requires**: Active 99 USDT/month subscription

### 🎓 Reviewer (Scholars/Experts)
**Access Level**: Review and moderation privileges
- Review submitted challenges for validity
- Provide expert responses and Islamic evidence
- Flag inappropriate or spam content
- Mark challenges as "Resolved" or "Valid Mistake"
- Access advanced moderation tools
- **Selection**: Verified Islamic scholars and subject matter experts

### ⚙️ Moderator / Admin
**Access Level**: Full system administration
- Manage all users and subscriptions
- Handle payment processing and payouts
- Monitor platform activity and content
- Approve reward claims and transfers
- Access comprehensive analytics dashboard
- Configure platform settings and rules

---

## 💰 Payment & Binance Integration

### Subscription Model
- **Monthly Fee**: 99 USDT per member
- **Payment Method**: Cryptocurrency via Binance API
- **Auto-renewal**: Automatic monthly billing
- **Grace Period**: 7-day grace period for failed payments
- **Suspension**: Account suspended after grace period expires

### Reward System
- **Prize Amount**: 999 USDT for confirmed valid mistakes
- **Escrow System**: Funds held in platform wallet until payout
- **Verification Process**: Multi-step approval before reward release
- **Payout Method**: Direct transfer to member's Binance wallet

### Wallet Management
- **Linked Wallets**: Members connect Binance wallet addresses
- **Transaction History**: Complete payment and reward records
- **Security Measures**: Two-factor authentication for transactions
- **Compliance**: KYC-lite verification for large payouts

---

## 🏆 Gamification & Reputation System

### Reputation Points
**Earning Methods**:
- Submit valid challenges (+100 points)
- Receive upvotes on arguments (+10 points)
- Participate in discussions (+5 points)
- Daily platform engagement (+2 points)
- Complete profile and verification (+25 points)

**Point Penalties**:
- Submit spam/invalid challenges (-50 points)
- Receive downvotes (-5 points)
- Violate community guidelines (-100 points)

### Achievement Badges
- 🚀 **First Challenge** - Submit your first challenge
- 💬 **Active Debater** - Participate in 10 discussions
- ⭐ **Popular Contributor** - Receive 100 total upvotes
- 🏅 **Top Challenger** - Rank in top 10 leaderboard
- 🔥 **Streak Master** - Maintain 30-day activity streak
- 💎 **Reputation Legend** - Achieve 1000+ reputation points

### Leaderboards
1. **Top Challengers** - Most active challenge submitters
2. **Top Reviewers** - Most helpful expert responses
3. **Most Upvoted** - Highest quality contributions
4. **Reputation Leaders** - Overall platform reputation
5. **Monthly Champions** - Current month's top performers

### Engagement Features
- **Daily Streaks**: Consecutive days of platform activity
- **Weekly Challenges**: Special themed submission periods
- **Monthly Competitions**: Extra rewards for top contributors
- **Progress Tracking**: Visual progress bars and statistics

---

## 📋 Challenge Lifecycle

### Phase 1: Submission
**Duration**: Immediate
- Member submits alleged mistake with detailed description
- Required fields: Title, category, description, sources, evidence
- Optional: Supporting images, documents, external links
- Automatic duplicate detection using AI similarity matching
- Initial spam filtering and content validation

### Phase 2: Open Debate (7 days)
**Duration**: 7 days from submission
- Challenge becomes publicly visible to all members
- Community members post arguments and counterarguments
- Voting system for argument quality and validity
- Real-time notifications for activity updates
- Discussion threading for organized debates

### Phase 3: Expert Review (14 days)
**Duration**: Up to 14 days
- Assigned reviewers (verified scholars) analyze all arguments
- Expert responses with Islamic sources and evidence
- Community can respond to expert analysis
- Additional evidence collection if needed
- Preliminary resolution recommendation

### Phase 4: Final Resolution
**Duration**: 24-48 hours
- Admin review of all evidence and expert opinions
- Final status assignment:
  - ✅ **Refuted** - Not a valid mistake
  - ⚠️ **Valid Mistake** - Confirmed error (999 USDT reward)
  - 🔄 **Needs More Research** - Requires additional investigation
  - ❌ **Invalid/Spam** - Doesn't meet submission criteria

### Phase 5: Archive & Documentation
**Duration**: Permanent
- Completed challenges archived in public knowledge base
- Searchable by category, keywords, and resolution status
- Permanent reference links for future citations
- Integration with transparency dashboard statistics

---

## 📊 Public Transparency Features

### Transparency Dashboard
- **Total Challenges**: Complete count of all submissions
- **Resolution Statistics**: Breakdown by status (refuted, valid, pending)
- **Reward Payouts**: Total USDT distributed to successful challengers
- **Member Statistics**: Active subscribers, participation rates
- **Response Times**: Average resolution time by category
- **Success Rate**: Percentage of challenges that are valid mistakes

### Public Archive System
- **Complete History**: Every resolved challenge permanently accessible
- **Search & Filter**: Advanced search by category, keywords, status
- **Reference Links**: Permanent URLs for academic and research citations
- **Export Options**: PDF generation for individual challenges
- **Multi-language**: Translations of key resolutions

### Real-time Activity Feed
- **Live Updates**: Recent submissions, resolutions, and major activities
- **Member Achievements**: Public recognition of milestones and badges
- **Expert Responses**: Highlighting new scholarly contributions
- **System Announcements**: Platform updates and important notices

---

## 🔐 Membership System

### Registration & Authentication
- **Laravel Breeze**: Clean, simple authentication system
- **Two-Factor Authentication**: SMS or authenticator app verification
- **Email Verification**: Required for account activation
- **Social Login**: Optional Google/Facebook integration
- **Password Security**: Strong password requirements and hashing

### Profile Management
- **Basic Information**: Username, display name, bio, location (optional)
- **Reputation Display**: Points, badges, achievements, and statistics
- **Activity History**: Complete log of challenges, comments, and votes
- **Privacy Settings**: Control visibility of personal information
- **Notification Preferences**: Customize email and push notifications

### Subscription Management
- **Payment Status**: Current subscription status and next billing date
- **Payment History**: Complete transaction records
- **Auto-renewal Control**: Enable/disable automatic renewals
- **Grace Period Handling**: Warnings and temporary access during payment issues
- **Account Recovery**: Reactivation process for suspended accounts

---

## 🛡️ Security & Compliance

### Data Protection
- **GDPR Compliance**: Full compliance with European data protection laws
- **Data Encryption**: All sensitive data encrypted at rest and in transit
- **Privacy Controls**: Member control over personal data visibility
- **Data Retention**: Clear policies on data storage and deletion
- **Right to Erasure**: Complete account and data deletion options

### Financial Security
- **Crypto Wallet Security**: Multi-signature wallet implementation
- **Transaction Auditing**: Complete logs of all financial transactions
- **KYC Verification**: Light verification for large reward payouts
- **Anti-Money Laundering**: Compliance with international AML regulations
- **Fraud Detection**: AI-powered detection of suspicious activities

### Content Moderation
- **Community Guidelines**: Clear rules for respectful discourse
- **AI Content Filtering**: Automatic detection of spam and inappropriate content
- **Human Moderation**: Expert review of flagged content
- **Appeal Process**: Fair system for content moderation appeals
- **Rate Limiting**: Prevent spam and abuse through usage limits

### System Security
- **Regular Security Audits**: Professional penetration testing and code reviews
- **Dependency Management**: Regular updates and security patch application
- **Access Logging**: Complete audit trail of all system access
- **Backup & Recovery**: Automated backups with disaster recovery procedures
- **Monitoring & Alerts**: 24/7 system monitoring with incident response

---

## 🛠️ Tech Stack

### Backend Framework
- **Laravel 12.x**: Latest PHP framework with modern features
- **PHP 8.2+**: Latest PHP version with performance improvements
- **MySQL Database**: Primary data storage with proper indexing
- **Redis Cache**: Session storage and caching layer
- **Queue System**: Background job processing for heavy operations

### Frontend Technologies
- **Laravel Blade**: Server-side templating with component system
- **Tailwind CSS**: Utility-first CSS framework for rapid development
- **Alpine.js**: Lightweight JavaScript framework for interactivity
- **Laravel Livewire**: Dynamic interfaces without complex JavaScript

### Third-party Integrations
- **Binance API**: Cryptocurrency payment processing and wallet management
- **Laravel Cashier**: Subscription billing management
- **Pusher/Laravel Echo**: Real-time notifications and live updates
- **Laravel Scout**: Full-text search capabilities
- **Intervention Image**: Image processing and optimization

### Development Tools
- **Laravel Breeze**: Authentication scaffolding
- **Laravel Telescope**: Debugging and performance monitoring
- **PHPUnit**: Comprehensive testing framework
- **Laravel Pint**: Code style formatting
- **Composer**: Dependency management

---

## 📄 Key Pages & User Interface

### Public Pages
1. **Homepage** - Mission statement, featured challenges, platform statistics
2. **Challenge Archive** - Browse all resolved challenges with search/filter
3. **Leaderboards** - Top contributors, reviewers, and achievers
4. **Transparency Dashboard** - Real-time platform statistics and metrics
5. **About/FAQ** - Platform explanation and frequently asked questions

### Member Pages
6. **Member Dashboard** - Personal activity overview and quick actions
7. **Submit Challenge** - Challenge submission form with validation
8. **Challenge Detail** - Individual challenge with debate thread
9. **My Challenges** - Personal submission history and status tracking
10. **Profile Settings** - Account management and preferences
11. **Wallet & Billing** - Subscription management and payment history

### Admin Pages
12. **Admin Dashboard** - System overview and key metrics
13. **User Management** - Member accounts, roles, and subscriptions
14. **Challenge Management** - Review queue and resolution tools
15. **Payment Management** - Transaction monitoring and payout processing
16. **System Settings** - Platform configuration and feature toggles
17. **Analytics Reports** - Detailed platform performance analytics

---

## 📈 Analytics & Admin Dashboard

### Key Performance Indicators (KPIs)
- **Member Growth**: New registrations, active subscribers, churn rate
- **Content Metrics**: Challenges submitted, resolved, engagement rates
- **Financial Metrics**: Revenue, payouts, conversion rates, profit margins
- **Quality Metrics**: Average resolution time, community satisfaction scores

### Real-time Monitoring
- **Live Activity**: Current online members, active discussions
- **System Performance**: Response times, error rates, server resources
- **Payment Processing**: Transaction success rates, failed payments
- **Content Moderation**: Flagged content, moderation queue status

### Reporting & Analytics
- **Daily Reports**: Automated summaries of platform activity
- **Monthly Analytics**: Comprehensive performance analysis
- **Member Insights**: Engagement patterns and behavior analysis
- **Financial Reports**: Revenue tracking and payout summaries
- **Export Capabilities**: CSV, PDF, and Excel report generation

---

## 🌍 Development Approach

### Development Environment
- **Local Setup**: XAMPP with PHP 8.2, MySQL, and Git Bash
- **Version Control**: Git with feature branch workflow
- **Code Standards**: PSR-12 coding standards with Laravel Pint
- **Testing Strategy**: Feature tests, unit tests, and browser tests

### Deployment Strategy
- **Production Environment**: Traditional hosting without SSH access
- **File Upload**: FTP/cPanel file manager deployment
- **Database Migration**: SQL export/import process
- **Environment Configuration**: Manual .env file setup
- **Asset Compilation**: Local build with production upload

### Multi-AI Development Approach
- **Consistency**: Standardized coding patterns across all AI agents
- **Zero Errors**: Comprehensive testing and validation at each step
- **24-Hour Timeline**: Rapid development with parallel AI agent work
- **Code Quality**: Automated code review and quality checks

---

## 🎯 Purpose Reminder

> **The Global Final Challenge**: "If anyone can prove a real mistake in Islam, they win 999 USDT. Otherwise, every question is publicly answered — ending Islamophobia with full transparency."

This platform serves as the definitive, transparent, and financially-backed challenge to anyone who believes they have found contradictions or errors in Islam. Through community-driven investigation, expert scholarly review, and permanent public documentation, we aim to:

1. **Address Every Concern**: No question or challenge goes unanswered
2. **Ensure Transparency**: All processes are public and auditable  
3. **Provide Incentives**: Real financial rewards for genuine discoveries
4. **Build Knowledge**: Create the world's most comprehensive Islamic Q&A resource
5. **Combat Islamophobia**: Replace ignorance and prejudice with facts and understanding

The platform's success will be measured not just by technical metrics, but by its contribution to interfaith understanding, reduction of religious prejudice, and the creation of a more informed global dialogue about Islam.

---

*This project guide serves as the foundation for all development decisions and should be referenced throughout the build process to ensure alignment with the platform's mission and technical requirements.*
