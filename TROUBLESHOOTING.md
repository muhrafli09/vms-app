# VMS Application - Troubleshooting Guide

## User Invitation & Registration Issues

### Problem: 404 Error on Registration Link

**Symptoms:**
- Clicking invitation link shows 404 error
- URL format: `/register?token=xxx&signature=xxx`

**Causes:**
1. **Invitation already used/deleted** - Once user registers, invitation is deleted
2. **Expired signed URL** - Signed URLs have expiration time
3. **Invalid signature** - URL was modified or APP_KEY changed
4. **Old invitation** - Token no longer exists in database

**Solutions:**

#### 1. Resend Invitation from Admin Panel
```
Admin Panel → User Invitations → Click "Resend" button
```

#### 2. Create New Invitation via Tinker
```bash
docker exec frontdesk-app php artisan tinker
```

```php
$invitation = App\Models\UserInvitation::create([
    'email' => 'user@example.com',
    'code' => substr(md5(rand(0, 9) . 'user@example.com' . time()), 0, 32)
]);

Mail::to($invitation->email)->send(new App\Mail\UserInvitationMail($invitation));
```

#### 3. Check if Invitation Exists
```bash
docker exec frontdesk-app php artisan tinker --execute="
App\Models\UserInvitation::all()->each(function(\$inv) {
    echo 'Email: ' . \$inv->email . ' | Code: ' . \$inv->code . PHP_EOL;
});
"
```

#### 4. Verify Route is Working
```bash
docker exec frontdesk-app php artisan route:list | grep register
```

Should show:
```
GET|HEAD   register   filament.app.auth.register
```

---

## Email Configuration Issues

### Problem: Emails Not Sending

**Check Current Config:**
```bash
docker exec frontdesk-app php artisan config:show mail
```

**Test Email Sending:**
```bash
docker exec frontdesk-app php artisan tinker
```

```php
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

**Update Mail Settings:**
```bash
docker exec frontdesk-app php artisan tinker
```

```php
$settings = app(\App\Settings\MailSettings::class);
$settings->mail_host = 'mail.example.com';
$settings->mail_port = '587';
$settings->mail_encryption = 'tls';
$settings->mail_username = 'user@example.com';
$settings->mail_password = 'password';
$settings->save();
```

Then clear config:
```bash
docker exec frontdesk-app php artisan config:clear
```

---

## Common Commands

### Clear All Caches
```bash
docker exec frontdesk-app php artisan optimize:clear
```

### View Logs
```bash
docker exec frontdesk-app tail -f storage/logs/laravel.log
```

### Check Database Connection
```bash
docker exec frontdesk-app php artisan db:show
```

### Restart Application
```bash
docker restart frontdesk-app
```

---

## Registration Flow

1. **Admin creates invitation** → Email sent with signed URL
2. **User clicks link** → Redirects to `/register?token=xxx&signature=xxx`
3. **System validates:**
   - Signature is valid (signed URL)
   - Token exists in `user_invitations` table
   - Email not already registered
4. **User fills form** → Creates user account
5. **Invitation deleted** → Cannot be reused
6. **User logged in** → Redirected to dashboard

---

## Security Notes

- **Signed URLs** prevent tampering with invitation links
- **One-time use** - Invitations are deleted after registration
- **Email verification** - Email is pre-filled and read-only
- **Rate limiting** - Prevents brute force registration attempts

---

## Support

For issues not covered here, check:
- Application logs: `storage/logs/laravel.log`
- Web server logs: `docker logs frontdesk-app`
- Database: `docker exec frontdesk-app php artisan tinker`
