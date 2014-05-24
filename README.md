php-rsvp
========

php-rsvp is a LAMP package for creating websites for accepting RSVPs to weddings and other events.

Guests can RSVP by requesting `rsvp.php?k=[key]` where `[key]` is either randomly generated or chosen randomly from a list of supplied options.

Features
--------

Implemented Features:
- Parties with multiple guests
- Meals, selected during RSVP process
- Emails collected for sending updates parties
- Ability to delete guests, parties, and meals
- Display of aggregate data (guests invited, total accepted, total per meal type)
- Random generation of URL keys
- Ability to supply preferred URL keys
- Generation of URLs to distribute to potential guests
- QR codes generated with RSVP URL (by default via qrencode binary: http://fukuchi.org/works/qrencode/index.html.en)
- Confirmation/Thank-you email sent after RSVP

Planned Features:
- Ability to edit guests, parties, and meals (can currently only add/delete)
- Google Calendar invitations sent with confirmation email

Additional possibilities
------------------------
The "rsvp.php?k=" part of the URL isn't the most attrative.

You can circumvent this by placing a .htaccess file in the www directory
containing:

```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l
RewriteRule ^([^/]+)$ /rsvp.php?k=$1 [B,L]
```

This will take your URL path and, assuming it does not reference a valid file,
assumes that it should be used as a URL key.

For example, for:

```
http://example.com/f8eCvyrI
```

Since 'f8eCvyrI' is probably not a file on your server, this will automatically
be translated by apache (via the .htaccess file) into:

```
http://example.com/rsvp.php?k=f8eCvyrI
```

