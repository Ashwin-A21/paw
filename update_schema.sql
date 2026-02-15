-- Create Feedback Table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Organization Type if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS organization_type ENUM('Individual', 'Charity', 'Organization', 'Trust') DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS organization_name VARCHAR(150) DEFAULT NULL;

-- Role Requests Table
CREATE TABLE IF NOT EXISTS role_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_role VARCHAR(50) NOT NULL,
    organization_name VARCHAR(150),
    organization_type ENUM('Individual', 'Charity', 'Organization', 'Trust'),
    document_proof VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
