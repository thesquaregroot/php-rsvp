php-rsvp
========

php-rsvp is a LAMP package for creating websites for accepting RSVPs to weddings and other events.

Guests can RSVP by requesting `rsvp.php?k=[key]` where `[key]` is either randomly generated or chosen randomly from a list of supplied options.

Features
--------

Implemented Features:
- Parties with multiple guests
- Meals selected during RSVP
- Option for parties to leave a note during RSVP
- Emails collected during RSVP for sending updates parties
- Ability to delete guests, parties, and meals
- Display of aggregate data (guests invited, total accepted, total per meal type)
- Random generation of URL keys
- Ability to supply preferred URL keys
- Generation of URLs to distribute to potential guests
- QR codes generated with RSVP URL (by default via qrencode binary: http://fukuchi.org/works/qrencode/index.html.en)
- Confirmation/Thank-you email sent after RSVP
- Set an final date for accepting RSVPs
- Get lists of email addresses of guests that have replied

Planned Features:
- Ability to edit guests, parties, and meals (can currently only add/delete)
- Google Calendar invitations sent with confirmation email

Dependancies & Installation
---------------------------
php-rsvp depends on:

- apache/httpd
- php5 (required: >=5.0, recommended: >=5.5)
- mysql
- jQuery
- jQuery-UI

It is recommended that you have apache, php5, and mysql working together prior to starting the installation.

Download the package to your destination of choosing and set the document root to to the `www/` directory in the project.  This will ensure that files containing sensitive information cannot be accessed by apache.

To start the installation navigate to `rsvp_admin.php` in order to set up the database.  As you will necessisarily
be supplying sensitive information in order to create the required schema it is recommended you perform this step
on the local network of the server you are installing this on or with an SSL encrypted connection.

At this page you will create the schema/tables needed, an account to access the database, and an initial admin
account.  After submitting the form you will hopefully see a success message and be instructed to add the database
password to the `include/rsvp_config.php` file.  This file is where much, if not all, of the customization of the
website should occur.  Inspect this file, secure it as necessary, add the password, and save it.

With the setup complete you should be able to log in to the admin page.  However, at this point you should download
your preferred versions of `jQuery` and `jQuery-UI` and install them into `www/js/` and `/www/css/`.  At this point
your should be ready to start configuring your event (in `include/rsvp_config.php`) and using the system.

Additional possibilities
------------------------
The "rsvp.php?k=" part of the URL isn't the most attrative.

You can circumvent this by placing a .htaccess file in the www directory
containing:

```
Options +FollowSymlinks
RewriteEngine on

# always send 404 on missing favicon
RewriteRule ^favicon.ico$ favicon.ico [L]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l
RewriteRule ^([^/]+)$ rsvp.php?k=$1 [QSA,B,L]
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

