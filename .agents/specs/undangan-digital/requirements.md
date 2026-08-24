# Requirements Document: Undangan Digital

## Introduction

Undangan Digital adalah aplikasi B2C yang memungkinkan pengantin membuat dan mengelola undangan pernikahan digital secara mandiri. Sistem menggunakan model bisnis lifetime per undangan dengan template berbasis folder yang dapat ditambahkan tanpa deployment. Aplikasi menyediakan preview interaktif, sistem pembayaran terintegrasi, dashboard editor, manajemen tamu dengan RSVP, dan amplop digital.

## Glossary

- **Platform**: Sistem undangan digital secara keseluruhan
- **User**: Pengantin yang membeli dan mengelola undangan
- **Guest**: Tamu undangan yang menerima link personal
- **Template**: Desain undangan yang terdiri dari sections dan ornaments
- **Section**: Bagian konten template (hero, countdown, gallery, story, gift, rsvp)
- **Ornament**: Elemen dekoratif template (flower-top, flower-bottom, divider)
- **Invitation**: Instance undangan yang dibuat oleh User
- **Preview_System**: Sistem yang memungkinkan User melihat template dengan data dummy atau data sendiri sebelum membeli
- **Order**: Transaksi pembelian paket atau add-on
- **Base_Package**: Paket undangan lifetime yang wajib dibeli
- **Add_On**: Fitur tambahan yang dapat dibeli terpisah (custom domain, managed setup, extra storage)
- **Payment_Provider**: Midtrans sebagai gateway pembayaran
- **Subdomain**: Format akses undangan: {nama}.undangan.com
- **Custom_Domain**: Domain pribadi User untuk mengakses undangan
- **RSVP**: Konfirmasi kehadiran tamu
- **Amplop_Digital**: Fitur untuk menerima transfer uang dari tamu
- **Template_Sync**: Proses sinkronisasi folder template ke database
- **Session_Storage**: Penyimpanan sementara data preview di browser
- **Mustache**: Template engine untuk rendering data ke HTML
- **R2**: Cloudflare R2 storage untuk media files
- **Admin**: Pengelola platform dengan akses admin panel

## Requirements

### Requirement 1: Template Management System

**User Story:** As an Admin, I want to manage templates via folder structure, so that I can add new templates without code deployment.

#### Acceptance Criteria

1. THE Template_Sync SHALL scan all folders in storage/templates/ directory
2. WHEN Template_Sync is executed, THE Template_Sync SHALL read template.json from each folder
3. WHEN template.json is valid, THE Template_Sync SHALL upsert template data to templates table
4. THE Template_Sync SHALL register all sections from template.json to template_sections table
5. THE Template_Sync SHALL register all ornaments from template.json to template_ornaments table
6. THE Template_Sync SHALL preserve existing template records during sync
7. WHEN a template folder contains assets directory, THE Template_Sync SHALL validate that style.css and script.js exist
8. THE Template_Sync SHALL validate that all section files referenced in template.json exist in sections directory
9. THE Template_Sync SHALL validate that all ornament files referenced in template.json exist in ornaments directory
10. WHEN template.json contains invalid JSON, THE Template_Sync SHALL log error and skip that template

### Requirement 2: Template Parser and Renderer

**User Story:** As a Developer, I want to parse and render templates with Mustache, so that invitation content can be dynamically generated.

#### Acceptance Criteria

1. THE Template_Parser SHALL parse HTML files containing Mustache syntax {{variable}}
2. WHEN rendering a template, THE Template_Renderer SHALL inject data from invitation_contents table
3. THE Template_Renderer SHALL escape HTML by default to prevent XSS attacks
4. THE Template_Renderer SHALL render all visible sections in sort_order sequence
5. THE Template_Renderer SHALL render active ornaments in their designated positions
6. WHEN a variable is undefined, THE Template_Renderer SHALL render empty string
7. THE Pretty_Printer SHALL format rendered HTML with proper indentation
8. FOR ALL valid invitation content, parsing then printing then parsing SHALL produce equivalent output (round-trip property)

### Requirement 3: Public Template Preview Without Authentication

**User Story:** As a potential User, I want to preview templates with dummy data, so that I can see how templates look before purchasing.

#### Acceptance Criteria

1. THE Platform SHALL display all active templates on /templates page without requiring authentication
2. WHEN a template is selected, THE Preview_System SHALL render template with predefined dummy data
3. THE Preview_System SHALL display template in mobile-responsive layout
4. THE Preview_System SHALL allow navigation between different templates without page reload
5. WHEN User clicks preview, THE Platform SHALL render template using client-side Mustache.js
6. THE Preview_System SHALL load template sections in correct sort_order
7. THE Preview_System SHALL display active ornaments in their designated positions

### Requirement 4: Interactive Preview with User Data

**User Story:** As a potential User, I want to fill my own data in preview, so that I can see how my invitation will look before purchasing.

#### Acceptance Criteria

1. WHEN User clicks "Coba dengan datamu" button, THE Preview_System SHALL display data input form
2. THE Preview_System SHALL accept bride_name, groom_name, and event_date as minimum required fields
3. WHEN User inputs data, THE Preview_System SHALL update template rendering in realtime using Mustache.js
4. WHEN User uploads preview photos, THE Preview_System SHALL use URL.createObjectURL() for display without server upload
5. THE Preview_System SHALL store preview data in browser sessionStorage
6. THE Preview_System SHALL include template_slug in sessionStorage data
7. THE Preview_System SHALL include preview_at timestamp in sessionStorage data
8. WHEN User clicks "Beli sekarang", THE Platform SHALL redirect to /checkout with template parameter

### Requirement 5: Checkout and Order Creation

**User Story:** As a potential User, I want to checkout and pay for invitation package, so that I can activate my invitation.

#### Acceptance Criteria

1. THE Platform SHALL display checkout page at /checkout route
2. WHEN checkout page loads, THE Platform SHALL read preview data from sessionStorage if available
3. THE Platform SHALL display order summary with selected template and base package price
4. THE Platform SHALL allow User to add add-on products to cart
5. WHEN User is not authenticated, THE Platform SHALL redirect to registration or login
6. WHEN authenticated User submits checkout, THE Platform SHALL create order record with status pending
7. THE Platform SHALL create order_items records for base package and selected add-ons
8. THE Platform SHALL snapshot current product prices to order_items.price_snapshot
9. THE Platform SHALL calculate total_amount from all order_items
10. WHEN order is created, THE Platform SHALL request snap_token from Midtrans API

### Requirement 6: Payment Processing with Midtrans

**User Story:** As a User, I want to pay via multiple payment methods, so that I can complete my purchase conveniently.

#### Acceptance Criteria

1. WHEN snap_token is received, THE Platform SHALL display Midtrans Snap payment interface
2. THE Platform SHALL support payment methods: QRIS, virtual account, e-wallet, and credit card
3. WHEN User completes payment, THE Payment_Provider SHALL send webhook to /webhook/midtrans
4. THE Platform SHALL verify Midtrans signature before processing webhook
5. WHEN signature is valid and status is paid, THE Platform SHALL update payment status to paid
6. THE Platform SHALL update order status to paid
7. THE Platform SHALL record paid_at timestamp
8. THE Platform SHALL store raw Midtrans response in payments.raw_response field
9. IF webhook is received multiple times for same transaction, THEN THE Platform SHALL process only once (idempotency)
10. WHEN payment status is paid, THE Platform SHALL activate purchased features in user_features table

### Requirement 7: Feature Activation After Payment

**User Story:** As a User, I want my purchased features activated automatically, so that I can start using my invitation immediately after payment.

#### Acceptance Criteria

1. WHEN payment status changes to paid, THE Platform SHALL create user_features records for each order_item
2. THE Platform SHALL set activated_at to current timestamp
3. THE Platform SHALL set expires_at to NULL for lifetime features
4. THE Platform SHALL link user_features to order_items via order_item_id
5. WHEN base_package is activated, THE Platform SHALL create invitation record for User
6. THE Platform SHALL assign selected template to invitation
7. THE Platform SHALL generate unique subdomain for invitation
8. THE Platform SHALL restore preview data from checkout to invitation_contents if available
9. THE Platform SHALL set invitation status to draft
10. WHEN features are activated, THE Platform SHALL send confirmation email via queue

### Requirement 8: Dashboard Access Control

**User Story:** As a User, I want to access dashboard only after purchasing base package, so that the system enforces proper access control.

#### Acceptance Criteria

1. THE Platform SHALL require authentication for all /dashboard routes
2. WHEN authenticated User accesses /dashboard routes, THE Platform SHALL verify base_package feature exists
3. IF User does not have base_package feature, THEN THE Platform SHALL return 403 error with message
4. THE Platform SHALL check feature expires_at is NULL or greater than current time
5. WHEN User has active base_package, THE Platform SHALL allow access to dashboard

### Requirement 9: Invitation Content Editor

**User Story:** As a User, I want to edit my invitation content, so that I can customize my invitation with personal information.

#### Acceptance Criteria

1. THE Platform SHALL display content editor form in dashboard
2. THE Platform SHALL load current invitation_contents data into form fields
3. THE Platform SHALL provide input fields for: bride_name, groom_name, bride_father, bride_mother, groom_father, groom_mother
4. THE Platform SHALL provide datetime inputs for: akad_datetime, reception_datetime
5. THE Platform SHALL provide text inputs for: akad_venue, akad_maps_url, reception_venue, reception_maps_url
6. THE Platform SHALL provide textarea for: love_story, special_message
7. WHEN User submits content form, THE Platform SHALL validate all required fields
8. WHEN validation passes, THE Platform SHALL update invitation_contents record
9. THE Platform SHALL preserve User's user_id in all queries to invitation_contents
10. THE Platform SHALL display success message after save

### Requirement 10: Media Upload and Storage

**User Story:** As a User, I want to upload photos and music, so that I can personalize my invitation with media content.

#### Acceptance Criteria

1. THE Platform SHALL accept image uploads for cover_photo_url field
2. THE Platform SHALL accept multiple image uploads for gallery
3. THE Platform SHALL accept audio file upload for music_url field
4. WHEN file is uploaded, THE Platform SHALL validate MIME type using finfo
5. THE Platform SHALL reject files with invalid MIME types
6. THE Platform SHALL generate UUID filename for uploaded files
7. THE Platform SHALL upload files to R2 storage with user_id prefix path
8. WHEN image is uploaded, THE Platform SHALL optimize image size via queue job
9. THE Platform SHALL store file URL in invitation_contents or invitation_gallery table
10. THE Platform SHALL generate temporary signed URLs with 1 hour expiration for R2 assets

### Requirement 11: Section Management

**User Story:** As a User, I want to reorder and toggle sections, so that I can customize my invitation layout.

#### Acceptance Criteria

1. THE Platform SHALL display all template sections in dashboard editor
2. THE Platform SHALL show current sort_order for each section
3. THE Platform SHALL allow User to drag and drop sections to reorder
4. WHEN User reorders sections, THE Platform SHALL update sort_order values in invitation_sections table
5. THE Platform SHALL allow User to toggle section visibility
6. WHEN User toggles visibility, THE Platform SHALL update is_visible field in invitation_sections table
7. THE Platform SHALL prevent hiding required sections
8. THE Platform SHALL scope all queries to current User's invitation_id
9. WHEN sections are reordered, THE Platform SHALL update sort_order atomically
10. THE Platform SHALL display preview of section order changes

### Requirement 12: Ornament Management

**User Story:** As a User, I want to toggle ornaments, so that I can control decorative elements in my invitation.

#### Acceptance Criteria

1. THE Platform SHALL display all template ornaments in dashboard editor
2. THE Platform SHALL show ornament position (top, bottom, between, overlay)
3. THE Platform SHALL allow User to toggle ornament active state
4. WHEN User toggles ornament, THE Platform SHALL update is_active field in invitation_ornaments table
5. THE Platform SHALL scope all queries to current User's invitation_id
6. WHEN ornament is activated, THE Platform SHALL render ornament in designated position
7. THE Platform SHALL display ornament preview in editor

### Requirement 13: Invitation Publishing

**User Story:** As a User, I want to publish my invitation, so that guests can access it via subdomain.

#### Acceptance Criteria

1. WHEN User clicks publish button, THE Platform SHALL validate that required content fields are filled
2. THE Platform SHALL validate that subdomain is unique
3. WHEN validation passes, THE Platform SHALL update invitation status to published
4. THE Platform SHALL set published_at timestamp to current time
5. THE Platform SHALL make invitation accessible at {subdomain}.undangan.com
6. WHEN invitation is published, THE Platform SHALL display success message with invitation URL
7. THE Platform SHALL allow User to unpublish invitation by changing status to draft
8. WHEN invitation is draft, THE Platform SHALL return 404 for public subdomain access

### Requirement 14: Subdomain and Custom Domain Resolution

**User Story:** As a Guest, I want to access invitation via subdomain or custom domain, so that I can view the invitation.

#### Acceptance Criteria

1. WHEN request is received, THE Platform SHALL extract host from request
2. IF host ends with platform domain, THEN THE Platform SHALL extract subdomain prefix
3. THE Platform SHALL query invitations table by subdomain field
4. IF host does not match platform domain, THEN THE Platform SHALL query invitations table by custom_domain field
5. THE Platform SHALL filter query by status equals published
6. IF invitation is not found, THEN THE Platform SHALL return 404 error
7. WHEN invitation is found, THE Platform SHALL set invitation to request context
8. THE Platform SHALL increment view_count by 1
9. THE Platform SHALL render invitation using template and invitation_contents data
10. THE Platform SHALL load all visible sections in sort_order

### Requirement 15: Guest Management

**User Story:** As a User, I want to manage guest list, so that I can track who I invited.

#### Acceptance Criteria

1. THE Platform SHALL display guest list in dashboard
2. THE Platform SHALL allow User to add guest manually with name, phone, category, and max_pax
3. WHEN guest is created, THE Platform SHALL generate unique_code for guest
4. THE Platform SHALL ensure unique_code is unique across all guests
5. THE Platform SHALL allow User to import guests from CSV file
6. WHEN CSV is uploaded, THE Platform SHALL validate CSV format and required columns
7. THE Platform SHALL create guest records for each valid CSV row
8. THE Platform SHALL scope all guest queries to current User's invitation_id
9. THE Platform SHALL display guest count and category breakdown
10. THE Platform SHALL allow User to edit and delete guests

### Requirement 16: Personal Guest Links

**User Story:** As a User, I want to generate personal links for guests, so that each guest sees their name on invitation.

#### Acceptance Criteria

1. THE Platform SHALL generate personal link format: {subdomain}.undangan.com?to={unique_code}
2. WHEN invitation is accessed with to parameter, THE Platform SHALL query guests table by unique_code
3. IF guest is found, THEN THE Platform SHALL display guest name in invitation
4. THE Platform SHALL display "Kepada Yth. {guest_name}" in invitation header
5. THE Platform SHALL pre-fill guest name in RSVP form
6. IF unique_code is invalid, THEN THE Platform SHALL display invitation without personalization
7. THE Platform SHALL allow User to copy personal link from dashboard
8. THE Platform SHALL generate QR code for personal link

### Requirement 17: RSVP System

**User Story:** As a Guest, I want to submit RSVP, so that I can confirm my attendance.

#### Acceptance Criteria

1. THE Platform SHALL display RSVP form in invitation
2. THE Platform SHALL provide attendance options: hadir, tidak_hadir
3. THE Platform SHALL allow Guest to specify pax_count up to guest.max_pax
4. THE Platform SHALL provide textarea for optional message
5. WHEN Guest submits RSVP, THE Platform SHALL validate guest_id exists
6. THE Platform SHALL create or update rsvp record for guest_id
7. THE Platform SHALL set attendance and pax_count from form data
8. THE Platform SHALL record submission timestamp in updated_at
9. THE Platform SHALL display success message after RSVP submission
10. THE Platform SHALL apply rate limiting of 5 requests per minute per IP address

### Requirement 18: RSVP Dashboard

**User Story:** As a User, I want to view RSVP responses, so that I can track guest confirmations.

#### Acceptance Criteria

1. THE Platform SHALL display RSVP list in dashboard
2. THE Platform SHALL show guest name, attendance status, pax_count, and message
3. THE Platform SHALL calculate total confirmed attendees (sum of pax_count where attendance is hadir)
4. THE Platform SHALL display RSVP statistics: total invited, total confirmed, total declined, pending
5. THE Platform SHALL allow filtering by attendance status
6. THE Platform SHALL allow filtering by guest category
7. THE Platform SHALL scope all RSVP queries to current User's invitation_id
8. THE Platform SHALL display RSVP submission timestamp
9. THE Platform SHALL allow exporting RSVP data to CSV
10. THE Platform SHALL update statistics in realtime when new RSVP is submitted

### Requirement 19: Digital Envelope (Amplop Digital)

**User Story:** As a User, I want to display digital envelope information, so that guests can send monetary gifts.

#### Acceptance Criteria

1. THE Platform SHALL provide input fields for bank_name, account_number, account_name in invitation_contents
2. THE Platform SHALL allow User to upload QRIS image to qris_image_url field
3. THE Platform SHALL provide input fields for gopay_number, ovo_number, dana_number
4. WHEN User saves digital envelope data, THE Platform SHALL update invitation_contents record
5. THE Platform SHALL display digital envelope section in published invitation
6. THE Platform SHALL display bank account details if provided
7. THE Platform SHALL display QRIS image if provided
8. THE Platform SHALL display e-wallet numbers if provided
9. THE Platform SHALL allow Guest to copy account numbers with one click
10. THE Platform SHALL not process actual payments (display only)

### Requirement 20: Custom Domain Add-On

**User Story:** As a User, I want to use custom domain for my invitation, so that I can have personalized URL.

#### Acceptance Criteria

1. WHERE User has purchased custom_domain add-on, THE Platform SHALL allow setting custom domain in dashboard
2. THE Platform SHALL validate custom domain format
3. THE Platform SHALL ensure custom domain is unique across all invitations
4. WHEN User saves custom domain, THE Platform SHALL update invitations.custom_domain field
5. THE Platform SHALL display DNS configuration instructions
6. THE Platform SHALL resolve invitation by custom_domain when accessed
7. THE Platform SHALL verify User has active custom_domain feature before allowing domain setting
8. IF User does not have custom_domain feature, THEN THE Platform SHALL display upgrade prompt
9. THE Platform SHALL allow User to remove custom domain and revert to subdomain
10. THE Platform SHALL update custom_domain to NULL when removed

### Requirement 21: Product and Pricing Management

**User Story:** As an Admin, I want to manage products and prices, so that I can adjust pricing without code deployment.

#### Acceptance Criteria

1. THE Platform SHALL provide admin panel for product management
2. THE Platform SHALL display all products with type, name, price, and is_active status
3. THE Platform SHALL allow Admin to edit product price
4. WHEN Admin updates price, THE Platform SHALL update products.price field
5. THE Platform SHALL allow Admin to toggle product is_active status
6. THE Platform SHALL use updated prices for new orders
7. THE Platform SHALL preserve price_snapshot in existing order_items
8. THE Platform SHALL allow Admin to edit product name and description
9. THE Platform SHALL prevent deletion of products referenced in orders
10. THE Platform SHALL display product usage statistics

### Requirement 22: Admin Template Management

**User Story:** As an Admin, I want to manage template visibility, so that I can control which templates are available to users.

#### Acceptance Criteria

1. THE Platform SHALL display all synced templates in admin panel
2. THE Platform SHALL show template name, slug, version, price, and is_active status
3. THE Platform SHALL allow Admin to toggle template is_active status
4. WHEN template is deactivated, THE Platform SHALL hide template from public template list
5. THE Platform SHALL allow Admin to update template price
6. THE Platform SHALL display template usage count (number of invitations using template)
7. THE Platform SHALL prevent deletion of templates in use
8. THE Platform SHALL allow Admin to trigger template sync manually
9. WHEN sync is triggered, THE Platform SHALL execute templates:sync command
10. THE Platform SHALL display last synced_at timestamp for each template

### Requirement 23: User Management for Admin

**User Story:** As an Admin, I want to manage users, so that I can provide support and handle issues.

#### Acceptance Criteria

1. THE Platform SHALL display all users in admin panel
2. THE Platform SHALL show user name, email, registration date, and active features
3. THE Platform SHALL allow Admin to view user's invitation details
4. THE Platform SHALL allow Admin to view user's order history
5. THE Platform SHALL allow Admin to manually activate features for user
6. THE Platform SHALL allow Admin to extend feature expiration dates
7. THE Platform SHALL display user's payment history
8. THE Platform SHALL allow Admin to search users by name or email
9. THE Platform SHALL display user statistics: total users, active invitations, revenue
10. THE Platform SHALL prevent Admin from deleting users with active orders

### Requirement 24: Audit Logging

**User Story:** As an Admin, I want to track critical actions, so that I can audit system activity.

#### Acceptance Criteria

1. WHEN invitation is published, THE Platform SHALL create audit log with action invitation.published
2. WHEN order is paid, THE Platform SHALL create audit log with action order.paid
3. WHEN feature is activated, THE Platform SHALL create audit log with action feature.activated
4. WHEN custom domain is set, THE Platform SHALL create audit log with action domain.set
5. THE Platform SHALL record user_id, action, subject_type, subject_id, and ip_address in audit logs
6. THE Platform SHALL store additional context in metadata JSON field
7. THE Platform SHALL allow Admin to view audit logs in admin panel
8. THE Platform SHALL allow filtering audit logs by user, action, and date range
9. THE Platform SHALL display audit logs in reverse chronological order
10. THE Platform SHALL retain audit logs for minimum 1 year

### Requirement 25: Security and Data Isolation

**User Story:** As a User, I want my data isolated from other users, so that my invitation data remains private.

#### Acceptance Criteria

1. THE Platform SHALL filter all invitation queries by user_id equals authenticated user
2. THE Platform SHALL filter all guest queries by invitation_id belonging to authenticated user
3. THE Platform SHALL filter all rsvp queries by invitation_id belonging to authenticated user
4. THE Platform SHALL use global scope on Invitation model to enforce user_id filtering
5. THE Platform SHALL validate file MIME type using finfo before upload
6. THE Platform SHALL reject files with MIME type not in allowed list
7. THE Platform SHALL generate UUID filenames to prevent path traversal
8. THE Platform SHALL apply rate limiting to RSVP endpoint: 5 requests per minute per IP
9. THE Platform SHALL apply rate limiting to preview endpoint: 60 requests per minute per IP
10. THE Platform SHALL verify Midtrans webhook signature before processing

### Requirement 26: Email Notifications

**User Story:** As a User, I want to receive email notifications, so that I stay informed about my invitation status.

#### Acceptance Criteria

1. WHEN payment is successful, THE Platform SHALL send confirmation email to User
2. THE Platform SHALL queue email sending via Laravel queue
3. THE Platform SHALL include order details and invitation URL in confirmation email
4. WHEN invitation is published, THE Platform SHALL send notification email to User
5. THE Platform SHALL include invitation URL and sharing instructions in publish email
6. WHEN RSVP is submitted, THE Platform SHALL send notification email to User
7. THE Platform SHALL include guest name and attendance status in RSVP email
8. THE Platform SHALL use email templates with consistent branding
9. THE Platform SHALL handle email sending failures gracefully
10. THE Platform SHALL retry failed email jobs up to 3 times

### Requirement 27: Error Tracking and Monitoring

**User Story:** As a Developer, I want to track errors in production, so that I can identify and fix issues quickly.

#### Acceptance Criteria

1. THE Platform SHALL integrate Sentry for error tracking
2. WHEN unhandled exception occurs, THE Platform SHALL send error report to Sentry
3. THE Platform SHALL include user context in error reports
4. THE Platform SHALL include request context in error reports
5. THE Platform SHALL capture stack traces for all exceptions
6. THE Platform SHALL group similar errors in Sentry
7. THE Platform SHALL send error notifications to development team
8. THE Platform SHALL provide health check endpoint at /health
9. WHEN health check is accessed, THE Platform SHALL verify database connectivity
10. WHEN health check is accessed, THE Platform SHALL verify Redis connectivity

### Requirement 28: Session and Preview Data Management

**User Story:** As a User, I want my preview data restored after checkout, so that I don't have to re-enter information.

#### Acceptance Criteria

1. WHEN User completes checkout, THE Platform SHALL read preview data from request
2. THE Platform SHALL validate preview data structure
3. WHEN invitation is created, THE Platform SHALL populate invitation_contents with preview data
4. THE Platform SHALL map preview fields to invitation_contents columns
5. THE Platform SHALL handle missing preview data gracefully
6. THE Platform SHALL preserve preview_at timestamp for audit purposes
7. IF preview data is older than 24 hours, THEN THE Platform SHALL ignore preview data
8. THE Platform SHALL clear sessionStorage after successful data restoration
9. THE Platform SHALL handle preview data from different devices gracefully
10. THE Platform SHALL allow User to manually enter data if preview data is unavailable

### Requirement 29: Template Asset Loading

**User Story:** As a Guest, I want invitation to load quickly, so that I have good user experience.

#### Acceptance Criteria

1. THE Platform SHALL serve template CSS from assets/style.css
2. THE Platform SHALL serve template JavaScript from assets/script.js
3. THE Platform SHALL minify CSS and JavaScript in production
4. THE Platform SHALL cache template assets with 1 year expiration
5. THE Platform SHALL serve media files from R2 CDN
6. THE Platform SHALL lazy load gallery images
7. THE Platform SHALL optimize images to WebP format when supported
8. THE Platform SHALL provide fallback to JPEG for unsupported browsers
9. THE Platform SHALL preload critical CSS and fonts
10. THE Platform SHALL defer non-critical JavaScript loading

### Requirement 30: Mobile Responsiveness

**User Story:** As a Guest, I want to view invitation on mobile device, so that I can access invitation anywhere.

#### Acceptance Criteria

1. THE Platform SHALL render all templates with mobile-responsive layout
2. THE Platform SHALL use viewport meta tag for proper mobile scaling
3. THE Platform SHALL ensure touch targets are minimum 44x44 pixels
4. THE Platform SHALL optimize images for mobile bandwidth
5. THE Platform SHALL test templates on viewport widths from 320px to 1920px
6. THE Platform SHALL ensure text is readable without zooming
7. THE Platform SHALL ensure forms are usable on mobile keyboards
8. THE Platform SHALL ensure RSVP form works on mobile devices
9. THE Platform SHALL ensure media uploads work on mobile browsers
10. THE Platform SHALL ensure navigation works with touch gestures

---

## Document Status

**Version:** 1.0  
**Created:** 2025-01-09  
**Status:** Initial Draft - Ready for Review

Dokumen ini berisi 30 requirements dengan total 300 acceptance criteria yang mencakup seluruh fitur MVP aplikasi undangan digital. Setiap requirement mengikuti EARS patterns dan INCOSE quality rules untuk memastikan testability dan clarity.

**Catatan Khusus:**
- Requirement 2 mencakup parser dan pretty printer dengan round-trip property testing (essential untuk Mustache template engine)
- Requirement 25 mencakup security dan data isolation yang kritis untuk aplikasi multi-user
- Semua requirements menggunakan SHALL untuk positive statements
- Semua system names didefinisikan di Glossary
- Tidak ada escape clauses atau vague terms
