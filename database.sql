-- Create the database
CREATE DATABASE IF NOT EXISTS kristian_tours;
USE kristian_tours;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    date_of_birth DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    role ENUM('user', 'admin') DEFAULT 'user'
);

-- Create user_profiles table for additional user information
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    profile_picture VARCHAR(255),
    preferences JSON,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create user_sessions table for tracking user logins
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create password_resets table for handling password reset requests
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reset_token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create email_verifications table for handling email verification
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    verification_token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_session_token ON user_sessions(session_token);
CREATE INDEX idx_reset_token ON password_resets(reset_token);
CREATE INDEX idx_verification_token ON email_verifications(verification_token);

-- Create Destinations table
CREATE TABLE destinations (
    destination_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    description TEXT,
    main_image_url VARCHAR(255),
    featured BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Packages table
CREATE TABLE packages (
    package_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration_days INTEGER NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    featured BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Package_Destinations (for packages with multiple destinations)
CREATE TABLE package_destinations (
    package_id INTEGER REFERENCES packages(package_id),
    destination_id INTEGER REFERENCES destinations(destination_id),
    day_number INTEGER NOT NULL,
    PRIMARY KEY (package_id, destination_id)
);

-- Create Activities table
CREATE TABLE activities (
    activity_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration_hours DECIMAL(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Package_Activities
CREATE TABLE package_activities (
    package_id INTEGER REFERENCES packages(package_id),
    activity_id INTEGER REFERENCES activities(activity_id),
    day_number INTEGER NOT NULL,
    included_in_price BOOLEAN DEFAULT true,
    PRIMARY KEY (package_id, activity_id)
);

-- Create Bookings table
CREATE TABLE bookings (
    booking_id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    package_id INTEGER REFERENCES packages(package_id),
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    travel_date DATE NOT NULL,
    number_of_travelers INTEGER NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(20) DEFAULT 'PENDING',
    payment_method VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Reviews table
CREATE TABLE reviews (
    review_id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    package_id INTEGER REFERENCES packages(package_id),
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Images table (for multiple images per destination/package)
CREATE TABLE images (
    image_id SERIAL PRIMARY KEY,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(100),
    destination_id INTEGER REFERENCES destinations(destination_id),
    package_id INTEGER REFERENCES packages(package_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Newsletter_Subscribers table
CREATE TABLE newsletter_subscribers (
    subscriber_id SERIAL PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT true
);

-- Create Job_Postings table
CREATE TABLE job_postings (
    job_id SERIAL PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    requirements TEXT,
    location VARCHAR(100),
    salary_range VARCHAR(50),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Job_Applications table
CREATE TABLE job_applications (
    application_id SERIAL PRIMARY KEY,
    job_id INTEGER REFERENCES job_postings(job_id),
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    resume_url VARCHAR(255),
    cover_letter TEXT,
    status VARCHAR(20) DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Contact_Messages table
CREATE TABLE contact_messages (
    message_id SERIAL PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT,
    status VARCHAR(20) DEFAULT 'UNREAD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data for Destinations
INSERT INTO destinations (name, country, description, main_image_url, featured) VALUES
('Greek Islands', 'Greece', 'Experience the magic of ancient ruins and crystal-clear waters', 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963', true),
('Croatian Coast', 'Croatia', 'Discover the gems of the Adriatic Sea', 'https://images.unsplash.com/photo-1596097155664-4f5c49ba1b69', true);

-- Insert sample data for Packages
INSERT INTO packages (name, description, duration_days, price, featured) VALUES
('Mediterranean Dream', 'Experience the best of Mediterranean beaches and culture', 10, 2999.00, true),
('Coastal Paradise', 'Explore beautiful coastlines and historic cities', 7, 2499.00, true);

-- Insert sample activities
INSERT INTO activities (name, description, duration_hours) VALUES
('Santorini Sunset Cruise', 'Experience the world-famous Santorini sunset from the water', 4),
('Dubrovnik City Walls Tour', 'Walk the historic walls of Dubrovnik', 3),
('Wine Tasting Experience', 'Sample local wines with expert sommeliers', 2); 