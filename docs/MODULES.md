# Module Documentation - Paw Pal

## 1. Core Modules (Root Directory)

### `index.php`

- **Purpose**: Main landing page and entry point.
- **Key Features**: Statistics overview, Hero section with GSAP, and links to all primary features.

### `adopt.php` & `adopt-apply.php`

- **Purpose**: Adoption gallery and application logic.
- **Functionality**: Queries `pets` table for "Available" status, displays pet cards, and handles adoption requests.

### `rescue.php`

- **Purpose**: Emergency rescue reporting.
- **Functionality**: Provides a form for users to report animals in distress. Integrated with `rescue_reports` table.

### `blogs.php` & `blog-single.php`

- **Purpose**: Knowledge sharing and success stories.
- **Functionality**: Dynamic rendering of blog content from the `blogs` table.

### `centers.php`

- **Purpose**: Verified partners directory.
- **Functionality**: Displays vetted NGOs and veterinarian clinics.

---

## 2. Administrative Module (`/admin`)

### `admin/index.php`

- **Purpose**: Admin command center.
- **Functionality**: High-level statistics (Total Pets, Pending Apps, Active Rescues) and recent activity logs.

### `admin/pets.php`

- **Purpose**: Inventory management for pets.
- **Functionality**: CRUD operations (Create, Read, Update, Delete) for the `pets` table.

### `admin/applications.php`

- **Purpose**: Review process for adoptions.
- **Functionality**: Approve or Reject incoming applications from users.

### `admin/rescues.php`

- **Purpose**: Rescue coordination.
- **Functionality**: Manage incoming rescue reports and assign them to volunteers/rescuers.

---

## 3. Volunteer/Rescuer Module (`/volunteer`)

### `volunteer/index.php`

- **Purpose**: Task dashboard for field workers.
- **Functionality**: Displays assigned tasks and active rescue reports filtered by urgency.

### `volunteer/tasks.php`

- **Purpose**: Task management.
- **Functionality**: Allows volunteers to see detailed task info and update progress.

---

## 4. Supporting Modules

### `config.php`

- **Purpose**: Database configuration.
- **Functionality**: Establishes connection to the MySQL server and sets global constants.

### `includes/`

- **Purpose**: Reusable snippets (Headers, Footers, utility functions).
- **Currently**: Contains layout components to maintain DRY (Don't Repeat Yourself) principle.

---

_Last Updated: February 2, 2026_
