# Donate Nova - Donation Micro-System

A focused Laravel-based donation payment system integrated with bKash payment gateway. This micro-system is designed to work alongside a CMS, handling donation processing and payment orchestration.

## Overview

Donate Nova is a single-purpose donation engine that provides a streamlined conversion funnel:
- Story → Trust → Donation Form → bKash Payment → Thank You

## Screenshots

### Landing Page Preview

The landing page has been designed with conversion optimization in mind, featuring:

**🎯 Hero Section**
- Bold headline: "Transform Lives, Build Hope"
- Emotional connection with impactful messaging
- Clear value proposition for Nova Foundation
- Prominent CTA button with hover effects

**📊 Impact Stats**
- 10,000+ Lives Impacted
- 50+ Active Projects
- 25+ Districts Reached
- Builds credibility and social proof

**📖 Story Section**
- Four program areas with visual cards:
  - 🎓 Education for All
  - 🏥 Healthcare Access
  - 🏗️ Community Development
  - 🚨 Emergency Relief
- Each area explains the impact of donations

**🛡️ Trust & Transparency**
- 100% Secure payment badge
- Full transparency promise
- 99% of donations go to programs
- Bank-level encryption messaging

**💰 Donation Form (Conversion-Optimized)**
- Quick amount selectors (৳500, ৳1,000, ৳2,000, ৳5,000, ৳10,000)
- Custom amount option
- Only 3 required fields (name, phone, amount)
- Visual feedback on form interactions
- Prominent bKash payment button with gradient

**👥 Social Proof**
- 5,000+ Active Donors
- 4.9/5 Donor Satisfaction
- 100% Transparency Score
- Real testimonial from verified donor

> **Note**: For detailed section-by-section breakdown, see [`docs/screenshots/LANDING_PAGE.md`](docs/screenshots/LANDING_PAGE.md)

### Key Conversion Features

✅ **Mobile-First Design** - Optimized for smartphone donations
✅ **Quick Amount Selection** - Preset buttons reduce friction
✅ **Minimal Form Fields** - Only 3 fields to complete
✅ **Trust Signals** - Security badges, stats, testimonials
✅ **Emotional Storytelling** - Connects donors to impact
✅ **Clear CTAs** - Multiple paths to donation form
✅ **Smooth Scrolling** - Anchored navigation to form

## Features

- **Mobile-first design** with Tailwind CSS
- **Minimal friction** donation form (3 fields only: name, phone, amount)
- **Quick amount selectors** for faster conversions
- **Secure bKash payment integration** using tokenized checkout
- **Payment verification** with status tracking
- **Donation logging** for reporting and analytics
- **Real-time payment status updates**
- **Conversion-optimized landing page** with storytelling and trust elements

## Tech Stack

- **Backend:** Laravel 11
- **Frontend:** Blade Templates + Tailwind CSS
- **Payment Gateway:** bKash Tokenized Checkout API
- **Database:** SQLite (can be configured for MySQL)
- **Build Tool:** Vite
- **PHP:** 8.0+

## Architecture

```
CMS Website (nova.org.bd)
        │
        │  Donate button
        ▼
donate.nova.org.bd  (Laravel App)
        │
        ├── Donation Landing Page
        ├── Donation Form
        ├── bKash Payment Gateway
        ├── Payment Verification
        └── Thank You Page
```

## Installation

### Prerequisites

- PHP 8.0 or higher
- Composer
- Node.js & NPM
- SQLite or MySQL

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/md-riaz/donate-nova.git
   cd donate-nova
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure bKash credentials**

   Edit `.env` file and add your bKash credentials:
   ```env
   BKASH_SANDBOX=true
   BKASH_APP_KEY=your_app_key
   BKASH_APP_SECRET=your_app_secret
   BKASH_USERNAME=your_username
   BKASH_PASSWORD=your_password
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

   Visit `http://localhost:8000` to see the application.

## Database Schema

### Donations Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | Donor name |
| phone | string | Donor phone number |
| amount | decimal(10,2) | Donation amount |
| transaction_id | string | bKash transaction ID |
| bkash_payment_id | string | bKash payment ID |
| status | enum | pending/success/failed |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Update time |

## Routes

| Method | URI | Description |
|--------|-----|-------------|
| GET | / | Landing page with donation form |
| POST | /donate | Process donation and redirect to bKash |
| GET | /payment/callback | bKash callback handler |
| GET | /thank-you | Thank you page after successful payment |

## Payment Flow

1. User fills donation form (name, phone, amount)
2. System creates donation record with 'pending' status
3. System initializes bKash payment
4. User is redirected to bKash payment page
5. User completes payment on bKash
6. bKash redirects back to callback URL
7. System executes and verifies payment
8. Database is updated with transaction details
9. User sees thank you page with donation details

## bKash Integration

This project uses the [theihasan/laravel-bkash](https://github.com/theihasan/laravel-bkash) package for bKash integration.

### Test Credentials

For sandbox testing, you can get test credentials from bKash merchant portal.

### Important Configuration

- Set `BKASH_SANDBOX=true` for testing environment
- Set `BKASH_SANDBOX=false` for production
- Ensure your callback URL is accessible from the internet in production

## Deployment

### Production Checklist

1. Set up subdomain (e.g., `donate.nova.org.bd`)
2. Configure web server (Nginx/Apache)
3. Set environment to production in `.env`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   BKASH_SANDBOX=false
   ```
4. Configure production database (MySQL recommended)
5. Set up SSL certificate (required for bKash)
6. Configure bKash production credentials
7. Update `APP_URL` in `.env` to your domain
8. Run migrations and optimize:
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Nginx Configuration Example

```nginx
server {
    listen 80;
    server_name donate.nova.org.bd;
    root /var/www/donate-nova/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## CMS Integration

To integrate with your main CMS website, add a donate button:

```html
<a href="https://donate.nova.org.bd" class="donate-button">
    Donate Now
</a>
```

## Security

- CSRF protection enabled by default (Laravel)
- Server-side amount validation
- Payment verification via bKash query API
- HTTPS required for production
- Database transactions for payment processing
- Secure session handling

## Future Enhancements

- Preset donation amounts (500, 1000, 2000, 5000)
- Monthly recurring donations
- Multiple payment gateways (Nagad, SSLCommerz)
- Donation leaderboard
- Email notifications
- Admin dashboard for donation management
- Export donations to CSV/Excel
- Analytics integration (Google Analytics, Facebook Pixel)

## Performance Optimization

Current optimizations:
- Asset bundling with Vite
- Tailwind CSS purging
- Database indexing on key columns
- Caching for bKash tokens

Target metrics:
- Page load: < 2 seconds
- CSS bundle: < 20KB
- Mobile-first responsive design

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

This project is built on Laravel and uses the following packages:
- [theihasan/laravel-bkash](https://github.com/theihasan/laravel-bkash) for bKash integration

## Support

For issues or questions, please open an issue on the GitHub repository.
