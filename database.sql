
CREATE DATABASE IF NOT EXISTS vxjtgclw_nairobi_survey CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vxjtgclw_nairobi_survey;
CREATE TABLE survey_responses (
id INT PRIMARY KEY AUTO_INCREMENT,
-- Basic Information
gender VARCHAR(20),
age VARCHAR(20),
education VARCHAR(50),
occupation VARCHAR(50),
occupation_other TEXT,
income VARCHAR(30),
sub_county VARCHAR(100),
ward VARCHAR(100),
estate VARCHAR(100),
residence_privacy BOOLEAN DEFAULT 0,
car_ownership VARCHAR(10),
bus_usage VARCHAR(20),
walking_usage VARCHAR(20),
trip_origin TEXT,
trip_destination TEXT,
transport_mode VARCHAR(30),
transport_mode_other TEXT,

-- WOD Questions
general_safety VARCHAR(20),
accident_concern VARCHAR(30),
driver_yield VARCHAR(20),
night_safety VARCHAR(20),
walkway_importance VARCHAR(30),
obstacles_frequency VARCHAR(20),
path_connectivity VARCHAR(30),
comfort_satisfaction VARCHAR(30),

-- Additional sections would follow the same pattern
-- Infrastructure Quality
walkway_obstruction VARCHAR(20),
street_lighting VARCHAR(30),
road_surface_safety VARCHAR(20),
traffic_calming VARCHAR(30),

-- Socioeconomic Context
income_limitation VARCHAR(20),
affordability_influence VARCHAR(30),
cost_effect VARCHAR(20),

-- Pedestrian Mobility Patterns
walk_frequency VARCHAR(20),
safety_route_choice VARCHAR(20),
leisure_walk VARCHAR(20),

-- Traffic-Related Safety Risks
vehicle_speed_risk VARCHAR(20),
witness_accidents VARCHAR(20),
crossing_danger VARCHAR(30),

-- Last Mile Accessibility
bus_stop_convenience VARCHAR(30),
path_friendliness VARCHAR(30),
job_accessibility VARCHAR(30),

-- Accessibility for Vulnerable Groups
vulnerable_accommodation VARCHAR(20),
children_school_safety VARCHAR(30),
wheelchair_accessibility VARCHAR(30),
equal_access_effectiveness VARCHAR(30),

-- Barriers and Comments
barriers TEXT,
barriers_other TEXT,
additional_comments TEXT,

-- Metadata
submission_time DATETIME DEFAULT CURRENT_TIMESTAMP,
ip_address VARCHAR(45),
user_agent TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Create admin user table
CREATE TABLE admin_users (
id INT PRIMARY KEY AUTO_INCREMENT,
username VARCHAR(50) UNIQUE NOT NULL,
password_hash VARCHAR(255) NOT NULL,
email VARCHAR(100),
full_name VARCHAR(100),
role ENUM('admin', 'viewer') DEFAULT 'viewer',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
last_login TIMESTAMP NULL
);
-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password_hash, email, full_name, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'Administrator', 'admin');
-- Create indexes for better performance
CREATE INDEX idx_submission_time ON survey_responses(submission_time);
CREATE INDEX idx_gender ON survey_responses(gender);
CREATE INDEX idx_age ON survey_responses(age);
CREATE INDEX idx_education ON survey_responses(education);
CREATE INDEX idx_income ON survey_responses(income);
