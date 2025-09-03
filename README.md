# Laravel Disposable Email Validator

A Laravel package to **detect and block disposable email addresses** using the [disposable-email-domains](https://github.com/disposable-email-domains/disposable-email-domains) list.  
Supports **blocklist** (domains to block) and **allowlist** (domains to allow), with auto-sync from GitHub.  

---

## 📦 Installation

Install via Composer:

```bash
composer require your-vendor/laravel-disposable-email
```

The package uses **Laravel Package Auto-Discovery**, so you don’t need to register the service provider manually.

---

## ⚙️ Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=config
```

This will create `config/disposable-email.php`:

```php
<?php

return [

    'blocklist' => [
        'mailinator.com',
        '10minutemail.com',
        'guerrillamail.com',
    ],

    'allowlist' => [
        'gmail.com',
        'yahoo.com',
        'hotmail.com',
        'outlook.com',
    ],

];
```

- **Blocklist** → Domains that should be blocked.  
- **Allowlist** → Domains that are explicitly allowed, even if normally considered disposable.  

---

## 🔄 Updating Blocklist

This package includes an Artisan command to **sync the latest blocklist** from GitHub:

```bash
php artisan disposable-email:update
```

The synced list is stored at:

```
storage/app/disposable-email-blocklist.json
```

✅ Synced domains + your custom `config('disposable-email.blocklist')` will be merged together.  
✅ `allowlist` always takes priority over blocklist.  

---

## 🛠 Usage

### Validation Rule

Use the built-in rule in your `FormRequest` or controller:

```php
use YourVendor\DisposableEmail\Rules\NotDisposableEmail;

$request->validate([
    'email' => ['required', 'email', new NotDisposableEmail],
]);
```

If the email domain is blocked, the validation will fail with:

```
Disposable or blocked email addresses are not allowed.
```

---

### Standalone Check

You can also use the validator class directly:

```php
use YourVendor\DisposableEmail\DisposableEmailValidator;

$validator = new DisposableEmailValidator();

if ($validator->isDisposable('test@mailinator.com')) {
    // Handle blocked email
}
```

---

## ⏱ Optional: Scheduler

To keep your blocklist always up-to-date, schedule the update command in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('disposable-email:update')->weekly();
}
```

---

## 📝 Example Workflow

1. Install package:
   ```bash
   composer require your-vendor/laravel-disposable-email
   ```

2. Publish config (optional):
   ```bash
   php artisan vendor:publish --tag=config
   ```

3. Update blocklist:
   ```bash
   php artisan disposable-email:update
   ```

4. Use rule in validation:
   ```php
   'email' => ['required', 'email', new NotDisposableEmail],
   ```

---

## ⚡ Features

- ✅ Auto-discovers in Laravel (no manual provider setup)  
- ✅ Configurable `blocklist` and `allowlist`  
- ✅ Artisan command to sync latest domains from GitHub  
- ✅ Validation rule for easy integration  
- ✅ Standalone class for custom usage  

---

## 📄 License

MIT License.  

---

🔥 Ready to keep disposable emails out of your app!
