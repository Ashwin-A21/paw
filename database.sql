CREATE DATABASE IF NOT EXISTS paw_rescue_db;
USE paw_rescue_db;

-- Users Table with expanded roles
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'user', 'volunteer', 'rescuer') DEFAULT 'user',
    is_verified BOOLEAN DEFAULT FALSE,
    lives_saved INT DEFAULT 0,
    gender VARCHAR(20) DEFAULT NULL,
    dob DATE DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pets Table (For Adoptions)
CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type ENUM('dog', 'cat', 'bird', 'other') NOT NULL,
    breed VARCHAR(50),
    age VARCHAR(20),
    gender ENUM('Male', 'Female') NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT 'default_pet.jpg',
    status ENUM('Available', 'Adopted', 'Pending') DEFAULT 'Available',
    added_by INT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Adoption Applications
CREATE TABLE IF NOT EXISTS adoption_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    pet_id INT,
    message TEXT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    admin_notes TEXT,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);

-- Rescue Reports (Anyone can report, rescuers/volunteers respond)
CREATE TABLE IF NOT EXISTS rescue_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT,
    reporter_name VARCHAR(100),
    contact_phone VARCHAR(20),
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    description TEXT NOT NULL,
    animal_type VARCHAR(50),
    urgency ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    image VARCHAR(255),
    status ENUM('Reported', 'Assigned', 'In Progress', 'Rescued', 'Closed') DEFAULT 'Reported',
    assigned_to INT,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Blogs / Success Stories
CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150),
    content TEXT NOT NULL,
    author_id INT,
    author VARCHAR(100) DEFAULT 'Admin',
    image VARCHAR(255),
    is_published BOOLEAN DEFAULT TRUE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Volunteer/Rescuer Tasks (Admin assigns tasks)
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assigned_to INT,
    rescue_report_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    due_date DATE,
    status ENUM('Assigned', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Assigned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (rescue_report_id) REFERENCES rescue_reports(id) ON DELETE SET NULL
);

-- Donations (optional feature)
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_name VARCHAR(100),
    donor_email VARCHAR(100),
    amount DECIMAL(10, 2) NOT NULL,
    message TEXT,
    payment_status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    donated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact Messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Plain text passwords for development (Password: 1234)
INSERT INTO users (username, email, password, role, is_verified, lives_saved, gender, phone) VALUES 
('Admin', 'admin@paw.com', '1234', 'admin', TRUE, 0, 'Male', '1234567890'),
('John User', 'john@example.com', '1234', 'user', TRUE, 0, 'Male', '9876543210'),
('Sarah Volunteer', 'sarah@volunteer.com', '1234', 'volunteer', TRUE, 15, 'Female', '5556667777'),
('Mike Rescuer', 'mike@rescuer.com', '1234', 'rescuer', TRUE, 22, 'Male', '8889990000');

-- Sample Pets
INSERT INTO pets (name, type, breed, age, gender, description, image, status, added_by) VALUES 
('Bella', 'dog', 'Labrador Retriever', '2 years', 'Female', 'Bella is a friendly and energetic Labrador who loves playing fetch and swimming. She is great with kids and other pets.', 'pet1.jpg', 'Available', 1),
('Milo', 'cat', 'Siamese', '1 year', 'Male', 'Milo is a calm and affectionate Siamese cat. He loves naps and being petted. Perfect for apartment living.', 'pet2.jpg', 'Available', 1),
('Rocky', 'dog', 'German Shepherd', '3 years', 'Male', 'Rocky is a loyal and intelligent German Shepherd. He has been trained in basic commands and is very protective.', 'pet3.jpg', 'Available', 1),
('Luna', 'cat', 'Persian', '2 years', 'Female', 'Luna is a beautiful Persian cat with a fluffy white coat. She enjoys quiet environments and gentle handling.', 'pet4.jpg', 'Available', 1),
('Max', 'dog', 'Golden Retriever', '4 years', 'Male', 'Max is a gentle giant who loves everyone he meets. Perfect for families looking for a loyal companion.', 'pet5.jpg', 'Available', 1);

-- Sample Blog
INSERT INTO blogs (title, slug, content, author_id, author, status, is_published) VALUES 
('Why Adopt a Pet?', 'why-adopt-a-pet', 'Adopting a pet saves two lives: the one you adopt and the one who takes their place in the shelter. Every year, millions of animals end up in shelters. By adopting, you give them a second chance at happiness while also gaining a loyal companion.\n\nAdopted pets are often already vaccinated, spayed/neutered, and microchipped, saving you time and money. Plus, shelters can help match you with a pet that fits your lifestyle.\n\nMake a difference today – adopt, don''t shop!', 1, 'Admin', 'approved', 1),
('How to Prepare Your Home for a New Pet', 'prepare-home-new-pet', 'Bringing a new pet home is exciting! Here are some tips to prepare:\n\n1. Pet-proof your home by removing hazardous items\n2. Set up a comfortable sleeping area\n3. Stock up on food, treats, and toys\n4. Schedule a vet visit\n5. Be patient during the adjustment period\n\nRemember, your new pet may need time to settle in. Give them love and patience!', 1, 'Admin', 'approved', 1);

-- Sample Rescue Report
INSERT INTO rescue_reports (reporter_name, contact_phone, location, description, animal_type, urgency, status) VALUES 
('Anonymous', '9876543210', 'Near City Park, Main Street', 'Injured stray dog found near the park. Appears to have a leg injury and is limping.', 'Dog', 'High', 'Reported');
