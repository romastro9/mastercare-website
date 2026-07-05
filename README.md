# MasterCare Premium Website v2

Premium clinic website for **MasterCare Cambodia / MasterCare Premium** with PHP admin panel and JSON storage.

## v2 Updates

- Premium homepage design upgrade
- Khmer / English language switch
- Admin first-time setup password
- Admin password change section
- Real image upload for logo, hero banner, services, promotions, products, and before/after gallery
- Edit button for services, promotions, products, and gallery
- Show / hide active control
- Booking request management
- Floating phone and Messenger buttons
- Mobile navigation menu

## Files

- `index.php` — public website
- `admin.php` — admin dashboard
- `config.php` — storage and upload config
- `assets/style.css` — website/admin design
- `assets/app.js` — mobile menu script
- `data/site.json` — website content database
- `uploads/` — uploaded images

## Install on Anajak / PHP Webserver

Upload all files into:

```text
/home/container/www
```

Required structure:

```text
/home/container/www/index.php
/home/container/www/admin.php
/home/container/www/config.php
/home/container/www/assets/style.css
/home/container/www/assets/app.js
/home/container/www/data/site.json
/home/container/www/uploads/
```

Set permissions:

```bash
chmod -R 775 /home/container/www/data /home/container/www/uploads
chmod 664 /home/container/www/data/site.json
```

Open website:

```text
http://my.anajak.cloud:24151
```

Open admin:

```text
http://my.anajak.cloud:24151/admin.php
```

## Admin Login

In v2, the first time you open `admin.php`, it asks you to create the admin username and password. This is safer than storing a default password in the source code.

## Important

Use `http://` for the Anajak port URL unless you connect a real custom domain and SSL.
