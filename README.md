# MasterCare Premium Website

Premium clinic website for **MasterCare Cambodia / MasterCare Premium** with a simple PHP admin panel.

## What is included

- Premium responsive clinic homepage
- Khmer / English content structure
- Services, promotions, products, and before/after sections
- Booking form
- Admin login
- Admin panel to edit website information
- Admin panel to add/edit/delete services, promotions, products, gallery items, and bookings
- JSON storage, no MySQL required

## Important

GitHub can store the website source code, but **GitHub Pages cannot run PHP admin panel**. To use the admin panel online, upload these files to PHP hosting or cPanel.

## Admin Login

Default admin:

```text
Username: admin
Password: MasterCare@2026
```

Change the password in `admin.php` before using the website for real customers.

## Install on cPanel

1. Download or clone this repository.
2. Upload all files to `public_html`.
3. Make sure these folders are writable:
   - `data`
   - `uploads`
4. Open your domain to view the website.
5. Open `/admin.php` to login to the admin panel.

## Security note

Do not keep the default admin password on a live website. Upload only customer images that you have permission to use.
