# Database Updates - April 16, 2026

Run the following SQL queries in your XAMPP phpMyAdmin (SQL tab) to sync your database with the latest platform features.

### 1. Proof of Rescue Verification
This adds the field required to store the photographic evidence when a rescuer marks a mission as "Rescued".

```sql
ALTER TABLE rescue_reports 
ADD COLUMN proof_image VARCHAR(255) DEFAULT NULL;
```

### 2. Notifications System (Ensuring Table Exists)
If your database does not yet have the notifications table for assignment alerts, run this:

```sql
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 3. User Verification & Stats (Safety Check)
Ensuring columns used for the admin management and performance stats are present:

```sql
-- Adds Lives Saved tracking if missing
ALTER TABLE users ADD COLUMN IF NOT EXISTS lives_saved INT DEFAULT 0;

-- Provides verification status if missing
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0;

-- Role support for rescuers/volunteers
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'volunteer', 'rescuer') DEFAULT 'user';
```

---

> [!IMPORTANT]
> **Password Hashing Reversion**: The platform now uses plaintext passwords as per your request. If you have existing users with hashed passwords, you may need to manually update their password strings in the `users` table to plaintext in order for them to log in, or have them register new accounts.
