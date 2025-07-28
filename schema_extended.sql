-- Extended MomCare AI Assistant Database Schema
-- Additional tables for comprehensive pregnancy management features

USE momcare_ai;

-- Health data tracking tables
CREATE TABLE IF NOT EXISTS health_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    metric_type ENUM('blood_pressure', 'blood_sugar', 'weight', 'heart_rate', 'temperature') NOT NULL,
    systolic INT NULL, -- for blood pressure
    diastolic INT NULL, -- for blood pressure 
    value DECIMAL(10,2) NOT NULL, -- for weight, sugar, etc.
    unit VARCHAR(20) NOT NULL DEFAULT 'unit',
    notes TEXT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_metric_date (user_id, metric_type, recorded_at)
);

-- Medication and reminders
CREATE TABLE IF NOT EXISTS medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medication_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    frequency VARCHAR(100) NOT NULL, -- e.g., "2 times daily", "every 8 hours"
    start_date DATE NOT NULL,
    end_date DATE NULL,
    instructions TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS medication_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medication_id INT NOT NULL,
    reminder_time TIME NOT NULL,
    is_taken BOOLEAN DEFAULT FALSE,
    taken_at TIMESTAMP NULL,
    reminder_date DATE NOT NULL,
    notes TEXT NULL,
    FOREIGN KEY (medication_id) REFERENCES medications(id) ON DELETE CASCADE,
    INDEX idx_reminder_date_time (reminder_date, reminder_time)
);

-- Pregnancy milestones and tips
CREATE TABLE IF NOT EXISTS pregnancy_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_number INT NOT NULL,
    category ENUM('health', 'nutrition', 'exercise', 'mental_health', 'preparation') NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_week_category (week_number, category)
);

-- Wellness classes and appointments
CREATE TABLE IF NOT EXISTS wellness_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(255) NOT NULL,
    class_type ENUM('yoga', 'meditation', 'nutrition', 'birthing', 'parenting') NOT NULL,
    description TEXT NULL,
    instructor_name VARCHAR(255) NULL,
    duration_minutes INT DEFAULT 60,
    max_participants INT DEFAULT 10,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS class_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    scheduled_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(500) NULL,
    is_virtual BOOLEAN DEFAULT FALSE,
    virtual_link VARCHAR(500) NULL,
    available_spots INT DEFAULT 10,
    FOREIGN KEY (class_id) REFERENCES wellness_classes(id) ON DELETE CASCADE,
    INDEX idx_schedule_date (scheduled_date, start_time)
);

CREATE TABLE IF NOT EXISTS class_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    schedule_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attendance_status ENUM('enrolled', 'attended', 'missed', 'cancelled') DEFAULT 'enrolled',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES class_schedules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, schedule_id)
);

-- Emergency contacts and hospitals
CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    emergency_phone VARCHAR(20) NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    specialties TEXT NULL, -- JSON array of specialties
    has_maternity BOOLEAN DEFAULT TRUE,
    has_nicu BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_location (latitude, longitude)
);

-- Telehealth consultations
CREATE TABLE IF NOT EXISTS telehealth_consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_name VARCHAR(255) NOT NULL,
    doctor_specialty VARCHAR(100) NULL,
    consultation_date DATETIME NOT NULL,
    duration_minutes INT DEFAULT 30,
    consultation_type ENUM('routine', 'urgent', 'follow_up') DEFAULT 'routine',
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    meeting_link VARCHAR(500) NULL,
    notes TEXT NULL,
    prescription TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, consultation_date)
);

-- Health reports and analytics
CREATE TABLE IF NOT EXISTS health_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_type ENUM('weekly', 'monthly', 'trimester') NOT NULL,
    report_period VARCHAR(50) NOT NULL, -- e.g., "2025-W04", "2025-01", "T1-2025"
    generated_data JSON NOT NULL, -- Stores charts data, insights, etc.
    insights TEXT NULL,
    recommendations TEXT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_period (user_id, report_period)
);

-- Mindfulness and emotional support
CREATE TABLE IF NOT EXISTS mindfulness_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_type ENUM('meditation', 'breathing', 'relaxation', 'affirmation') NOT NULL,
    duration_minutes INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mood_before ENUM('anxious', 'stressed', 'neutral', 'calm', 'happy') NULL,
    mood_after ENUM('anxious', 'stressed', 'neutral', 'calm', 'happy') NULL,
    notes TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, completed_at)
);

-- User preferences and settings
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    notification_preferences JSON NULL, -- Settings for different types of notifications
    privacy_settings JSON NULL,
    wearable_device_connected BOOLEAN DEFAULT FALSE,
    wearable_device_type VARCHAR(100) NULL,
    emergency_contact_1 VARCHAR(20) NULL,
    emergency_contact_2 VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_prefs (user_id)
);

-- Wearable device data integration
CREATE TABLE IF NOT EXISTS wearable_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_type VARCHAR(100) NOT NULL,
    data_type ENUM('steps', 'heart_rate', 'sleep', 'activity') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    recorded_at TIMESTAMP NOT NULL,
    synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_data_date (user_id, data_type, recorded_at)
);

-- Chat conversation contexts (for better AI responses)
CREATE TABLE IF NOT EXISTS chat_contexts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    context_type ENUM('current_symptoms', 'recent_concerns', 'upcoming_appointments', 'medication_questions') NOT NULL,
    context_data JSON NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_context (user_id, context_type)
);

-- Educational content and articles
CREATE TABLE IF NOT EXISTS educational_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    target_week_start INT NULL,
    target_week_end INT NULL,
    content_type ENUM('article', 'video', 'infographic', 'checklist') DEFAULT 'article',
    language VARCHAR(10) DEFAULT 'en',
    reading_time_minutes INT DEFAULT 5,
    is_featured BOOLEAN DEFAULT FALSE,
    published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category_week (category, target_week_start, target_week_end)
);

-- User progress tracking
CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    milestone_type ENUM('appointment_completed', 'class_attended', 'medication_compliance', 'health_metric_logged') NOT NULL,
    milestone_date DATE NOT NULL,
    details JSON NULL,
    points_earned INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_milestone (user_id, milestone_type, milestone_date)
);

-- Sample data insertion
INSERT INTO pregnancy_tips (week_number, category, title, content) VALUES
(1, 'health', 'Start Taking Prenatal Vitamins', 'Begin taking prenatal vitamins with folic acid to support your baby\'s neural tube development.'),
(1, 'nutrition', 'Eat Folate-Rich Foods', 'Include leafy greens, citrus fruits, and fortified cereals in your diet.'),
(4, 'health', 'Schedule Your First Prenatal Visit', 'Contact your healthcare provider to schedule your first prenatal appointment.'),
(8, 'mental_health', 'Practice Relaxation Techniques', 'Try deep breathing exercises and meditation to manage early pregnancy anxiety.'),
(12, 'nutrition', 'Focus on Protein Intake', 'Ensure adequate protein intake for your baby\'s rapid growth during this period.'),
(16, 'exercise', 'Start Gentle Prenatal Yoga', 'Consider joining prenatal yoga classes to improve flexibility and reduce stress.'),
(20, 'preparation', 'Consider Anatomy Scan', 'This is typically when the detailed anatomy ultrasound is performed.'),
(24, 'health', 'Glucose Screening Test', 'Your doctor may recommend glucose screening for gestational diabetes.'),
(28, 'preparation', 'Start Childbirth Classes', 'Begin preparing for labor and delivery with childbirth education classes.'),
(32, 'health', 'Monitor Baby\'s Movements', 'Pay attention to your baby\'s movement patterns and report any changes.'),
(36, 'preparation', 'Prepare Hospital Bag', 'Start gathering items for your hospital stay and newborn care.');

INSERT INTO wellness_classes (class_name, class_type, description, instructor_name) VALUES
('Prenatal Yoga Flow', 'yoga', 'Gentle yoga poses designed specifically for pregnant women to improve flexibility and reduce stress.', 'Sarah Johnson'),
('Meditation for Expectant Mothers', 'meditation', 'Guided meditation sessions to promote relaxation and emotional well-being during pregnancy.', 'Dr. Emily Chen'),
('Nutrition During Pregnancy', 'nutrition', 'Learn about essential nutrients and healthy eating habits for you and your baby.', 'Nutritionist Lisa Brown'),
('Birthing and Breathing Techniques', 'birthing', 'Learn various breathing techniques and positions for labor and delivery.', 'Midwife Maria Rodriguez'),
('New Parent Preparation', 'parenting', 'Essential skills and knowledge for caring for your newborn baby.', 'Parenting Coach Tom Wilson');

INSERT INTO hospitals (name, address, phone, emergency_phone, latitude, longitude, specialties, has_maternity, has_nicu) VALUES
('City General Hospital', '123 Main St, Downtown', '(555) 123-4567', '(555) 911-0000', 40.7128, -74.0060, '["Obstetrics", "Gynecology", "Pediatrics", "NICU"]', TRUE, TRUE),
('Women\'s Health Center', '456 Oak Ave, Midtown', '(555) 234-5678', '(555) 911-0001', 40.7589, -73.9851, '["Maternal Medicine", "High-Risk Pregnancy", "Fertility"]', TRUE, TRUE),
('Community Medical Center', '789 Pine Rd, Suburbs', '(555) 345-6789', '(555) 911-0002', 40.6892, -74.0445, '["Family Medicine", "Emergency Care", "Obstetrics"]', TRUE, FALSE),
('Metropolitan Hospital', '321 Elm St, Central', '(555) 456-7890', '(555) 911-0003', 40.7831, -73.9712, '["Cardiology", "Neurology", "Maternal-Fetal Medicine"]', TRUE, TRUE);

INSERT INTO educational_content (title, content, category, target_week_start, target_week_end, content_type) VALUES
('First Trimester Nutrition Guide', 'During your first trimester, focus on getting enough folic acid, iron, and calcium. Morning sickness may affect your appetite, so eat small, frequent meals...', 'nutrition', 1, 12, 'article'),
('Understanding Prenatal Vitamins', 'Prenatal vitamins fill nutritional gaps in your diet. Look for vitamins containing folic acid, iron, calcium, DHA, and vitamin D...', 'health', 1, 40, 'article'),
('Safe Exercises During Pregnancy', 'Regular exercise during pregnancy can help you feel better and prepare your body for labor. Safe activities include walking, swimming, and prenatal yoga...', 'fitness', 1, 40, 'article'),
('Preparing for Labor', 'As you approach your due date, learn about the stages of labor, pain management options, and when to go to the hospital...', 'preparation', 32, 40, 'article');

-- Comprehensive Sample Data Insertion

-- Sample users for testing (passwords are hashed versions of 'password123')
INSERT INTO users (username, email, password, full_name, phone_number, date_of_birth, expected_due_date, emergency_contact_name, emergency_contact_phone) VALUES
('sarah_mom', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson', '(555) 123-4567', '1992-03-15', '2025-09-15', 'Mike Johnson', '(555) 987-6543'),
('emily_expecting', 'emily@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emily Chen', '(555) 234-5678', '1990-07-22', '2025-10-20', 'David Chen', '(555) 876-5432'),
('maria_first', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Rodriguez', '(555) 345-6789', '1995-11-08', '2025-12-01', 'Carlos Rodriguez', '(555) 765-4321'),
('lisa_wellness', 'lisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa Thompson', '(555) 456-7890', '1988-05-03', '2025-08-10', 'John Thompson', '(555) 654-3210');

-- Sample doctors
INSERT INTO doctors (full_name, email, phone_number, specialization, license_number, is_active) VALUES
('Dr. Amanda Williams', 'dr.williams@hospital.com', '(555) 111-2222', 'Obstetrics & Gynecology', 'MD12345', TRUE),
('Dr. Robert Chang', 'dr.chang@hospital.com', '(555) 222-3333', 'Maternal-Fetal Medicine', 'MD23456', TRUE),
('Dr. Jennifer Davis', 'dr.davis@hospital.com', '(555) 333-4444', 'Family Medicine', 'MD34567', TRUE),
('Dr. Michael Brown', 'dr.brown@hospital.com', '(555) 444-5555', 'Pediatrics', 'MD45678', TRUE),
('Dr. Lisa Garcia', 'dr.garcia@hospital.com', '(555) 555-6666', 'Endocrinology', 'MD56789', TRUE);

-- Sample health metrics data
INSERT INTO health_metrics (user_id, metric_type, systolic, diastolic, value, unit, notes, recorded_at) VALUES
(1, 'blood_pressure', 120, 80, 120, 'mmHg', 'Normal reading', '2025-07-01 08:00:00'),
(1, 'blood_pressure', 118, 78, 118, 'mmHg', 'Slight improvement', '2025-07-07 08:15:00'),
(1, 'weight', NULL, NULL, 65.5, 'kg', 'Weekly weigh-in', '2025-07-01 09:00:00'),
(1, 'weight', NULL, NULL, 66.0, 'kg', 'Healthy weight gain', '2025-07-08 09:00:00'),
(1, 'blood_sugar', NULL, NULL, 95, 'mg/dL', 'Fasting glucose', '2025-07-05 07:00:00'),
(2, 'blood_pressure', 125, 82, 125, 'mmHg', 'Slightly elevated', '2025-07-02 08:30:00'),
(2, 'weight', NULL, NULL, 58.2, 'kg', 'Pre-pregnancy weight', '2025-07-01 10:00:00'),
(2, 'weight', NULL, NULL, 58.8, 'kg', 'Good progress', '2025-07-08 10:00:00'),
(3, 'heart_rate', NULL, NULL, 72, 'bpm', 'Resting heart rate', '2025-07-03 09:30:00'),
(3, 'temperature', NULL, NULL, 98.6, 'F', 'Normal temperature', '2025-07-03 10:00:00');

-- Sample medications
INSERT INTO medications (user_id, medication_name, dosage, frequency, start_date, end_date, instructions) VALUES
(1, 'Prenatal Vitamins', '1 tablet', 'Once daily', '2025-06-01', NULL, 'Take with food to avoid nausea'),
(1, 'Folic Acid', '400 mcg', 'Once daily', '2025-06-01', NULL, 'Essential for neural tube development'),
(1, 'Iron Supplement', '27 mg', 'Once daily', '2025-07-01', NULL, 'Take with vitamin C for better absorption'),
(2, 'Prenatal Vitamins', '1 tablet', 'Once daily', '2025-06-15', NULL, 'Take in the morning'),
(2, 'Calcium Supplement', '1000 mg', 'Twice daily', '2025-06-20', NULL, 'Take with meals'),
(3, 'Prenatal Vitamins', '1 tablet', 'Once daily', '2025-07-01', NULL, 'New prescription'),
(4, 'Prenatal Vitamins', '1 tablet', 'Once daily', '2025-05-01', NULL, 'Continuing from pre-pregnancy');

-- Sample medication reminders
INSERT INTO medication_reminders (medication_id, reminder_time, reminder_date, is_taken, taken_at) VALUES
(1, '08:00:00', '2025-07-28', TRUE, '2025-07-28 08:05:00'),
(1, '08:00:00', '2025-07-29', FALSE, NULL),
(2, '08:00:00', '2025-07-28', TRUE, '2025-07-28 08:05:00'),
(3, '20:00:00', '2025-07-28', FALSE, NULL),
(4, '09:00:00', '2025-07-28', TRUE, '2025-07-28 09:10:00'),
(5, '12:00:00', '2025-07-28', TRUE, '2025-07-28 12:15:00'),
(5, '18:00:00', '2025-07-28', FALSE, NULL);

-- Sample appointments
INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, appointment_type, notes, appointment_status) VALUES
(1, 1, '2025-08-05', '10:00:00', 'routine', 'Regular checkup at 20 weeks', 'scheduled'),
(1, 1, '2025-07-15', '14:30:00', 'ultrasound', 'Anatomy scan completed', 'completed'),
(2, 2, '2025-08-10', '11:30:00', 'consultation', 'High-risk pregnancy consultation', 'scheduled'),
(2, 1, '2025-07-20', '09:15:00', 'routine', 'First trimester visit', 'completed'),
(3, 3, '2025-08-02', '15:00:00', 'routine', 'Initial prenatal visit', 'scheduled'),
(4, 1, '2025-07-30', '16:30:00', 'follow-up', 'Blood pressure monitoring', 'scheduled');

-- Sample class schedules
INSERT INTO class_schedules (class_id, scheduled_date, start_time, end_time, location, is_virtual, virtual_link, available_spots) VALUES
(1, '2025-08-01', '10:00:00', '11:00:00', 'Wellness Center Room A', FALSE, NULL, 8),
(1, '2025-08-03', '10:00:00', '11:00:00', 'Wellness Center Room A', FALSE, NULL, 8),
(2, '2025-08-02', '14:00:00', '14:45:00', NULL, TRUE, 'https://zoom.us/j/meditation123', 15),
(3, '2025-08-05', '18:00:00', '19:30:00', 'Conference Room B', FALSE, NULL, 12),
(4, '2025-08-07', '16:00:00', '18:00:00', 'Birth Center', FALSE, NULL, 6),
(5, '2025-08-10', '13:00:00', '15:00:00', NULL, TRUE, 'https://zoom.us/j/parenting456', 20);

-- Sample class enrollments
INSERT INTO class_enrollments (user_id, schedule_id, attendance_status) VALUES
(1, 1, 'enrolled'),
(1, 2, 'enrolled'),
(1, 3, 'enrolled'),
(2, 2, 'enrolled'),
(2, 4, 'enrolled'),
(3, 1, 'enrolled'),
(3, 5, 'enrolled'),
(4, 3, 'enrolled');

-- Sample telehealth consultations
INSERT INTO telehealth_consultations (user_id, doctor_name, doctor_specialty, consultation_date, duration_minutes, consultation_type, status, meeting_link, notes) VALUES
(1, 'Dr. Amanda Williams', 'OB/GYN', '2025-08-01 15:00:00', 30, 'routine', 'scheduled', 'https://telehealth.momcare.com/session/12345', 'Routine 22-week checkup'),
(2, 'Dr. Robert Chang', 'Maternal-Fetal Medicine', '2025-07-25 11:00:00', 45, 'urgent', 'completed', 'https://telehealth.momcare.com/session/67890', 'Discussed test results - all normal'),
(3, 'Dr. Jennifer Davis', 'Family Medicine', '2025-08-05 09:30:00', 30, 'routine', 'scheduled', 'https://telehealth.momcare.com/session/11111', 'First prenatal visit'),
(4, 'Dr. Lisa Garcia', 'Endocrinology', '2025-07-30 14:00:00', 30, 'follow_up', 'scheduled', 'https://telehealth.momcare.com/session/22222', 'Gestational diabetes follow-up');

-- Sample mindfulness sessions
INSERT INTO mindfulness_sessions (user_id, session_type, duration_minutes, mood_before, mood_after, notes, completed_at) VALUES
(1, 'meditation', 15, 'stressed', 'calm', 'Felt much more relaxed after the session', '2025-07-27 20:00:00'),
(1, 'breathing', 10, 'anxious', 'neutral', 'Helped with morning anxiety', '2025-07-28 08:30:00'),
(2, 'meditation', 20, 'neutral', 'happy', 'Great way to start the day', '2025-07-28 07:00:00'),
(2, 'relaxation', 25, 'stressed', 'calm', 'Progressive muscle relaxation worked well', '2025-07-27 21:30:00'),
(3, 'affirmation', 5, 'anxious', 'calm', 'Positive affirmations helped confidence', '2025-07-28 09:00:00'),
(4, 'breathing', 12, 'stressed', 'neutral', 'Deep breathing exercises', '2025-07-27 19:00:00');

-- Sample user preferences
INSERT INTO user_preferences (user_id, language, timezone, notification_preferences, privacy_settings, emergency_contact_1, emergency_contact_2) VALUES
(1, 'en', 'America/New_York', '{"email": true, "sms": false, "push": true, "appointment_reminders": true, "medication_reminders": true}', '{"share_data": false, "analytics": true}', '(555) 987-6543', '(555) 111-9999'),
(2, 'en', 'America/Los_Angeles', '{"email": true, "sms": true, "push": true, "appointment_reminders": true, "medication_reminders": true}', '{"share_data": true, "analytics": true}', '(555) 876-5432', NULL),
(3, 'es', 'America/Chicago', '{"email": true, "sms": false, "push": false, "appointment_reminders": true, "medication_reminders": false}', '{"share_data": false, "analytics": false}', '(555) 765-4321', '(555) 222-8888'),
(4, 'en', 'America/Denver', '{"email": false, "sms": true, "push": true, "appointment_reminders": true, "medication_reminders": true}', '{"share_data": true, "analytics": true}', '(555) 654-3210', NULL);

-- Sample wearable data
INSERT INTO wearable_data (user_id, device_type, data_type, value, unit, recorded_at) VALUES
(1, 'Apple Watch', 'steps', 8500, 'steps', '2025-07-27 23:59:59'),
(1, 'Apple Watch', 'heart_rate', 72, 'bpm', '2025-07-28 12:00:00'),
(1, 'Apple Watch', 'sleep', 7.5, 'hours', '2025-07-28 07:00:00'),
(2, 'Fitbit', 'steps', 6200, 'steps', '2025-07-27 23:59:59'),
(2, 'Fitbit', 'heart_rate', 68, 'bpm', '2025-07-28 11:30:00'),
(3, 'Samsung Galaxy Watch', 'steps', 4800, 'steps', '2025-07-27 23:59:59'),
(4, 'Garmin', 'activity', 45, 'minutes', '2025-07-28 10:00:00');

-- Sample chat contexts for AI personalization
INSERT INTO chat_contexts (user_id, context_type, context_data, expires_at) VALUES
(1, 'current_symptoms', '{"symptoms": ["morning sickness", "fatigue"], "severity": "mild", "duration": "2 weeks"}', '2025-08-28 00:00:00'),
(1, 'recent_concerns', '{"concerns": ["weight gain", "baby movement"], "discussed_with_doctor": false}', '2025-08-28 00:00:00'),
(1, 'upcoming_appointments', '{"next_appointment": "2025-08-05", "doctor": "Dr. Amanda Williams", "type": "routine"}', '2025-08-05 23:59:59'),
(2, 'medication_questions', '{"current_medications": ["prenatal_vitamins", "calcium"], "questions": ["timing", "interactions"]}', '2025-08-28 00:00:00'),
(3, 'current_symptoms', '{"symptoms": ["back pain", "heartburn"], "severity": "moderate", "duration": "1 week"}', '2025-08-28 00:00:00'),
(4, 'recent_concerns', '{"concerns": ["blood pressure", "gestational diabetes"], "discussed_with_doctor": true}', '2025-08-28 00:00:00');

-- Sample user progress tracking
INSERT INTO user_progress (user_id, milestone_type, milestone_date, details, points_earned) VALUES
(1, 'appointment_completed', '2025-07-15', '{"appointment_type": "ultrasound", "doctor": "Dr. Williams"}', 10),
(1, 'health_metric_logged', '2025-07-28', '{"metric": "blood_pressure", "value": "118/78"}', 5),
(1, 'medication_compliance', '2025-07-28', '{"medication": "prenatal_vitamins", "taken": true}', 2),
(2, 'class_attended', '2025-07-20', '{"class": "Prenatal Yoga", "instructor": "Sarah Johnson"}', 8),
(2, 'appointment_completed', '2025-07-20', '{"appointment_type": "routine", "doctor": "Dr. Williams"}', 10),
(3, 'health_metric_logged', '2025-07-28', '{"metric": "weight", "value": "59.2kg"}', 5),
(4, 'medication_compliance', '2025-07-27', '{"streak_days": 30, "medication": "prenatal_vitamins"}', 15);

-- Sample chat messages for AI training context
INSERT INTO chat_messages (user_id, message, response, timestamp) VALUES
(1, 'I have been feeling nauseous in the mornings. Is this normal?', 'Morning sickness is very common during the first trimester of pregnancy. It affects about 70-80% of pregnant women. Here are some tips to help manage it: 1) Eat small, frequent meals 2) Keep crackers by your bedside 3) Try ginger tea 4) Avoid strong smells. If symptoms are severe or you cannot keep food down, please contact your healthcare provider.', '2025-07-25 09:15:00'),
(1, 'What foods should I avoid during pregnancy?', 'During pregnancy, you should avoid: 1) Raw or undercooked meats and eggs 2) High-mercury fish like shark, swordfish 3) Unpasteurized dairy products 4) Raw sprouts 5) Alcohol 6) High-caffeine beverages (limit to 200mg/day) 7) Processed deli meats unless heated. Focus on eating plenty of fruits, vegetables, lean proteins, and whole grains.', '2025-07-26 14:30:00'),
(2, 'When should I start feeling baby movements?', 'First-time mothers typically feel baby movements (called quickening) between 18-25 weeks of pregnancy. Women who have been pregnant before may feel movements as early as 16 weeks. Initially, movements feel like flutters or bubbles. As your baby grows, movements become more pronounced. If you are past 25 weeks and have not felt movement, or if you notice a decrease in usual movement patterns, contact your healthcare provider.', '2025-07-27 11:45:00'),
(3, 'I am having trouble sleeping. Any suggestions?', 'Sleep difficulties are common during pregnancy. Here are some tips: 1) Use a pregnancy pillow for support 2) Sleep on your left side to improve blood flow 3) Establish a bedtime routine 4) Avoid screens before bed 5) Try relaxation techniques 6) Keep your bedroom cool and dark 7) Avoid large meals before bedtime. If sleep problems persist, discuss with your doctor as they may recommend safe sleep aids.', '2025-07-28 22:00:00');

-- Additional sample pregnancy tips with more detailed content
INSERT INTO pregnancy_tips (week_number, category, title, content) VALUES
(5, 'health', 'Understanding Early Pregnancy Symptoms', 'At 5 weeks, you may experience breast tenderness, fatigue, and morning sickness. These symptoms are caused by hormonal changes and are completely normal.'),
(6, 'nutrition', 'Hydration is Key', 'Drink at least 8-10 glasses of water daily. Proper hydration helps prevent constipation, reduces swelling, and supports increased blood volume.'),
(10, 'mental_health', 'Managing Pregnancy Anxiety', 'It is normal to feel anxious about pregnancy. Practice deep breathing, join support groups, and communicate with your partner about your feelings.'),
(14, 'exercise', 'Safe Second Trimester Exercises', 'The second trimester is often the best time for exercise. Continue with walking, swimming, and prenatal yoga while avoiding contact sports.'),
(18, 'preparation', 'Choosing a Pediatrician', 'Start researching pediatricians now. Schedule interviews and visits to find someone you trust for your baby\'s care.'),
(22, 'health', 'Anatomy Scan Preparation', 'Your detailed ultrasound will check your baby\'s organs and development. This is also when you can find out the baby\'s sex if desired.'),
(26, 'nutrition', 'Iron-Rich Foods for Energy', 'Combat fatigue with iron-rich foods like lean red meat, spinach, beans, and fortified cereals. Pair with vitamin C for better absorption.'),
(30, 'preparation', 'Baby Shower Planning', 'If you are having a baby shower, now is a good time to start planning. Create your registry and think about the items you will need.'),
(34, 'health', 'Recognizing Preterm Labor Signs', 'Learn the signs of preterm labor: regular contractions, pelvic pressure, back pain, and fluid leakage. Contact your doctor immediately if experienced.'),
(38, 'preparation', 'Finalizing Birth Plan', 'Discuss your birth preferences with your healthcare provider. Consider pain management options, who you want present, and postpartum wishes.');

-- Create indexes for better performance
CREATE INDEX idx_health_metrics_user_date ON health_metrics(user_id, recorded_at);
CREATE INDEX idx_medications_user_active ON medications(user_id, is_active);
CREATE INDEX idx_appointments_user_date ON appointments(user_id, appointment_date);
CREATE INDEX idx_chat_messages_user_timestamp ON chat_messages(user_id, timestamp);
CREATE INDEX idx_wearable_data_user_type_date ON wearable_data(user_id, data_type, recorded_at);
CREATE INDEX idx_user_progress_user_milestone ON user_progress(user_id, milestone_type, milestone_date);
CREATE INDEX idx_mindfulness_user_date ON mindfulness_sessions(user_id, completed_at);
CREATE INDEX idx_telehealth_user_date ON telehealth_consultations(user_id, consultation_date);
