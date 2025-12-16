# Elite Waste Management Admin Dashboard

A comprehensive web application for managing waste collection services, billing, and payments in Goaso.

## Features

- **Dashboard**: Real-time overview of revenue, active customers, and pending payments.
- **Zone Management**: Organize service areas (Zones) for efficient logistics.
- **Customer CRM**: Manage residential and commercial customer profiles, effective zones, and service types.
- **Billing & Invoicing**: 
    - Recurring subscription billing (Weekly/Monthly/Quarterly).
    - Automated invoice generation via Scheduler.
    - PDF-ready Invoice views.
- **Payments**:
    - **Online**: Integrated with **Paystack** for credit card/mobile money payments.
    - **Offline**: Record cash payments manually.
- **SMS Notifications**: Integrated with **MyCSMS** (API v2) for:
    - Welcome messages.
    - Invoice alerts.
    - Payment receipts.
- **Reports**: Detailed financial reports (Receivables, Revenue, Payment Logs).
- **Settings**: Administrative control over API Keys, Company Info, and SMS Templates.

## Tech Stack

- **Framework**: Laravel 10.x / 11.x
- **Language**: PHP 8.2+
- **Database**: MySQL
- **Frontend**: Blade Templates + TailwindCSS (via Vite)
- **Permissions**: Spatie/Laravel-Permission

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL

### Steps

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/yourusername/elit-waste-admin.git
    cd elit-waste-admin
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Configure your database credentials in `.env`:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=elit_waste_db
    DB_USERNAME=root
    DB_PASSWORD=
    ```

4.  **Database Migration & Seeding**
    ```bash
    php artisan migrate --seed
    ```
    *This will create the default roles (Owner, Admin, Accountant) and a default Owner user.*

5.  **Build Assets**
    ```bash
    npm run build
    ```

6.  **Run the Server**
    ```bash
    php artisan serve
    ```

## Key Commands

- **Start Queue Worker** (Required for SMS):
    ```bash
    php artisan queue:work
    ```

- **Run Billing Cycle** (Manually trigger recurring invoices):
    ```bash
    php artisan billing:run
    ```

## Default Login

- **Email**: `admin@elitwaste.com`
- **Password**: `password` *[Change immediately after login]*

## Configuration

Navigate to **System Settings** in the dashboard sidebar to configure:
- **Paystack Keys** (Public/Secret)
- **SMS Gateway** (MyCSMS API Key, Sender ID, Base URL)
- **Company Details** (Name, Phone, Invoice Terms)

## Support

For issues, please contact the development team.
