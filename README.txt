RHON'S FADE - FULL BOOKING SYSTEM (PHP + MySQL)

PAGES
  user-login.php      Customer login (with "Forgot Password?")
  user-register.php   Create a customer account
  forgot-password.php / reset-password.php   Reset a forgotten password
  user-home.php       Customer home page
  services.php        Services page (images, prices, Book Now) - visible without login
  booking.php         Book an appointment (blocks past dates and already-booked times)
  my-bookings.php     Customer's bookings + Cancel Booking
  admin-login.php     Admin login
  admin-dashboard.php Admin: stats, bookings (confirm/cancel), customers,
                      services (upload / remove an image per service)
  logout.php          Log out (customer or admin)

SETUP (XAMPP)
1. Copy this whole folder to C:\xampp\htdocs\rhons_fade\
2. Put your logo at images\logo.png (not included).
3. Start Apache and MySQL in the XAMPP control panel.
4. phpMyAdmin (http://localhost/phpmyadmin):
   - NEW database:      Import > database/schema.sql > Go
   - EXISTING database: SQL tab > run database/update.sql once
     (keep your own config/db.php if yours already works)
5. Open http://localhost/rhons_fade/services.php or user-login.php

DEFAULT ADMIN (fresh install only)
  admin@rhonsfade.com / admin123   - change it after first login.

SERVICE IMAGES
  Admin login > Services > choose a file > Upload image. One service at a time.
  JPG, PNG or WEBP, up to 3 MB. Files are saved in images/services/.

FORGOT PASSWORD
  forgot-password.php shows the reset link on screen for local testing.
  Before going online, set $SHOW_LINK_ON_SCREEN = false at the top of that file
  and set up email sending.
