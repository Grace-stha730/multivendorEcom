# eSewa payment (sandbox / test mode)

This project uses eSewa's **public test environment**. No real money moves and no merchant account is needed. The defaults live in `config/services.php`, so it works without any `.env` changes.

## Try it

1. `php artisan migrate` (adds `payment_uuid`, `payment_reference`, `paid_at` to `orders`).
2. Log in as a customer, add a product, and check out with **E-Sewa**.
3. You are redirected to eSewa's test site. Log in with the test account from eSewa's docs (https://developer.esewa.com.np/pages/Epay):
   - eSewa ID: `9711111111` (or `9711111112`, `9711111113`, `9711111114`)
   - Password: `Nepal@123`
   - MPIN: `1122`
   - OTP token: `123456`
4. After paying you land on **My Orders** and the order shows a green **Paid** badge.
5. If you cancel or the payment fails, the order stays **Pending** and shows a **Pay with eSewa** button to retry.

## How it works

| Step | Where |
|---|---|
| Order is created as `Pending` | `Cart::checkoutSubmit`, `Checkout::placeOrder` |
| Redirect to the payment page | `EsewaPaymentController@pay` (only the order's owner) |
| Signed form is auto-posted to eSewa | `EsewaService::formFor`, `resources/views/payments/esewa-redirect.blade.php` |
| eSewa redirects back with signed data | `EsewaPaymentController@success` / `@failure` |
| Order becomes `Paid` only after checks | `EsewaService::completeFromCallback` |

`completeFromCallback` marks an order paid only if **all** of these hold:

1. The payload's HMAC-SHA256 signature is valid, and it covers `transaction_code`, `status`, `total_amount`, `transaction_uuid` and `product_code`.
2. The status is `COMPLETE`, the merchant code matches, and the amount equals the order total.
3. eSewa's own status API (server to server) also confirms `COMPLETE`.

A forged or edited redirect URL therefore can't mark an order as paid. Each payment attempt uses a fresh `transaction_uuid`, and refreshing the success URL is harmless.

## Going live later

Set these in `.env` (values come from eSewa when you become a merchant) and leave the code unchanged:

```
ESEWA_PRODUCT_CODE=...
ESEWA_SECRET_KEY=...
ESEWA_FORM_URL=https://epay.esewa.com.np/api/epay/main/v2/form
ESEWA_STATUS_URL=https://esewa.com.np/api/epay/transaction/status/
```

## Limits

- Refunds are not implemented. Cancelling a paid order does not refund it.
- Stock is reduced when the order is placed (existing behavior), even if payment is not completed.
