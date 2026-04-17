# Hex & Halo

A whimsical PHP web application for generating personalized certificates with celestial themes. This project creates beautifully designed PDF certificates for "blessings, chaos licenses, and minor miracles" with QR codes and custom styling.

## About

Hex & Halo is a satirical take on bureaucratic celestial paperwork. It generates bespoke PDF documents for:
- Protection Seals
- Chaos Licenses
- Miracle Passes
- Week-long Blessings

Each certificate is personalized with your name, dates, and includes a unique QR code for verification.

## Features

- **Certificate Generation**: Create themed certificates with professional layouts
- **QR Code Integration**: Each certificate includes a scannable QR code
- **Shopping Cart System**: Add multiple certificates to cart before checkout
- **Payment Processing**: Integrated Stripe payment system
- **Responsive Design**: Mobile-friendly Bootstrap-based interface
- **PDF Export**: Generate downloadable PDF certificates using Dompdf

## Project Structure

```
├── index.php              # Landing page
├── certificate.php        # Certificate selection page
├── minor-miracles.php     # Minor miracles certificate generator
├── blessing.php           # Blessing certificate generator
├── chaos-license.php      # Chaos license generator
├── protection.php         # Protection seal generator
├── cart.php               # Shopping cart
├── checkout.php           # Payment processing
├── checkout_success.php   # Success page
├── layout.php             # Base HTML layout
├── config.php             # Main configuration
├── config.stripe.php      # Stripe configuration
├── shop-meta.json         # Shop metadata
├── halo-crypto.php        # Experimental crypto corner
├── styles/
│   └── angelic-shop.css   # Custom CSS styling
└── tmp_certs/             # Temporary certificate storage
```

## Dependencies

This project relies on several Composer packages:

- `dompdf/dompdf` - For PDF generation
- `endroid/qr-code` - For QR code creation
- `phpmailer/phpmailer` - For email functionality
- `stripe/stripe-php` - For payment processing

## Installation

1. Clone the repository
2. Run `composer install` to install dependencies
3. Configure your Stripe API keys in `config.stripe.php`:
   ```php
   <?php
   define('STRIPE_PUBLISHABLE_KEY', 'your_publishable_key_here');
   define('STRIPE_SECRET_KEY', 'your_secret_key_here');
   define('TEST_MODE', true); // Set to false for live transactions
   ?>
   ```
4. Ensure the `tmp_certs/` directory is writable
5. Serve with a PHP-enabled web server

## Usage

1. Visit the homepage to browse certificate options
2. Select a certificate type and customization options
3. Add items to your cart
4. Proceed to checkout and complete payment
5. Download your personalized certificate in PDF format

## Customization

Certificates are generated with dynamic content including:
- Customer name
- Issue date
- Unique ID
- Expiration date (where applicable)
- QR code linking to verification
- Themed artwork and copy

The styling uses a custom CSS file (`angelic-shop.css`) with a celestial theme featuring:
- Gold and purple color scheme
- Decorative elements and icons
- Responsive layout for all devices

## Development

This project was built with:
- PHP 8+
- Bootstrap 5 for frontend components
- Composer for dependency management

## License

This project is for educational purposes. All content is fictional and any resemblance to real entities is purely coincidental.

## Contributing

As this is a class project, contributions are not actively sought but the code is shared for educational purposes.