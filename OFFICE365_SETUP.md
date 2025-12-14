Office365 (Microsoft 365) SMTP setup and testing

Overview
You can send mail from your website using your Microsoft 365 mailbox (admin@provistaconsultants.com). This uses your existing mailbox and avoids third-party costs.

1) Enable SMTP AUTH or create an app password
- If your tenant has SMTP AUTH disabled, an admin can enable it for the organization or the specific mailbox.
- If the mailbox has MFA enabled, create an app password for SMTP (or use an Exchange Online-enabled app password alternative) so SMTP clients can authenticate.

2) Set environment variables (recommended)
- On your server (or locally for testing), set the following environment variables:

  export O365_SMTP_USER="admin@provistaconsultants.com"
  export O365_SMTP_PASS="your-mailbox-or-app-password"

- Do NOT commit these values to Git.

3) Local testing
- Start the website locally after exporting the variables:

  export O365_SMTP_USER="admin@provistaconsultants.com"
  export O365_SMTP_PASS="xxxxxxxxxxxxxxxx"
  php -S localhost:8001 -t .

- Open http://localhost:8001 and submit the contact form. The email should be sent to admin@provistaconsultants.com.

4) Production deployment
- Add `O365_SMTP_USER` and `O365_SMTP_PASS` to your host's environment/secret settings (e.g., in your VM, container, or hosting provider control panel).
- Restart the service/process so the variables are available to PHP.

5) Troubleshooting
- If authentication fails, verify:
  - Username and password are correct.
  - SMTP AUTH is allowed for the mailbox/tenant.
  - Network allows outbound connections to smtp.office365.com on port 587.
- Check mail logs and Microsoft 365 admin center sign-in logs for blocked authentication attempts.

Security & deliverability notes
- For production, prefer using an app password or service account dedicated to sending site mail.
- Consider domain authentication (SPF/DKIM) if sending from a custom domain for better deliverability.
- Never store secrets in source control. Use environment variables or a secrets manager.
