SendGrid setup and local testing

1) Create SendGrid API key
- Sign up / log in to SendGrid: https://app.sendgrid.com/
- Go to Settings → API Keys → Create API Key
- Give it a name (e.g. "Provista Website SMTP") and grant "Mail Send" permission
- Copy the generated key (starts with `SG.`)

2) Configure on your server (recommended)
- Set an environment variable `SENDGRID_API_KEY` with the key value.
  Example (bash):

  export SENDGRID_API_KEY="SG.XXXXXXXX..."

- If running under a process manager (systemd, pm2, etc.) add the env var to the service config.

3) Local development (optional)
- Do NOT commit your real API key. Use `.env.example` as a template.
- Create a file named `.env` in the project root with the line:

  SENDGRID_API_KEY=SG.YOUR_SENDGRID_API_KEY_HERE

- Load env vars before starting the PHP server. Example on macOS / Linux:

  export SENDGRID_API_KEY="SG.XXXXXXXX..."
  php -S localhost:8001 -t .

4) Test the contact form
- Start the local server and open http://localhost:8001
- Go to the Contact section and submit the form.
- Email should be sent to `admin@provistaconsultants.com` with subject:
  `[Website Message] <subject> — YYYY-MM-DD HH:MM:SS` (timestamp in Asia/Riyadh timezone)

5) Troubleshooting
- If emails are not delivered, check:
  - The API key value and permissions
  - SMTP port/network access (port 587 must be allowed)
  - Your server mail logs or SendGrid activity feed
- For production, consider SPF/DKIM configuration and using SendGrid's domain authentication for better deliverability.

Security note
- Never commit real API keys to version control. Use environment variables or a secrets manager.
