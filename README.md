# AvazTek Payment Core PHP SDK

کلاینت رسمی PHP/Laravel برای Payment Platform مرکزی AvazTek. این SDK برای Laravel 8 تا 13 طراحی شده و تمام درخواست‌ها را با HMAC، timestamp و nonce امضا می‌کند.

## نصب

```bash
composer require amirkateb/payment-core-client
php artisan vendor:publish --tag=payment-core-config
```

`.env`:

```env
PAYMENT_CORE_URL=https://avaztek.ir
PAYMENT_CORE_KEY_ID=pay_live_xxxxxxxxxxxxxxxxxxxxxxxx
PAYMENT_CORE_SECRET=paysec_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Secret را فقط در سمت سرور نگه دارید و هرگز در JavaScript، HTML یا مخزن Git قرار ندهید.

## ایجاد پرداخت

```php
use Avaztek\PaymentCore\PaymentClient;

$client = app(PaymentClient::class);
$response = $client->createTransaction([
    'order_id' => (string) $order->id,
    'amount' => 250000,
    'currency' => 'IRT',
    'description' => 'پرداخت سفارش #'.$order->id,
    'customer' => [
        'name' => $order->customer_name,
        'email' => $order->customer_email,
        'mobile' => $order->customer_mobile,
    ],
    'return_url' => route('payments.return'),
    'metadata' => ['cart_id' => $order->cart_id],
]);

return redirect()->away($response['transaction']['checkout_url']);
```

کاربر در Checkout مرکزی بین درگاه‌های فعال و مجاز برای همان مبلغ انتخاب می‌کند.

## استعلام امن نتیجه

پارامترهای صفحه بازگشت فقط برای UX هستند. برای تصمیم مالی همیشه تراکنش را server-to-server استعلام کنید:

```php
$result = $client->transaction(request('payment_id'));
if (($result['transaction']['status'] ?? null) === 'paid') {
    // fulfil order idempotently
}
```

## Webhook

هدرهای webhook:

- `X-Payment-Event`
- `X-Payment-Timestamp`
- `X-Payment-Signature`

```php
$raw = $request->getContent();
$valid = $client->verifyWebhook(
    $request->header('X-Payment-Timestamp'),
    $request->header('X-Payment-Event'),
    $raw,
    $request->header('X-Payment-Signature')
);
abort_unless($valid, 401);
```

Webhook handler باید idempotent باشد؛ `event_id` را ذخیره کنید و یک event را دوبار اعمال نکنید.

## Laravel 8–13

Auto-discovery فعال است. در تمام نسخه‌های Laravel 8 تا 13 می‌توانید `PaymentClient` را dependency-inject کنید یا از Facade `PaymentCore` استفاده کنید.

## خطاها

تمام خطاهای HTTP/تنظیمات با `Avaztek\PaymentCore\Exceptions\PaymentCoreException` پرتاب می‌شوند. `statusCode()` و `responseBody()` برای logging امن قابل استفاده‌اند؛ Secret را log نکنید.
