# Deploying to InfinityFree

How to put AAR9SPEED online on InfinityFree's free hosting, and keep it updated
automatically from GitHub. Takes about an hour the first time.

Free hosting suits a demo or a small shop. Labels in InfinityFree's panels change
from time to time, so if a button below has a slightly different name, look for
the closest match.

## What you need

- A GitHub account with this repository (CI must be passing on `main`).
- The files from your computer: `aar9speed.sql`, the `migrations/` folder, and the
  `images/` folder (product photos, which are not in git).
- An FTP program such as [FileZilla](https://filezilla-project.org/) (free).

## 1. Create the hosting account

1. Sign up at [infinityfree.com](https://www.infinityfree.com/).
2. In the client area, **Create account** and pick a free subdomain, for example
   `aar9speed.infinityfreeapp.com`.
3. Open the account's **Control Panel**. Under the PHP settings, choose
   **PHP 8.2 or newer**. The app does not run on PHP 7.

## 2. Create and fill the database

1. Control Panel → **MySQL Databases** → create a database (e.g. `aar9speed`).
2. Note the four values shown for it. You need them in step 4.

   | Setting | Looks like |
   |---|---|
   | MySQL host | `sql123.infinityfree.com` (not `localhost`) |
   | Database name | `if0_12345678_aar9speed` |
   | Username | `if0_12345678` |
   | Password | your hosting account password |

3. Click **phpMyAdmin** next to the database, open the **Import** tab and import
   these files **one at a time, in this order**:
   1. `aar9speed.sql`
   2. `migrations/002_improvements.sql`
   3. `migrations/003_automation.sql`
   4. `migrations/005_widen_contact_fields.sql`
   5. `migrations/006_payment_method.sql`

   If importing `aar9speed.sql` fails at a line about **TRIGGER**, your plan does not
   allow triggers. Remove the `CREATE TRIGGER … DELIMITER ;` block near the
   "Triggers `item`" comment, drop the half-imported tables, and import again. The
   app marks sold-out products itself, so nothing is lost.

## 3. Get your FTP details

Client area → your account → **FTP Details**. Note:

| Setting | Looks like |
|---|---|
| FTP hostname | `ftpupload.net` |
| FTP username | `if0_12345678` |
| FTP password | your hosting account password |
| Folder for the website | `htdocs` |

## 4. Upload the private files once (by hand)

These files never go through git. Connect with FileZilla, open `htdocs`, and upload:

1. **`.env`**: create it on your computer from `.env.example` with the live values:

   ```
   DB_HOST=sql123.infinityfree.com
   DB_USER=if0_12345678
   DB_PASS=your-hosting-password
   DB_NAME=if0_12345678_aar9speed
   LOW_STOCK_THRESHOLD=5
   APP_TIMEZONE=Asia/Manila
   DAILY_REPORT_TIME=21:00
   SCHEDULER_INTERVAL_MINUTES=5
   CRON_SECRET=paste-a-long-random-string
   DISCORD_WEBHOOK_URL=
   TELEGRAM_BOT_TOKEN=
   TELEGRAM_CHAT_ID=
   ```

   Make a random `CRON_SECRET` with `php -r "echo bin2hex(random_bytes(24));"`.

2. **The `images/` folder**, including its hidden `.htaccess` file (in FileZilla:
   *Server → Force showing hidden files*). That file stops scripts running from
   the photo folder.

`.htaccess` in the main folder blocks `.env`, the SQL dump and the server-side
folders from being downloaded. It is uploaded by the deploy in step 6.

## 5. Add the deploy secrets to GitHub

GitHub → your repository → **Settings → Secrets and variables → Actions →
New repository secret**. Add:

| Name | Value |
|---|---|
| `FTP_SERVER` | `ftpupload.net` |
| `FTP_USERNAME` | your FTP username |
| `FTP_PASSWORD` | your FTP password |

Optional: `FTP_SERVER_DIR` if the website folder is not `htdocs/`.

## 6. Deploy

Push to `main` (or merge a pull request). The **CI** workflow runs the lint and
smoke-test jobs; if both pass, the **Deploy to hosting (FTP)** job uploads the
changed files. Check it under the repository's **Actions** tab.

The first deploy uploads everything and can take several minutes on free hosting.
Later deploys only send changed files. Until the `FTP_SERVER` secret exists, the
deploy job skips itself with a message instead of failing.

## 7. Turn on HTTPS

1. Client area → **Free SSL Certificates** → request one for your subdomain and
   follow the steps until it shows as issued.
2. Open `https://your-subdomain/login.php`. When it loads with the padlock, edit
   `.htaccess`: remove the `#` from the four lines under "Force HTTPS", then push.

## 8. Secure the admin account

Sign in as `admin` and **change the password straight away**: top-right menu →
**Update my account**. The seeded password is known to anyone with the repository.

## 9. Check it

- Sign in, make a small test sale, open its receipt, then refund it.
- These must **not** download (expect 403 or 404):
  `https://your-subdomain/.env`, `/aar9speed.sql`, `/config/Database/Database.php`.

## Automations on free hosting

InfinityFree shows a browser check to visitors that scripts and cron services
cannot pass, so calling `cron.php` from cron-job.org or GitHub Actions usually
returns that check page instead of running. The built-in fallback still works:
while anyone has the app open, the notification bell runs due automations at most
every `SCHEDULER_INTERVAL_MINUTES`. To test alerts once Discord or Telegram is set up, open
`https://your-subdomain/cron.php?key=YOUR_CRON_SECRET&task=test` in your browser.

## Updating the database later

New `migrations/*.sql` files are not run by the deploy. Import each new one in
phpMyAdmin before (or right after) pushing the code that needs it.
