# Security Practices

This document describes security measures applied in the application. **Do not trust client-side input**; all validation and type-checking must be done on the server.

## CSRF (Cross-Site Request Forgery) protection

- **Token-based defense**: The app uses a unique, unpredictable nonce per session (see `includes/csrf.php`). Every state-changing form includes the token via `csrf_field()` and every POST handler validates it with `csrf_require()` or `csrf_validate()`.
- **No state change over GET**: Do **not** use the GET method for any request that triggers a state change. GET is used only for safe operations (e.g. viewing the cart, listing products). All mutations (login, register, profile updates, checkout, add/remove cart, admin actions) use POST.
- **Optional Referer check**: `csrf_validate(['check_referer' => true])` can be used to require same-origin Referer. This may break legitimate traffic if users or proxies disable the Referer header; use only where acceptable.
- **XSS**: CSRF defenses can be bypassed via XSS. Keep the application free of cross-site scripting (escape output, use Content-Security-Policy where possible).

## XSS (Cross-Site Scripting) prevention

- **Output encoding**: All dynamic data echoed into HTML (body or attributes) must be escaped. Use the `h()` helper from `includes/validation.php`: `echo h($value);`. It wraps `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. Use for any value from the database or from `$_GET`/`$_POST`/`$_REQUEST` when rendering HTML.
- **Session cookie hardening**: Session cookies are set with **HttpOnly** (so JavaScript cannot read them, mitigating cookie theft via XSS) and **SameSite=Lax** (reduces CSRF from cross-site requests). See `includes/session.php`; all entry points use it instead of calling `session_start()` directly.
- **Character encoding**: Every HTML page should declare charset (e.g. `<meta charset="UTF-8">` in `<head>`). When the encoding is not specified, the browser may guess and treat sequences as special, enabling subtle XSS (CWE-116).
- **Assume all input is malicious**: Validate and sanitize on the server; use allow-lists for known-good values. Escape on output; do not rely on client-side checks alone.

## Content-Security-Policy (CSP) and security headers

- **Content-Security-Policy** is set to restrict script, style, font, image, and other sources, reducing XSS and injection risk.
- **Where it is set**:
  - **Apache (XAMPP / typical hosting)**: Root `.htaccess` sets CSP, `X-Content-Type-Options`, `X-Frame-Options`, and `Referrer-Policy` (requires `mod_headers`).
  - **PHP fallback**: `includes/security_headers.php` sends the same headers when included (e.g. via `includes/db.php`). Use this when the server does not set these headers (e.g. `mod_headers` disabled or PHP built-in server).
- **Allowed origins**: Same-origin; `https://cdn.jsdelivr.net` (Bootstrap, Icons, SweetAlert2); `https://code.jquery.com`; `https://fonts.googleapis.com` and `https://fonts.gstatic.com`. Inline scripts and styles are allowed (`unsafe-inline`) for current Bootstrap/legacy usage; consider moving to nonces or hashes later for a stricter policy.
- **Directives with no fallback**: The policy explicitly sets `frame-src`, `worker-src`, `media-src`, and `manifest-src` (each `'self'`) so that browsers do not treat missing directives as "allow anything"; this satisfies CSP audits and reduces XSS/injection surface.
- **Load balancer / reverse proxy**: If the app runs behind a proxy or load balancer, configure CSP (and other security headers) there as well so they are applied consistently and cannot be bypassed.

## Input validation and SQL safety

- **Server-side validation**: All `$_GET`, `$_POST`, and `$_REQUEST` inputs are validated and type-checked on the server. See `includes/validation.php` for helpers (`validate_int`, `validate_id`, `validate_enum`, `validate_order_dir`, `sanitize_string`, `sanitize_string_allowlist`).
- **Parameterized queries**: The application uses **PDO prepared statements** with placeholders (`?` or `:name`). User input is never concatenated into SQL strings.
- **Allow-lists**: Sort columns, sort direction (ORDER BY), and filter values use allow-lists so only known-good values are used in queries or logic.
- **Escape/sanitize**: Text from the client is sanitized (length limits, strip tags, allow-list of characters) before use in DB or output.

## Database access (principle of least privilege)

- **Do not use** the `sa`, `root`, or `db_owner` database user for the application.
- Use a **dedicated, least-privileged database user** that has only the permissions the app needs:
  - `SELECT`, `INSERT`, `UPDATE`, `DELETE` on the application’s tables only.
  - No `DROP`, `CREATE`, `ALTER`, or schema changes unless required.
  - No `EXEC` or execution of dynamic SQL in stored procedures.
- Grant the **minimum database access** necessary (per table and per operation). This does not remove SQL injection risk by itself but **limits the impact** if something is compromised.
- Store database credentials in environment variables or a secure config file outside the web root; do not commit real credentials to version control.

## Stored procedures (if used later)

- Prefer stored procedures with **parameterized arguments**.
- **Do not** build dynamic SQL inside procedures with string concatenation, and do not use `EXEC`, `EXECUTE IMMEDIATE`, or equivalent with user input.

## Files updated for security

- `.htaccess` (project root) – Content-Security-Policy, X-Content-Type-Options, X-Frame-Options, Referrer-Policy.
- `includes/security_headers.php` – PHP fallback for the same security headers when the server does not set them.
- `includes/csrf.php` – CSRF token generation (unpredictable nonce), `csrf_field()`, `csrf_validate()`, `csrf_require()`; optional Referer check.
- `includes/validation.php` – includes `csrf.php`; shared validation, sanitization, and output encoding (`h()` for XSS-safe HTML).
- `includes/session.php` – session cookie params (HttpOnly, SameSite, Secure) and session start; use instead of `session_start()`.
- **All state-changing forms** now include anti-CSRF tokens and server-side validation: login (`index.php`), register (`register.php`), admin and user profile, admin products/price updates, user cart and checkout (`user/products.php` – add to cart, remove from cart, checkout), admin manage accounts, suppliers (add/edit/delete), promotions (add/edit), sales (mark completed), customers (add/edit), loyalty program (edit member, save settings), and pricing history (add/edit/delete via AJAX). AJAX POST requests send the token in the request body (`_csrf_token`) or via a meta tag and JavaScript.
- `AJAX/get_audit_logs.php` – validated POST params; ORDER BY uses allow-listed column and direction only.
- `admin/suppliers.php` – type-check and sanitize all POST input; validate IDs.
- `admin/audit_log.php` – allow-listed filter and validated page.
- `admin/manage_accounts.php` – allow-listed filter/role; validated user ID and page.
- `admin/customers.php` – allow-listed GET actions; validated IDs; sanitized POST for add/edit.
- `AJAX/check_username.php` – sanitized username before DB check.
