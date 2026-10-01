# SmartStudyPro Setup

## Requirements

- Windows
- XAMPP with Apache and PHP
- PHP 8.2 or newer
- SQLite PDO extension
- A Google OAuth web client for Google sign-in
- A Gemini API key for AI features
- DPO Pay credentials when production payment processing is implemented

## Fastest Local Setup

Open PowerShell in the SmartStudyPro project folder and run:

\`\`\`powershell
Set-ExecutionPolicy -Scope Process Bypass
.\setup.ps1
\`\`\`

The script creates the database directory, creates .env from .env.example when needed, generates APP_SECRET, creates private learning storage outside the project web root, and checks PHP syntax.

Start Apache in XAMPP and open:

\`\`\`
http://localhost/SmartStudyPro/
\`\`\`

## Environment

The payment switch is:

\`\`\`env
PAYMENT_MODE=demo
\`\`\`

Use demo for local testing. Demo mode intentionally confirms test payments without contacting a payment provider.

Use:

\`\`\`env
PAYMENT_MODE=production
\`\`\`

only after the production DPO Pay integration is configured and verified. The current application deliberately refuses to simulate a successful payment when production mode is enabled.

## Google Sign-In

Set:

\`\`\`env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
\`\`\`

Create a Google OAuth Web Application and add this local redirect URI:

\`\`\`
http://localhost/SmartStudyPro/google-auth.php
\`\`\`

The redirect URI must exactly match the URI configured in Google Cloud.

## Gemini

Set:

\`\`\`env
GEMINI_API_KEY=
\`\`\`

Restart Apache after changing the environment file.

## SmartStudyPro CMS

SmartStudyPro includes its own SQLite-backed content management system at `/cms`. The protected administration area is available at `/admin`, and `/cms` uses the same admin session. It manages the site's courses, lessons, quizzes, products, pages, settings and media without a separate CMS account.

For an existing installation that still contains legacy Cockpit data, run `php scripts/migrate-cockpit-to-smartstudypro-cms.php` once. The migration copies content into `database/cms.db` and does not delete the original content. After verifying the native CMS, the legacy Cockpit runtime can be removed in a separate cleanup step.

## CMS Images

Cockpit media under:

\`\`\`
cms/storage/uploads/
\`\`\`

is public CMS media and is intentionally allowed to load normally.

Do not store protected paid learning resources there.

Protected learning resources belong in:

\`\`\`
SmartStudyProPrivateLearning/
\`\`\`

The application serves those resources through authorization-controlled endpoints.

## Existing Learning Resources

After updating an existing installation, migrate existing paid resources with:

\`\`\`powershell
php scripts/migrate-protected-learning.php
\`\`\`

The migration copies eligible resources to private storage and updates their database paths. It does not delete the original CMS files automatically.

## Configuration Summary

| Setting | Demo | Production |
|---|---|---|
| APP_ENV | local | production |
| APP_DEBUG | false | false |
| PAYMENT_MODE | demo | production |
| Google Sign-In | Optional | Configure |
| Gemini | Optional | Configure |
| Cockpit | Only needed during legacy-content migration | Not required by the native CMS |
| Protected storage | Required for paid resources | Required for paid resources |

## Security

Never commit .env.

Never place API keys, OAuth secrets, DPO credentials, or admin password hashes in public source files.

Do not put paid learning files back into public CMS uploads after migration.


## DPO Pay Production Flow

SmartStudyPro uses DPO Pay's hosted checkout. Card and payment details are handled by DPO rather than stored by SmartStudyPro.

The production flow is:

1. Customer reviews unpaid bookings.
2. SmartStudyPro calculates the amount from server-side booking records.
3. SmartStudyPro creates a DPO createToken transaction.
4. DPO returns a transaction token.
5. Customer is redirected to DPO's hosted payment page.
6. DPO returns the customer to `payment-return.php`.
7. SmartStudyPro calls DPO `verifyToken` server-side.
8. The amount and currency are compared with the original transaction.
9. Only a verified successful DPO result marks bookings as paid.
10. `payment-callback.php` can also verify and reconcile the transaction.
11. Repeated callbacks and returns are safe because the payment record and booking update are handled idempotently.

DPO documents `000` as the successful transaction result. Results such as `900` pending, `901` declined, `903` expired, and `904` cancelled must not grant paid access. citeturn2search2turn3view0

DPO's documented v6 API endpoint is `https://secure.3gdirectpay.com/API/v6/`. The API uses XML and requires the merchant Company Token. DPO's hosted payment page uses the returned transaction token. citeturn2search3turn3view0

### DPO environment

Set:

```env
PAYMENT_MODE=production
DPO_PAY_BASE_URL=https://secure.3gdirectpay.com/API/v6/
DPO_PAY_COMPANY_TOKEN=your_company_token
DPO_PAY_SERVICE_ID=your_dpo_service_type
DPO_PAY_CURRENCY=UGX
```

Your DPO service type is merchant-specific. Do not use a test service ID for production.

Set `APP_URL` to the public HTTPS URL of SmartStudyPro:

```env
APP_URL=https://your-domain.example
```

DPO must be able to reach the return and callback endpoints. Localhost URLs cannot be used for a real external DPO production callback.

The application derives these URLs automatically:

```text
https://your-domain.example/payment-return.php
https://your-domain.example/payment-callback.php
```

### Testing

For local UI/application testing:

```env
PAYMENT_MODE=demo
```

Demo mode does not contact DPO and is intentionally separate from real payment processing.

For DPO sandbox testing, use the sandbox credentials issued/documented by DPO and place the supplied Company Token and Service Type in `.env`. DPO documents sandbox/test merchant accounts separately. citeturn2search5

### Important

Do not mark a booking as paid from browser return parameters alone. SmartStudyPro verifies the transaction with DPO before granting paid access. DPO documents `verifyToken` as the transaction status lookup and requires verification when the customer returns. citeturn3view0


## Administration

Open:

```text
http://localhost/SmartStudyPro/admin
```

The `/admin` route is the administration entry point. `admin.php` remains the implementation behind the clean route.

After signing in, use the SmartStudyPro CMS link to open `/cms`.

## Clean URLs and Apache

SmartStudyPro uses Apache `mod_rewrite` through `.htaccess`. Apache documents that `.htaccess` directives are ignored when `AllowOverride None` is active. citeturn0view0

For a normal XAMPP installation, verify that `httpd.conf` allows overrides for the SmartStudyPro directory:

```apache
<Directory "C:/xampp/htdocs">
    AllowOverride All
    Require all granted
</Directory>
```

Also make sure Apache has `mod_rewrite` enabled, then restart Apache.

Expected routes:

```text
/                 -> Home
/about            -> About
/courses          -> Courses
/products         -> Products
/contact          -> Contact
/booking          -> Booking
/login            -> Login
/register         -> Register
/admin            -> Admin
/cms              -> CMS
/cms/media        -> Media Library
```

Unknown routes are sent to the SmartStudyPro branded 404 page.

If the home page works but every clean URL returns Apache's default 404 page, check `AllowOverride` and `mod_rewrite` first. Apache can serve `index.php` as a directory index even when the application's rewrite rules are not being applied. citeturn0view0

## Custom Error Pages

SmartStudyPro provides branded handling for common HTTP errors through `error.php`. Supported pages include 400, 401, 403, 404, 405, 408, 409, 410, 413, 415, 422, 429, 500, 501, 502, 503 and 504.

Each error receives a SmartStudyPro-specific message, HTTP status code, unique support code, safe request path, and server-side diagnostic logging. User-facing pages do not expose exception traces or sensitive server details.

Error logs are stored under:

```text
storage/logs/errors.log
```
