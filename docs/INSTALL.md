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
