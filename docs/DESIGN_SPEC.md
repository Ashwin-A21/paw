# Design & Architecture Specifications - Paw Pal

## 1. Technology Stack

- **Frontend**: HTML5, Tailwind CSS (via CDN), GSAP (Animations), Lucide Icons.
- **Backend**: PHP (Procedural/Intermediate).
- **Database**: MySQL.
- **Client-side Logic**: Vanilla JavaScript.
- **Design Principles**: Glassmorphism, Modern Typography (Plus Jakarta Sans & Cormorant Garamond), Magnetic Hover Effects.

## 2. System Architecture

Paw Pal follows a standard Client-Server architecture:

- **Client**: Browser-based interface handling interactions and animations.
- **Server**: PHP engine processing logic, session management, and database operations.
- **Storage**: MySQL database for persistent storage.

### Data Flow

1. User interacts with the UI (e.g., submits an adoption form).
2. JavaScript/HTML sends a POST request to a PHP endpoint (e.g., `adopt-apply.php`).
3. PHP validates the session and input.
4. PHP executes a SQL query on the MySQL database.
5. PHP redirects the user or provides feedback based on the database result.

## 3. UI/UX Design System

### Color Palette

| Color      | Hex       | Usage                           |
| ---------- | --------- | ------------------------------- |
| Paw BG     | `#F9F8F6` | Primary background              |
| Paw Dark   | `#2D2825` | Primary text and dark sections  |
| Paw Accent | `#D4A373` | Buttons, highlights, highlights |
| Paw Alert  | `#E07A5F` | Emergency alerts, rescue CTAs   |
| Paw Gray   | `#9D958F` | Secondary text                  |

### Typography

- **Heading (Serif)**: `Cormorant Garamond` - Used for primary headings and elegant italic highlights.
- **Body (Sans)**: `Plus Jakarta Sans` - Used for modern, readable body text and UI components.

### Visual Effects

- **Glassmorphism**: Applied to the navbar and dashboard cards using `backdrop-filter: blur(12px)`.
- **Magnetic Components**: Interactive elements (buttons, logos) utilize GSAP to "pull" towards the cursor.
- **Parallax**: Background shapes move at varying speeds relative to scroll/mouse movement.

## 4. Security Architecture

- **Session Management**: Secure PHP sessions control access to `admin/` and `volunteer/` subdirectories.
- **Input Sanitization**: Use of `mysqli_real_escape_string` and prepared statements to prevent SQL injection.
- **Access Control**: Role-based redirection in `index.php` and sub-module indices.

---

_Last Updated: February 2, 2026_
