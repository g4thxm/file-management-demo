# Security status

This is a local demonstration and source-review edition. It is not approved for production hosting or a public tunnel.

## Known limits

- Laravel 9.52.20 is outside security support. The unchanged dependency lock has known advisories in 16 production package groups, 18 including development dependencies, in the checked audit. Package matches are not proof that every issue is exploitable in this app.
- Moving to a maintained framework requires integration updates and regression testing. A constraints-only update did not produce a supported, advisory-free stack.
- Authorization, attachment validation, generated-output paths, legacy dispatch and deployment settings need a separate hardening review before real records or untrusted users are introduced.
- The local test suite is bounded functional evidence, not an exhaustive security audit.

## Safe use of this edition

Use invented data and a new local database. Keep debug disabled, bind the server to 127.0.0.1, use the log mailer, choose your own demo password and keep .env private. Never import production data or real documents into this demonstration.

Do not publish credentials, access tokens, private records or an exploit against a live service in an issue. Arrange a private reporting channel with the repository owner for sensitive findings.

## Framework references

- https://laravel.com/framework/docs/9.x/releases
- https://laravel.com/framework/docs/releases
