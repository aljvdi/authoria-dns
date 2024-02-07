# Project Authoria - DNS Verification

A simple DNS verification tool for the Project Authoria (Part of my personal project which we used in [Daart Digital Agency](https://github.com/daart-agency)).

This tool is used to verify the ownership of a domain by checking the custom TXT record in the DNS of the domain.

## Requirements
- sqlite3
- PHP 7.4 or higher
- Composer
- A web server (Apache, Nginx, etc.) (Production only)

## Installation

```bash
# Clone the repository
git clone git@github.com:aljvdi/authoria-dns.git

# Install the dependencies
cd authoria-dns
composer install

# Initiate the database (SQLite)
php db/init.php

# Run the server (NOTE: this is a PHP built-in server and should not be used in production. Use a proper web server like Apache or Nginx.)
php -S 127.0.0.1:{PORT}
```

## Usage

This project is designed to be used as an API. You can use the following endpoints to interact with the tool:

| Method | Endpoint            | Description                                         | Body or Query Parameters                                                  | Response                                                                                                                                                                                                                                                      |
|--------|---------------------|-----------------------------------------------------|---------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| POST   | /api/v1/new         | Create a new verification request                   | Query: `domain: string` & `ttl: int` (Optional, Based on S, default: 300) | JSON: `{"id": "verification_UUID", "domain": "domain", "TXT_record_to_verify": "authoria-dns-verification={KEY}", "expires_at": "UNIX_TIMESTAMP"}`                                                                                                            |
| GET    | /api/v1/verify/     | Verify the status of a verification request         | Query: `id: string` (e.g. `verification_UUID`)                            | JSON: `{"id": "verification_UUID", "domain": "domain", "status": "PENDING/VERIFIED/EXPIRED/NOT_FOUND"}`                                                                                                                                                       |
| POST   | /api/v1/bulk-verify | Verify the status of multiple verification requests | Body (JSON): `{"ids": ["verification_UUID1", "verification_UUID2", ...]}` | JSON: `{"verification_UUID1": {"id": "verification_UUID1", "domain": "domain", "status": "PENDING/VERIFIED/EXPIRED/NOT_FOUND"}, "verification_UUID2": {"id": "verification_UUID2", "domain": "domain", "status": "PENDING/VERIFIED/EXPIRED/NOT_FOUND"}, ...}` |

### Status Enum
- `PENDING`: The verification request is still pending.
- `VERIFIED`: The verification request has been verified.
- `EXPIRED`: The verification request has expired (user did not verify the domain within the TTL).
- `NOT_FOUND`: The verification request was not found.

## Testing
To run the tests, you can use the following command:

```bash
composer test

# or
vendor/bin/phpunit tests/test_dns.php
```

## License
This project is licensed under the MIT License - see the [LICENCE](./LICENCE.txt) file for details.