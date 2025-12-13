-- Hotel Reservation System Database (SQLite3)
-- Based on Project Documentation ERD with DATETIME for check-in/out

-- Table: admin
CREATE TABLE IF NOT EXISTS admin (
    admin_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT (datetime('now', 'localtime'))
);

-- Table: guest (User accounts)
CREATE TABLE IF NOT EXISTS guest (
    guest_id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    phone TEXT NOT NULL,
    address TEXT,
    password TEXT NOT NULL,
    created_date DATETIME DEFAULT (datetime('now', 'localtime'))
);

-- Table: room_type
CREATE TABLE IF NOT EXISTS room_type (
    room_type_id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_name TEXT NOT NULL,
    nightly_rate REAL NOT NULL,
    max_guests INTEGER NOT NULL,
    description TEXT,
    thumbnail TEXT,
    image1 TEXT,
    image2 TEXT,
    image3 TEXT,
    image4 TEXT,
    image5 TEXT
);

-- Table: room
CREATE TABLE IF NOT EXISTS room (
    room_id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_number TEXT NOT NULL UNIQUE,
    room_type_id INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT 'Available',
    floor TEXT NOT NULL,
    FOREIGN KEY (room_type_id) REFERENCES room_type(room_type_id) ON DELETE CASCADE
);

-- Table: reservation (CHANGED: check_in_date and check_out_date are now DATETIME)
CREATE TABLE IF NOT EXISTS reservation (
    reservation_id INTEGER PRIMARY KEY AUTOINCREMENT,
    guest_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    check_in_date DATETIME NOT NULL,
    check_out_date DATETIME NOT NULL,
    num_guests INTEGER NOT NULL,
    total_amount REAL NOT NULL,
    status TEXT NOT NULL DEFAULT 'Confirmed',
    booking_date DATETIME DEFAULT (datetime('now', 'localtime')),
    FOREIGN KEY (guest_id) REFERENCES guest(guest_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES room(room_id) ON DELETE CASCADE
);

-- Table: billing
CREATE TABLE IF NOT EXISTS billing (
    bill_id INTEGER PRIMARY KEY AUTOINCREMENT,
    reservation_id INTEGER NOT NULL,
    room_charge REAL NOT NULL,
    tax_amount REAL NOT NULL,
    total_amount REAL NOT NULL,
    payment_status TEXT NOT NULL DEFAULT 'Pending',
    bill_date DATETIME DEFAULT (datetime('now', 'localtime')),
    FOREIGN KEY (reservation_id) REFERENCES reservation(reservation_id) ON DELETE CASCADE
);

-- Insert default admin account
-- Username: admin, Password: admin123
INSERT OR IGNORE INTO admin (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample room types
INSERT OR IGNORE INTO room_type (type_name, nightly_rate, max_guests, description) VALUES
('Standard', 800.00, 2, 'Comfortable room with basic amenities'),
('Deluxe', 1500.00, 2, 'Spacious room with premium features'),
('Family Suite', 2500.00, 4, 'Large suite perfect for families'),
('Presidential Suite', 5000.00, 4, 'Luxury suite with exclusive amenities');

-- Insert sample rooms
INSERT OR IGNORE INTO room (room_number, room_type_id, status, floor) VALUES
('101', 1, 'Available', '1'),
('102', 1, 'Available', '1'),
('103', 1, 'Available', '1'),
('201', 2, 'Available', '2'),
('202', 2, 'Available', '2'),
('301', 3, 'Available', '3'),
('401', 4, 'Available', '4');