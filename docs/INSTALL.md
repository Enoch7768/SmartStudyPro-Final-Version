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

## Cockpit

Cockpit remains a separate application and authentication system. SmartStudyPro does not currently provide Cockpit SSO.

Set the Cockpit reset values if you want the SmartStudyPro admin area to reset the configured Cockpit administrator:

\`\`\`env
COCKPIT_ADMIN_EMAIL=
COCKPIT_RESET_PASSWORD=
\`\`\`

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
| Cockpit | Required for CMS | Required for CMS |
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
