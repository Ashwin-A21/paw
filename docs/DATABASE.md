# Database Schema Documentation - Paw Pal

## 1. Database Overview

- **Storage Engine**: InnoDB
- **Character Set**: utf8mb4
- **Schema Name**: `paw_rescue_db`

## 2. Table Definitions

### 2.1. `users`

Stores all account data for users, volunteers, rescuers, and admins.
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT | PK, AI | Unique identifier |
| `username` | VARCHAR(50) | NOT NULL | Display name |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | Login email |
| `password` | VARCHAR(255) | NOT NULL | Hashed password |
| `role` | ENUM | default 'user' | admin, user, volunteer, rescuer |
| `is_verified`| BOOLEAN | default FALSE | Verified status |

### 2.2. `pets`

Stores information about pets available for adoption.
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT | PK, AI | Unique identifier |
| `name` | VARCHAR(50) | NOT NULL | Pet name |
| `type` | ENUM | NOT NULL | dog, cat, bird, other |
| `status` | ENUM | default 'Available'| Available, Adopted, Pending |
| `added_by` | INT | FK -> users.id | Submitter ID |

### 2.3. `adoption_applications`

Links users to their adoption interests.
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT | PK, AI | Unique identifier |
| `user_id` | INT | FK -> users.id | Applicant ID |
| `pet_id` | INT | FK -> pets.id | Pet ID |
| `status` | ENUM | default 'Pending' | Pending, Approved, Rejected |

### 2.4. `rescue_reports`

Dynamic reports of animals in distress.
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT | PK, AI | Unique identifier |
| `location` | VARCHAR(255) | NOT NULL | textual location |
| `urgency` | ENUM | default 'Medium' | Low, Medium, High, Critical |
| `status` | ENUM | default 'Reported' | Reported, Assigned, In Progress, Rescued, Closed |

### 2.5. `tasks`

Specific tasks assigned to volunteers.
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT | PK, AI | Unique identifier |
| `assigned_to`| INT | FK -> users.id | Volunteer/Rescuer ID |
| `status` | ENUM | default 'Assigned' | Assigned, In Progress, Completed, Cancelled |

## 3. Relationships

- **users** : **pets** (1:N) - One user (admin) can add many pets.
- **users** : **adoption_applications** (1:N) - One user can apply for multiple pets.
- **pets** : **adoption_applications** (1:N) - One pet can have multiple applications.
- **rescue_reports** : **tasks** (1:N) - One rescue case can result in multiple management tasks.

---

_Last Updated: February 2, 2026_
