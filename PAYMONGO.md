# PayMongo online payments

PayMongo is available in **System Settings → PayMongo**. POS payment controls remain unchanged.

## Setup

The database migration is installed with `php tools/migrate-paymongo.php --apply`. It creates dedicated configuration, account-method and online-payment tables. It also allows sales without a cashier so verified online payments are not attributed to a staff member or cash drawer. Existing sales are preserved.

1. Enter matching test public and secret keys. The application address is detected automatically; there is no Public application URL field. Behind a reverse proxy, deployment administrators can set the canonical address using `APP_URL` or `public_url` in private application configuration. Forwarded host headers are not trusted automatically.
2. Save to validate the secret key and synchronize the account's active payment methods. The public key's format and mode are checked; hosted checkout authenticates using the secret key.
3. Select Enabled and save. The application creates or reuses the `checkout_session.payment.paid` webhook and encrypts its signing secret. Alternatively, register the displayed endpoint in PayMongo and supply its signing secret.
4. Use a separate development/staging database for test mode: successful test payments create test sales in that database and do not collect real funds. Complete a test order and verify a paid payment, duplicate delivery, cancellation and payment-status check before entering live keys. Outstanding checkouts must be resolved before switching keys or modes.

The public webhook endpoint is `<application URL>/ajax/paymongo_webhook.php`. Localhost cannot receive PayMongo notifications; use a public HTTPS deployment or test tunnel. A redirect or a client-supplied status never proves payment. Customers can also request a server-side check from order tracking, and a return from hosted checkout triggers that check automatically.

## Secrets and operations

- Secret and webhook keys are encrypted with AES-256-GCM. Back up `config/paymongo.key` securely alongside encrypted database backups. It is excluded from version control and the web server blocks the config directory. Losing this key prevents access to saved credentials and payment snapshots.
- Disabling PayMongo immediately prevents new online checkouts without requiring an API connection. Existing callbacks and status checks continue to work.
- Synced methods are keyed by environment. Removed methods become unavailable; their history is retained. Saving settings refreshes capabilities.
- Creation retries preserve the original payload, credentials and idempotency key. Ambiguous requests are not retried after 23 hours, within PayMongo's 24-hour key lifetime; investigate these with PayMongo before accepting another payment.
- A pending online checkout blocks counter payment and order cancellation. Customers and settings administrators can expire an unpaid checkout; the app releases the reservation only after PayMongo confirms expiration. This does not cancel the food order or restore its reserved stock.
- A verified payment creates one completed sale, copies the original order items and links their existing stock consumption. It does not deduct stock twice or post cash movements to a cashier drawer.
- The settings page lists recent attempts and lets administrators check or expire sessions. A conflicting paid transaction is retained for review rather than creating a duplicate sale.
- Gateway refunds are not initiated by local POS/history refund buttons. Refunds and their financial reconciliation must be handled through PayMongo and an administrator. Automatic refund synchronization and partial-refund reporting are outside this implementation.

## Verification

`php tools/tests/burger-integration.php --paymongo-only` runs isolated gateway tests using a mocked transport. The full integration suite also runs these checks. Tests never contact PayMongo or change source business data. Real merchant connectivity, webhook delivery and live settlement require the merchant's credentials and public HTTPS deployment and cannot be demonstrated with mocked tests.

Official references: [account capabilities](https://docs.paymongo.com/docs/account-settings-account-capabilities), [hosted checkout](https://docs.paymongo.com/docs/payment-channels-hosted-checkout), [webhook verification](https://docs.paymongo.com/docs/developer-tools-webhook-setup-management), [idempotent requests](https://docs.paymongo.com/reference/idempotent-requests).
