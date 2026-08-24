# Implementation Tasks: Undangan Digital

## Overview

Implementation plan untuk aplikasi Undangan Digital berdasarkan requirements.md dan design.md. Total estimasi: 4 minggu (MVP Phase 1).

---

## Week 1: Foundation & Template System

### Task 1: Database Schema Setup
- [ ] 1.1 Create migration for users table (with role column)
- [ ] 1.2 Create migration for products table
- [ ] 1.3 Create migration for templates table
- [ ] 1.4 Create migration for template_sections table
- [ ] 1.5 Create migration for template_ornaments table
- [ ] 1.6 Create migration for invitations table
- [ ] 1.7 Create migration for invitation_contents table
- [ ] 1.8 Create migration for invitation_sections table
- [ ] 1.9 Create migration for invitation_ornaments table
- [ ] 1.10 Create migration for invitation_gallery table
- [ ] 1.11 Create migration for guests table
- [ ] 1.12 Create migration for rsvps table
- [ ] 1.13 Create migration for orders table
- [ ] 1.14 Create migration for order_items table
- [ ] 1.15 Create migration for payments table
- [ ] 1.16 Create migration for user_features table
- [ ] 1.17 Create migration for audit_logs table
- [ ] 1.18 Run migrations and verify schema

### Task 2: Eloquent Models
- [ ] 2.1 Create User model with relationships and hasFeature() helper
- [ ] 2.2 Create Product model with scopes
- [ ] 2.3 Create Template model with relationships
- [ ] 2.4 Create TemplateSection model
- [ ] 2.5 Create TemplateOrnament model
- [ ] 2.6 Create Invitation model with global scope for data isolation
- [ ] 2.7 Create InvitationContent model
- [ ] 2.8 Create InvitationSection model
- [ ] 2.9 Create InvitationOrnament model
- [ ] 2.10 Create InvitationGallery model
- [ ] 2.11 Create Guest model with getPersonalLink() helper
- [ ] 2.12 Create Rsvp model
- [ ] 2.13 Create Order model with relationships
- [ ] 2.14 Create OrderItem model
- [ ] 2.15 Create Payment model
- [ ] 2.16 Create UserFeature model
- [ ] 2.17 Create AuditLog model

### Task 3: Authentication Setup
- [ ] 3.1 Configure Laravel Fortify for registration and login
- [ ] 3.2 Create registration page (Vue + Inertia)
- [ ] 3.3 Create login page (Vue + Inertia)
- [ ] 3.4 Configure email verification
- [ ] 3.5 Create password reset flow
- [ ] 3.6 Add role-based middleware (admin/user)
- [ ] 3.7 Test authentication flow

### Task 4: Template Sync System
- [ ] 4.1 Create TemplateService with syncTemplates() method
- [ ] 4.2 Create parseTemplateJson() method
- [ ] 4.3 Create validateTemplateFiles() method
- [ ] 4.4 Create SyncTemplates Artisan command
- [ ] 4.5 Create storage/templates directory structure
- [ ] 4.6 Create romantic template folder with template.json
- [ ] 4.7 Create romantic template sections (hero, countdown, story, gallery, gift, rsvp)
- [ ] 4.8 Create romantic template ornaments (flower-top, flower-bottom, divider)
- [ ] 4.9 Create romantic template assets (style.css, script.js)
- [ ] 4.10 Create elegant template folder with template.json
- [ ] 4.11 Create elegant template sections
- [ ] 4.12 Create elegant template ornaments
- [ ] 4.13 Create elegant template assets
- [ ] 4.14 Test templates:sync command
- [ ] 4.15 Verify templates in database

### Task 5: Public Template Preview
- [ ] 5.1 Create TemplateController with index() and preview() methods
- [ ] 5.2 Create PreviewService with getDummyData() method
- [ ] 5.3 Create Templates/Index.vue page (template grid)
- [ ] 5.4 Create Templates/Preview.vue page
- [ ] 5.5 Create TemplateRenderer.vue component (Mustache.js integration)
- [ ] 5.6 Add Mustache.js to package.json and install
- [ ] 5.7 Implement client-side template rendering
- [ ] 5.8 Add mobile-responsive layout
- [ ] 5.9 Test preview with dummy data
- [ ] 5.10 Add navigation between templates

---

## Week 2: Preview System & Payment

### Task 6: Interactive Preview with User Data
- [ ] 6.1 Create PreviewDataForm.vue component
- [ ] 6.2 Implement real-time Mustache rendering on data input
- [ ] 6.3 Add photo upload with URL.createObjectURL()
- [ ] 6.4 Implement sessionStorage save on data change
- [ ] 6.5 Add "Beli sekarang" button with redirect to checkout
- [ ] 6.6 Implement preview data validation in PreviewService
- [ ] 6.7 Add preview data expiration check (24 hours)
- [ ] 6.8 Test preview flow end-to-end
- [ ] 6.9 Handle edge case: different device (no sessionStorage)
- [ ] 6.10 Add loading states and error handling

### Task 7: Checkout System
- [ ] 7.1 Create CheckoutController with index() and store() methods
- [ ] 7.2 Create OrderService with createOrder() method
- [ ] 7.3 Create Checkout/Index.vue page
- [ ] 7.4 Load preview data from sessionStorage
- [ ] 7.5 Display order summary with template and base package
- [ ] 7.6 Add add-on product selection (custom domain, managed setup, extra storage)
- [ ] 7.7 Calculate total amount dynamically
- [ ] 7.8 Implement authentication check (redirect to login if not authenticated)
- [ ] 7.9 Create order with pending status on submit
- [ ] 7.10 Snapshot product prices to order_items
- [ ] 7.11 Test checkout flow

### Task 8: Midtrans Payment Integration
- [ ] 8.1 Install Midtrans PHP SDK
- [ ] 8.2 Add Midtrans credentials to .env
- [ ] 8.3 Create PaymentService with requestSnapToken() method
- [ ] 8.4 Implement Midtrans Snap integration in checkout
- [ ] 8.5 Create MidtransWebhookController
- [ ] 8.6 Implement webhook signature verification
- [ ] 8.7 Implement payment status update logic
- [ ] 8.8 Add idempotency check (lockForUpdate)
- [ ] 8.9 Exclude webhook route from CSRF protection
- [ ] 8.10 Test webhook with Midtrans sandbox
- [ ] 8.11 Handle all payment statuses (pending, paid, failed, expired)

### Task 9: Feature Activation After Payment
- [ ] 9.1 Create ActivateFeaturesJob queue job
- [ ] 9.2 Implement feature activation in OrderService
- [ ] 9.3 Create user_features records for each order_item
- [ ] 9.4 Create invitation record when base_package is activated
- [ ] 9.5 Implement subdomain generation logic
- [ ] 9.6 Restore preview data to invitation_contents
- [ ] 9.7 Create SendEmailConfirmation job
- [ ] 9.8 Create payment success email template
- [ ] 9.9 Test feature activation flow end-to-end
- [ ] 9.10 Verify invitation created with correct data

### Task 10: Middleware & Access Control
- [ ] 10.1 Create HasBasePackage middleware
- [ ] 10.2 Create ResolveInvitation middleware
- [ ] 10.3 Apply HasBasePackage to dashboard routes
- [ ] 10.4 Test access control (403 without base_package)
- [ ] 10.5 Test subdomain resolution
- [ ] 10.6 Test custom domain resolution

---

## Week 3: Dashboard & Content Management

### Task 11: Dashboard Layout
- [ ] 11.1 Create Dashboard/Index.vue page
- [ ] 11.2 Create AppShell.vue layout component
- [ ] 11.3 Create AppSidebar.vue component
- [ ] 11.4 Create AppHeader.vue component
- [ ] 11.5 Create NavMain.vue component
- [ ] 11.6 Add dashboard navigation menu
- [ ] 11.7 Display invitation status and URL
- [ ] 11.8 Add quick stats (view count, RSVP count)
- [ ] 11.9 Test dashboard layout on mobile
- [ ] 11.10 Add loading states

### Task 12: Content Editor
- [ ] 12.1 Create EditorController with index() and saveContent() methods
- [ ] 12.2 Create ProfileUpdateRequest form request
- [ ] 12.3 Create Dashboard/Editor.vue page
- [ ] 12.4 Add form fields for bride/groom names and parents
- [ ] 12.5 Add datetime inputs for akad and reception
- [ ] 12.6 Add venue and maps URL inputs
- [ ] 12.7 Add textarea for love story and special message
- [ ] 12.8 Implement form validation
- [ ] 12.9 Add save button with loading state
- [ ] 12.10 Display success/error messages
- [ ] 12.11 Add live preview panel
- [ ] 12.12 Test content save and reload

### Task 13: Media Upload System
- [ ] 13.1 Install and configure Cloudflare R2 (or use local storage for dev)
- [ ] 13.2 Create MediaService with upload() method
- [ ] 13.3 Implement MIME type validation using finfo
- [ ] 13.4 Generate UUID filenames
- [ ] 13.5 Create MediaController with upload() method
- [ ] 13.6 Add cover photo upload to editor
- [ ] 13.7 Add gallery photo upload (multiple files)
- [ ] 13.8 Add music file upload
- [ ] 13.9 Create OptimizeUploadedImage job
- [ ] 13.10 Implement image optimization (resize, WebP conversion)
- [ ] 13.11 Generate temporary signed URLs (1 hour expiration)
- [ ] 13.12 Test upload flow with various file types
- [ ] 13.13 Test MIME validation (reject PHP files, etc.)
- [ ] 13.14 Add upload progress indicator
- [ ] 13.15 Handle upload errors gracefully

### Task 14: Section & Ornament Management
- [ ] 14.1 Create InvitationService with reorderSections() method
- [ ] 14.2 Create toggleSectionVisibility() method
- [ ] 14.3 Create toggleOrnament() method
- [ ] 14.4 Add section list to editor with drag-and-drop
- [ ] 14.5 Implement section reordering (update sort_order)
- [ ] 14.6 Add visibility toggle for sections
- [ ] 14.7 Prevent hiding required sections
- [ ] 14.8 Add ornament list with toggle switches
- [ ] 14.9 Update ornament is_active on toggle
- [ ] 14.10 Add preview update on section/ornament changes
- [ ] 14.11 Test reordering and toggling

### Task 15: Invitation Publishing
- [ ] 15.1 Create InvitationService with publish() method
- [ ] 15.2 Implement validateRequiredContent() method
- [ ] 15.3 Add publish button to dashboard
- [ ] 15.4 Validate required fields before publish
- [ ] 15.5 Update invitation status to published
- [ ] 15.6 Set published_at timestamp
- [ ] 15.7 Display success message with invitation URL
- [ ] 15.8 Add unpublish functionality
- [ ] 15.9 Create invitation published email template
- [ ] 15.10 Test publish flow
- [ ] 15.11 Verify public access at subdomain

### Task 16: Public Invitation View
- [ ] 16.1 Create PublicInvitationController with show() method
- [ ] 16.2 Apply ResolveInvitation middleware
- [ ] 16.3 Load invitation with content, sections, ornaments
- [ ] 16.4 Increment view_count on access
- [ ] 16.5 Create Public/Invitation.vue page
- [ ] 16.6 Render template with Mustache.js
- [ ] 16.7 Load sections in sort_order
- [ ] 16.8 Render active ornaments in positions
- [ ] 16.9 Add mobile-responsive layout
- [ ] 16.10 Test subdomain access
- [ ] 16.11 Test custom domain access (if configured)
- [ ] 16.12 Handle 404 for unpublished invitations

---

## Week 4: Guest Management & RSVP

### Task 17: Guest Management
- [ ] 17.1 Create GuestController with index(), store(), import() methods
- [ ] 17.2 Create Dashboard/Guests/Index.vue page
- [ ] 17.3 Add guest list table with filters
- [ ] 17.4 Add manual guest creation form
- [ ] 17.5 Generate unique_code on guest creation
- [ ] 17.6 Add CSV import functionality
- [ ] 17.7 Validate CSV format and columns
- [ ] 17.8 Create guest records from CSV rows
- [ ] 17.9 Display guest count and category breakdown
- [ ] 17.10 Add edit and delete guest functionality
- [ ] 17.11 Generate personal link with unique_code
- [ ] 17.12 Add QR code generation for personal links
- [ ] 17.13 Add copy link button
- [ ] 17.14 Test guest management flow

### Task 18: Personal Guest Links
- [ ] 18.1 Update PublicInvitationController to handle ?to= parameter
- [ ] 18.2 Query guest by unique_code
- [ ] 18.3 Display "Kepada Yth. {guest_name}" if guest found
- [ ] 18.4 Pre-fill guest name in RSVP form
- [ ] 18.5 Handle invalid unique_code gracefully
- [ ] 18.6 Test personal link access
- [ ] 18.7 Verify guest name display

### Task 19: RSVP System
- [ ] 19.1 Create PublicRsvpController with store() method
- [ ] 19.2 Add RSVP form to invitation view
- [ ] 19.3 Add attendance options (hadir, tidak_hadir)
- [ ] 19.4 Add pax_count input (up to guest.max_pax)
- [ ] 19.5 Add optional message textarea
- [ ] 19.6 Implement RSVP submission
- [ ] 19.7 Create or update rsvp record
- [ ] 19.8 Apply rate limiting (5 requests per minute per IP)
- [ ] 19.9 Display success message after submission
- [ ] 19.10 Create RSVP received email template
- [ ] 19.11 Send email notification to user
- [ ] 19.12 Test RSVP submission flow
- [ ] 19.13 Test rate limiting

### Task 20: RSVP Dashboard
- [ ] 20.1 Create RsvpController with index() method
- [ ] 20.2 Create Dashboard/RSVP/Index.vue page
- [ ] 20.3 Display RSVP list table
- [ ] 20.4 Show guest name, attendance, pax_count, message
- [ ] 20.5 Calculate total confirmed attendees
- [ ] 20.6 Display RSVP statistics (invited, confirmed, declined, pending)
- [ ] 20.7 Add filter by attendance status
- [ ] 20.8 Add filter by guest category
- [ ] 20.9 Add export to CSV functionality
- [ ] 20.10 Add real-time updates (optional: polling or websockets)
- [ ] 20.11 Test RSVP dashboard

### Task 21: Digital Envelope (Amplop Digital)
- [ ] 21.1 Add digital envelope fields to content editor
- [ ] 21.2 Add bank account inputs (bank_name, account_number, account_name)
- [ ] 21.3 Add QRIS image upload
- [ ] 21.4 Add e-wallet number inputs (GoPay, OVO, DANA)
- [ ] 21.5 Create digital envelope section in invitation template
- [ ] 21.6 Display bank account details if provided
- [ ] 21.7 Display QRIS image if provided
- [ ] 21.8 Display e-wallet numbers if provided
- [ ] 21.9 Add copy account number button
- [ ] 21.10 Test digital envelope display

### Task 22: Admin Panel (FilamentPHP)
- [ ] 22.1 Install FilamentPHP
- [ ] 22.2 Create admin user seeder
- [ ] 22.3 Create ProductResource for product management
- [ ] 22.4 Add product CRUD (create, read, update, delete)
- [ ] 22.5 Add product price editing
- [ ] 22.6 Add product is_active toggle
- [ ] 22.7 Create TemplateResource for template management
- [ ] 22.8 Display synced templates
- [ ] 22.9 Add template is_active toggle
- [ ] 22.10 Add template price editing
- [ ] 22.11 Add manual template sync trigger
- [ ] 22.12 Create UserResource for user management
- [ ] 22.13 Display user list with active features
- [ ] 22.14 Add user search by name/email
- [ ] 22.15 Display user statistics (total users, revenue)
- [ ] 22.16 Create AuditLogResource
- [ ] 22.17 Display audit logs with filters
- [ ] 22.18 Test admin panel access (role:admin only)

---

## Testing Tasks

### Task 23: Property-Based Tests
- [ ] 23.1 Set up property test generators
- [ ] 23.2 Create TemplateRenderingTest (round-trip property)
- [ ] 23.3 Create XssEscapingTest (HTML escaping property)
- [ ] 23.4 Create PreviewValidationTest (data validation property)
- [ ] 23.5 Create PreviewMappingTest (field mapping property)
- [ ] 23.6 Create PreviewExpirationTest (timestamp logic property)
- [ ] 23.7 Run all property tests (100 iterations each)

### Task 24: Unit Tests
- [ ] 24.1 Create TemplateServiceTest
- [ ] 24.2 Create OrderServiceTest
- [ ] 24.3 Create PaymentServiceTest
- [ ] 24.4 Create PreviewServiceTest
- [ ] 24.5 Create MediaServiceTest
- [ ] 24.6 Create InvitationServiceTest
- [ ] 24.7 Create model tests (User, Invitation, Guest, Order)
- [ ] 24.8 Achieve 80%+ unit test coverage

### Task 25: Integration Tests
- [ ] 25.1 Create payment flow integration test
- [ ] 25.2 Create media upload integration test
- [ ] 25.3 Create invitation publishing integration test
- [ ] 25.4 Create guest management integration test
- [ ] 25.5 Create RSVP flow integration test

### Task 26: Feature Tests
- [ ] 26.1 Create authentication tests
- [ ] 26.2 Create template preview tests
- [ ] 26.3 Create checkout tests
- [ ] 26.4 Create dashboard tests
- [ ] 26.5 Create guest management tests
- [ ] 26.6 Create RSVP tests
- [ ] 26.7 Create admin panel tests
- [ ] 26.8 Achieve 100% feature test coverage

---

## Deployment & Infrastructure

### Task 27: Error Tracking & Monitoring
- [ ] 27.1 Install and configure Sentry
- [ ] 27.2 Add Sentry DSN to .env
- [ ] 27.3 Configure error reporting
- [ ] 27.4 Test error tracking
- [ ] 27.5 Create health check endpoint
- [ ] 27.6 Test health check (database, Redis, R2)

### Task 28: Production Configuration
- [ ] 28.1 Configure Redis for cache and queue
- [ ] 28.2 Install and configure Laravel Horizon
- [ ] 28.3 Configure Cloudflare R2 for production
- [ ] 28.4 Set up email service (Mailgun, SES, etc.)
- [ ] 28.5 Configure Nginx with wildcard SSL
- [ ] 28.6 Set up subdomain routing
- [ ] 28.7 Configure custom domain support
- [ ] 28.8 Set up queue workers
- [ ] 28.9 Configure cron for scheduled tasks
- [ ] 28.10 Test production deployment

### Task 29: Seeding & Sample Data
- [ ] 29.1 Create ProductSeeder with base package and add-ons
- [ ] 29.2 Create TemplateSeeder (run templates:sync)
- [ ] 29.3 Create sample user with invitation
- [ ] 29.4 Create sample guests and RSVPs
- [ ] 29.5 Run all seeders and verify

### Task 30: Documentation
- [ ] 30.1 Update README.md with setup instructions
- [ ] 30.2 Document environment variables
- [ ] 30.3 Document Artisan commands
- [ ] 30.4 Document API endpoints (if any)
- [ ] 30.5 Document deployment process
- [ ] 30.6 Create user guide for pengantin
- [ ] 30.7 Create admin guide

---

## Pre-Launch Checklist

- [ ] 2 templates completed and mobile-responsive
- [ ] Preview interactive works without login
- [ ] sessionStorage flow preserves data through checkout
- [ ] Midtrans payment tested with sandbox (all methods)
- [ ] Webhook idempotent (tested with duplicate webhooks)
- [ ] File upload validates MIME (not extension)
- [ ] Subdomain routing works (including local dev)
- [ ] Wildcard SSL active in staging
- [ ] Sentry connected and tracking errors
- [ ] Rate limiting active on public endpoints
- [ ] Admin panel can change product prices without deploy
- [ ] templates:sync can add new templates without restart

---

## Task Status Legend

- `[ ]` Not started
- `[~]` In progress
- `[x]` Completed
- `[*]` Optional (can be skipped for MVP)

---

**Total Tasks:** 30 main tasks with 400+ sub-tasks
**Estimated Duration:** 4 weeks (MVP Phase 1)
**Priority:** Follow week-by-week order for optimal flow
