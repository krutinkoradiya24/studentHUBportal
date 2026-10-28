# Event Management System – Comprehensive Test Report & Verification

**Project Name:** Event Management System (EventHub)  
**Target Environment:** Apache 2.4 / PHP 8.2 / MariaDB (MySQL 10.4) on XAMPP  
**Application URL:** `http://localhost/event-management`  
**Database Name:** `event_db`  
**Execution Date:** October 24, 2026  
**Test Runner:** Automated Chrome Headless Puppeteer Suite & Server-Side Verification  
**Total Test Cases:** 14  
**Passed:** 14  
**Failed:** 0  
**Overall Status:** **100% PASS**

---

## Executive Summary

The **Event Management System** was subjected to end-to-end automated functional, security, and validation testing. All 10 required feature domains were tested on the live application:
1. **Admin Authentication & Dashboard Access Control**
2. **Event Creation with Image Upload**
3. **Event Directory Rendering & Poster Display**
4. **Event Update & Modification Handling**
5. **Event Deletion with Confirmation Modal**
6. **Valid Poster Upload Processing (MIME & Extension Validation)**
7. **Invalid File Type Rejection (.txt, .pdf, non-image files)**
8. **File Size Enforcement (> 2 MB Server-Side Rejection)**
9. **User-Facing Success & Error Notifications**
10. **Unauthorized Route & Operation Protection (`require_admin()` Guards)**

All 14 screenshots were captured directly from the running web application and saved into `test-report-screenshots/`.

---

## Comprehensive Test Results Table

| Test ID | Feature Tested | Testing Steps | Expected Result | Actual Result | Status | Screenshot Filename |
| :--- | :--- | :--- | :--- | :--- | :---: | :--- |
| **TC-01** | Admin Login Page | 1. Navigate to `/login.php`<br>2. Inspect UI and input fields<br>3. Input credentials (`admin` / masked password) | Login page renders securely with username & password fields; password input is masked. | Rendered modern dark UI portal with icon badges and masked password field. | **PASS** | `01-admin-login.png` |
| **TC-02** | Admin Dashboard Access | 1. Click "Sign In"<br>2. Complete session authentication<br>3. Verify redirection to `/index.php` | Redirection to dashboard with session token, event statistics, and management tables. | Successfully redirected to dashboard; displayed header, stats counters, create form, and event table. | **PASS** | `02-admin-dashboard.png` |
| **TC-03** | Successful Event Creation | 1. Complete event form (Title, Date, Time, Venue, Category)<br>2. Attach `valid_poster.jpg` (149 KB)<br>3. Submit form | Event saved to database; poster uploaded to `uploads/`; success notification displayed. | Event inserted into MySQL `events`; poster stored; redirected with "Event created successfully!" banner. | **PASS** | `03-event-creation-success.png` |
| **TC-04** | Event List with Posters | 1. View Event Directory table<br>2. Verify poster thumbnails and placeholders<br>3. Check for broken images | All events listed with titles, categories, dates, and posters loading without 404 errors. | Table rendered crisp thumbnails for events with posters and gradient fallback badges for text-only events. Zero broken images. | **PASS** | `04-event-list-with-posters.png` |
| **TC-05** | Successful Event Update | 1. Click "Edit" on target event<br>2. Update title and venue in `edit.php`<br>3. Submit update | Changes persisted to database; user redirected to dashboard with success alert. | Database record updated; redirected to `index.php?msg=Event updated successfully!`; table displays updated details. | **PASS** | `05-event-update-success.png` |
| **TC-06** | Deletion Confirmation Dialog | 1. Click "Delete" button on event row<br>2. Inspect confirmation modal | Modal dialog appears prompting for confirmation before executing deletion. | High-visibility confirmation modal (`#deleteModal`) displayed target event name with "Cancel" and "Yes, Delete Event" options. | **PASS** | `06-event-deletion-confirmation.png` |
| **TC-07** | Successful Event Deletion | 1. Click "Yes, Delete Event" in modal<br>2. Verify deletion request processing | Event deleted from database; poster safely removed; event removed from UI list. | MySQL record removed; poster safely deleted; table updated immediately; success banner displayed. | **PASS** | `07-event-deletion-success.png` |
| **TC-08** | Valid Poster Upload | 1. Attach valid JPEG image (149 KB)<br>2. Submit event creation<br>3. Inspect table thumbnail | Poster passes validation, uploads to `uploads/`, and renders in the table thumbnail. | Valid file passed MIME inspection; saved with unique hashed filename; thumbnail rendered with border glow. | **PASS** | `08-valid-poster-upload.png` |
| **TC-09** | Invalid File Type Validation | 1. Attach `invalid_file.txt` (text file)<br>2. Submit event form | Server rejects unsupported file type; shows clear error; no file stored. | Application displayed red alert: "Invalid file type. Only JPG, JPEG, PNG, and WebP images are allowed." No upload occurred. | **PASS** | `09-invalid-file-type-error.png` |
| **TC-10** | File Size Limit Validation | 1. Attach `oversized_poster.jpg` (2.6 MB > 2.0 MB limit)<br>2. Submit event form | Server rejects file exceeding 2 MB; displays file size error message. | Application displayed red alert: "File size exceeds the 2 MB limit. Please upload a smaller image." No upload occurred. | **PASS** | `10-file-size-limit-error.png` |
| **TC-11** | Success Notifications | 1. Trigger successful action (create/update/delete)<br>2. Verify alert component | High-visibility emerald success alert displayed with readable text. | Success banner rendered prominently with checkmark icon and friendly text message. | **PASS** | `11-success-messages.png` |
| **TC-12** | Error Notifications | 1. Attempt login with invalid credentials<br>2. Verify error message | Friendly error message shown without revealing sensitive system details. | Alert displayed: "Invalid username or password. Please try again." No database or technical trace exposed. | **PASS** | `12-error-messages.png` |
| **TC-13** | Unauthorized Dashboard Access | 1. Attempt direct access to `/index.php` in clean session without login | Server blocks access; redirects to login page with unauthorized warning. | HTTP 302 redirect triggered to `login.php?error=unauthorized`; warning banner displayed. | **PASS** | `13-unauthorized-dashboard-access.png` |
| **TC-14** | Unauthorized Operation Blocked | 1. Attempt direct access to protected edit route `/edit.php?id=1` while unauthenticated | Server-side guard prevents access; redirects to login portal. | Access blocked immediately by `require_admin()`; browser redirected to `login.php?error=unauthorized`. | **PASS** | `14-unauthorized-operation-blocked.png` |

---

## Detailed Feature Verification Breakdown

### 1. Admin Login & Dashboard Access Control
- **URL Tested:** `http://localhost/event-management/login.php`
- **Security Check:** Password inputs use `type="password"` masking to ensure credentials are never exposed in plaintext or captured screenshots.
- **Session Handling:** `session_start()` initializes a secure PHP session. Upon successful login (`admin` / `Admin@123`), session state `$_SESSION['user']` and `$_SESSION['role'] = 'admin'` are established.
- **Verification:** Unauthorized requests to `index.php` receive an instant 302 redirect to `login.php?error=unauthorized`.

### 2. Event Creation & Data Integrity
- **URL Tested:** `http://localhost/event-management/index.php`
- **Form Validation:** All required fields (`title`, `event_date`, `location`, `category`) are validated on the server.
- **Database Interaction:** Parameterized SQL prepared statements (`$stmt = $conn->prepare(...)`) prevent SQL injection attacks.
- **Poster Association:** Filename hashes are stored in the `poster` column and linked to the record.

### 3. Event Directory & Media Display
- **Rendering Test:** Checked table rows for image loading integrity.
- **Fallbacks:** Events without posters render a stylish gradient badge `<div class="poster-placeholder">` with event category icons, preventing broken image placeholders.
- **Image Security:** Images are served directly from the `uploads/` directory with unique time-stamped hashes (e.g. `1791537429_6ac8b1155ae37.jpeg`).

### 4. Event Modification (edit.php)
- **URL Tested:** `http://localhost/event-management/edit.php?id=X`
- **Pre-Population:** Existing event details and poster preview are pre-populated into the form.
- **Update Handling:** Updating fields updates the database row cleanly; replacing a poster triggers `safe_delete_poster()` to remove stale files if unreferenced by other records.

### 5. Event Deletion & Modal Confirmation
- **Confirmation Mechanism:** Replaced invasive browser `window.confirm` with an accessible in-app modal overlay (`#deleteModal`).
- **Confirmation Safety:** The modal displays the specific title of the event being deleted to prevent accidental clicks.
- **Cleanup:** Confirmed that deleting an event removes its row from `event_db.events` and deletes the associated uploaded poster from disk.

### 6. File Upload Validation (MIME & Size Limits)
- **File Type Enforcement:** `upload.php` performs dual validation:
  1. Extension check against allowed list: `['jpg', 'jpeg', 'png', 'webp']`.
  2. MIME type inspection using `mime_content_type()` to prevent renamed executable files.
- **File Size Enforcement:** Server strictly rejects files over `2 * 1024 * 1024` bytes (2 MB), outputting: `"File size exceeds the 2 MB limit. Please upload a smaller image."`.

---

## Bugs Identified & Remediations Applied

| Issue Description | Root Cause | Remediation Applied | Status |
| :--- | :--- | :--- | :---: |
| **1. Session Invalidation on Protected Routes** | Basic script lacked role checking on `edit.php`. | Added `require_admin()` server-side guard to both `index.php` and `edit.php` in `auth.php`. | **RESOLVED** |
| **2. Accidental Event Deletion Risk** | Deletion was triggered directly via simple link without confirmation dialog. | Implemented custom dark-themed `#deleteModal` with event title display and explicit user confirmation button. | **RESOLVED** |
| **3. Date Input Formatting Discrepancy** | Browser locale formatting when typing dates caused MySQL `0000-00-00` values. | Configured ISO 8601 `YYYY-MM-DD` programmatic assignment, ensuring valid SQL date storage and form validation compatibility. | **RESOLVED** |
| **4. Broken Image Icons for Posterless Events** | Lack of fallback rendered browser broken image icons for events without images. | Implemented CSS gradient fallback badges with FontAwesome icons for events without posters. | **RESOLVED** |

---

## Screenshot Inventory

All screenshots have been generated from the live application and saved into `test-report-screenshots/`:

1. `01-admin-login.png` – Administrator login portal with masked password input.
2. `02-admin-dashboard.png` – Admin dashboard with statistics, creation form, and event table.
3. `03-event-creation-success.png` – Successful event creation with poster and success alert.
4. `04-event-list-with-posters.png` – Event directory showcasing uploaded posters and fallback badges.
5. `05-event-update-success.png` – Edit event workflow and successful update notification banner.
6. `06-event-deletion-confirmation.png` – Deletion confirmation modal overlay with target event title.
7. `07-event-deletion-success.png` – Event deletion success notification and table update.
8. `08-valid-poster-upload.png` – Valid uploaded poster thumbnail visible in directory row.
9. `09-invalid-file-type-error.png` – Error alert rejecting unsupported `.txt` file upload.
10. `10-file-size-limit-error.png` – Error alert rejecting oversized file (> 2 MB).
11. `11-success-messages.png` – Prominent emerald notification banner for successful operations.
12. `12-error-messages.png` – User-friendly red error alert on invalid login credentials.
13. `13-unauthorized-dashboard-access.png` – Unauthorized direct dashboard access blocked with redirect.
14. `14-unauthorized-operation-blocked.png` – Unauthorized edit route operation blocked with redirect.

---
**Report Approved by:** Automated Quality Assurance Suite  
**Result:** **14 / 14 Tests Passed (100% Success Rate)**
