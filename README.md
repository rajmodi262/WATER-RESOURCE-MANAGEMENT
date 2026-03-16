<div align="center">

<br>

# 🌊 Water Resource Management System
### *Integrated River Management System (IRMS) for India*

<br>

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Leaflet.js](https://img.shields.io/badge/Leaflet.js-Interactive%20Map-199900?style=for-the-badge&logo=leaflet&logoColor=white)](https://leafletjs.com)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![CSS3](https://img.shields.io/badge/CSS3-Styled-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![License](https://img.shields.io/badge/License-MIT-brightgreen?style=for-the-badge)](LICENSE)

<br>

> A full-stack web application for **monitoring, managing, and visualizing**  
> India's river systems, disaster history, infrastructure, and conservation projects —  
> all in one unified platform.

<br>

</div>

---

## 📌 Table of Contents

- [Overview](#-overview)
- [Live Pages](#-live-pages)
- [Features](#-features)
- [Interactive Map](#-interactive-map)
- [CRUD Panel](#-crud-panel)
- [Database Schema](#-database-schema)
- [Project Structure](#-project-structure)
- [Installation & Setup](#-installation--setup)
- [Technologies Used](#-technologies-used)
- [Future Improvements](#-future-improvements)

---

## 🧠 Overview

The **Water Resource Management System (WRMS)** is an integrated platform designed to centralize data collection, monitoring, and response mechanisms for India's extensive river systems.

It tackles real-world challenges including **floods, droughts, water pollution, and infrastructure monitoring** through a unified web interface — combining an interactive map, full CRUD management panel, and rich content sections about India's river ecosystem.

---

## 🖥️ Live Pages

| Page | File | Description |
|------|------|-------------|
| 🏠 **Home** | `index.php` | Hero video, About, Objectives, Technology, Stats, Contact |
| 🗺️ **Interactive Map** | `map.php` | Leaflet.js map with 100+ markers across India |
| ⚙️ **Manage Data** | `crud_operations.php` | Full CRUD panel for all database tables |

---

## ✨ Features

- 🎬 **Video Hero Section** — Full-screen background video with CTA button
- 🗺️ **Interactive Map** — Leaflet.js map centered on India with clustered markers
- 🔍 **Smart Search** — Autocomplete search bar with OpenStreetMap geocoding
- 🗂️ **Filter Controls** — Toggle visibility of 6 marker categories in real-time
- 🛠️ **CRUD Panel** — Create, Read, Update, Delete records for 7 database tables
- 🏗️ **Dynamic Forms** — Form fields change automatically based on selected table
- 📊 **Data Tables** — Fetched records displayed as responsive styled tables
- 📱 **Responsive Design** — Sidebar navigation + mobile-friendly layout
- 🔒 **Prepared Statements** — All SQL uses `mysqli` prepared statements to prevent injection
- 🚀 **AJAX Submissions** — Forms submit via `fetch()` API with JSON responses

---

## 🗺️ Interactive Map

The map (`map.php`) uses **Leaflet.js** with **MarkerCluster** for performance and is restricted to India's bounding box.

### Map Layers (Filterable)

| Icon | Category | Examples |
|------|----------|---------|
| 🔵 **River** | Major rivers | Ganga, Indus, Narmada, Brahmaputra |
| 🟢 **Tourism Projects** | Eco/cultural tourism | Tehri Lake Eco-Tourism, Varanasi Ghats, Kumbh Mela prep |
| 🟡 **Cleaning Projects** | River cleaning | Plastic waste removal, sewage treatment, industrial cleanup |
| 🔴 **Dams** | Dams & barrages | Tehri Dam, Sardar Sarovar, Tarbela, Farakka Barrage |
| 🟣 **River Dev Projects** | Development initiatives | Clean Ganga Project, flood control walls, riverfront beautification |
| ⚪ **Weather Stations** | Monitoring stations | Flood/monsoon/cyclone monitoring across states |

### Map Capabilities

```
✅ Marker clustering for performance
✅ Custom icons per category
✅ Popup info on click (name, capacity, budget, status)
✅ Autocomplete search for markers + OSM geocoding
✅ Real-time layer filtering via checkboxes
✅ Zoom + pan restricted to India's geographic bounds
```

---

## ⚙️ CRUD Panel

The management panel (`crud_operations.php`) supports **Create, Read, Update, Delete** on 7 tables with fully dynamic form generation.

### Supported Tables

| Table | Primary Key | Key Fields |
|-------|-------------|-----------|
| `disaster` | `Disaster_ID` | Disaster Type, Year, Cause, Severity, River ID, Affected Area |
| `disaster_history` | `Event_ID` | Date, Damage Estimate, Casualties, Disaster ID, River ID |
| `affected_area` | `Area_ID` | Name, Type, Disaster ID |
| `river_development_project` | `Project_ID` | Project Name, River ID, City ID, Purpose, Status, Budget |
| `cleaning_project` | `Cleaning_ID` | Cleaning Type, River ID, Village ID, Duration, Impact, Budget |
| `tourism_project` | `Project_ID` | Project Name, Budget, Dam ID, City ID, Project Type |
| `weather_station` | `Station_ID` | Station Name, Coordinates, Monitoring Capability, City ID |

### Operation Flow

```
User selects Operation (Create / Read / Update / Delete)
        ↓
User selects Table
        ↓
Dynamic form fields appear (specific to the selected table)
        ↓
Form submits via fetch() → AJAX POST to crud_operations.php
        ↓
PHP processes + MySQL prepared statement executes
        ↓
JSON response → Success/Error displayed in animated result section
```

---

## 🗄️ Database Schema

Database: `project_resource`

```
┌─────────────────┐     ┌──────────────────────┐     ┌───────────────────┐
│     river        │────▶│       disaster        │────▶│  disaster_history  │
│ River_ID (PK)   │     │ Disaster_ID (PK)      │     │ Event_ID (PK)     │
│ River_Name      │     │ Disaster_Type         │     │ Date              │
│ Length          │     │ Year                  │     │ Damage_Estimate   │
│ Source_Location │     │ Cause                 │     │ Casualties        │
│ End_Location    │     │ Severity              │     │ Disaster_ID (FK)  │
└─────────────────┘     │ River_ID (FK)         │     │ River_ID (FK)     │
                        │ Affected_Area         │     └───────────────────┘
                        └──────────────────────┘
        
┌──────────────────┐     ┌──────────────────┐     ┌──────────────────────┐
│  affected_area   │     │      dam         │     │  river_dev_project   │
│ Area_ID (PK)     │     │ Dam_ID (PK)      │     │ Project_ID (PK)      │
│ Name             │     │ Dam_Name         │     │ Project_Name         │
│ Type             │     │ Capacity         │     │ River_ID (FK)        │
│ Disaster_ID (FK) │     │ Year_Built       │     │ City_ID (FK)         │
└──────────────────┘     │ River_ID (FK)    │     │ Purpose              │
                         │ City_ID (FK)     │     │ Status               │
                         │ Risk_Level       │     │ Budget_Value         │
                         └──────────────────┘     └──────────────────────┘

┌──────────────────────┐     ┌──────────────────────┐     ┌───────────────────────┐
│   cleaning_project   │     │   tourism_project    │     │    weather_station    │
│ Cleaning_ID (PK)     │     │ Project_ID (PK)      │     │ Station_ID (PK)       │
│ Cleaning_Type        │     │ Project_Name         │     │ Station_Name          │
│ River_ID (FK)        │     │ Budget_Value         │     │ Coordinates           │
│ Village_ID (FK)      │     │ Dam_ID (FK)          │     │ Monitoring_Capability │
│ Duration             │     │ City_ID (FK)         │     │ City_ID (FK)          │
│ Impact               │     │ Project_Type         │     └───────────────────────┘
│ Budget_Value         │     └──────────────────────┘
└──────────────────────┘

Additional tables: state, city, village, tributary, irrigation_canal
```

---

## 📁 Project Structure

```
Water-Resource-Management-System/
│
├── 📄 index.php                     # Home page (hero, about, objectives, stats)
├── 📄 map.php                       # Interactive Leaflet.js map
├── 📄 crud_operations.php           # CRUD management UI + AJAX form logic
│
├── 📂 components/
│   ├── header.php                   # Sidebar navigation + hamburger menu
│   └── footer.php                   # Footer with contact link
│
├── 📂 config/
│   └── db.php                       # MySQLi database connection
│
├── 📂 api/
│   └── crud_handler.php             # JSON API handler for CRUD operations
│
├── 📂 classes/
│   ├── River.php                    # River OOP class (CRUD methods)
│   └── Disaster.php                 # Disaster OOP class (CRUD methods)
│
├── 📂 assets/
│   ├── 📂 css/
│   │   ├── styles.css               # Global styles (body, hero, sections, footer)
│   │   ├── crud.css                 # CRUD panel styles (forms, tables, buttons)
│   │   └── map.css                  # Map container & controls styles
│   ├── 📂 js/
│   │   └── api.js                   # Frontend API utilities
│   ├── 📂 images/                   # Background images for each section
│   ├── 📂 icons/                    # Custom map marker icons (6 categories)
│   └── 📂 videos/                   # Hero background video
│
└── 📄 README.md
```

---

## ⚙️ Installation & Setup

### Prerequisites
- PHP 8.0+
- MySQL / MariaDB
- Apache or Nginx (XAMPP / WAMP / LAMP recommended)
- A modern browser (Chrome, Firefox, Edge)

---

### Step 1 — Clone the Repository
```bash
git clone https://github.com/your-username/water-resource-management-system.git
cd water-resource-management-system
```

### Step 2 — Setup the Database
1. Open **phpMyAdmin** or MySQL CLI
2. Create a new database:
```sql
CREATE DATABASE project_resource;
```
3. Import the SQL schema file (if provided):
```bash
mysql -u root -p project_resource < database/schema.sql
```

### Step 3 — Configure Database Connection

Edit `config/db.php`:
```php
$servername = "localhost";
$username   = "root";       // your MySQL username
$password   = "";           // your MySQL password
$dbname     = "project_resource";
```

### Step 4 — Start the Server

**Using XAMPP:**
- Place the project folder in `htdocs/`
- Start Apache & MySQL from XAMPP Control Panel
- Visit: `http://localhost/water-resource-management-system/`

**Using PHP built-in server:**
```bash
php -S localhost:8000
```
Then open: `http://localhost:8000`

---

## 🛠️ Technologies Used

| Technology | Purpose |
|------------|---------|
| **PHP 8** | Server-side logic, CRUD operations, OOP classes |
| **MySQL / MySQLi** | Relational database with prepared statements |
| **HTML5 / CSS3** | Structure and styling with glassmorphism effects |
| **JavaScript (ES6)** | DOM manipulation, fetch API, dynamic form fields |
| **Leaflet.js** | Interactive map with markers and clustering |
| **Leaflet.MarkerCluster** | Performance clustering for 100+ markers |
| **Leaflet-GeoSearch** | OpenStreetMap autocomplete geocoding |
| **jQuery 3.6** | AJAX requests and DOM helpers |
| **Google Fonts** | Roboto, Lato, Montserrat, Open Sans, Poppins |

---

## 🌍 Content Sections (index.php)

| Section | Description |
|---------|-------------|
| 🎬 **Hero** | Full-screen video background with tagline and CTA |
| 📖 **About IRMS** | Overview of the Integrated River Management System |
| 🎯 **Objectives** | 5 key goals: disaster tracking, resource management, infrastructure |
| 💻 **Technology** | IoT sensors, ML flood prediction, satellite imagery |
| 📊 **Statistics** | 700M people supported, 20% of global flood deaths in India |
| ✅ **Conclusion** | System impact summary |
| 📬 **Contact** | Email and phone contact details |

---

## 🔮 Future Improvements

- [ ] Add **user authentication** (admin login for CRUD access)
- [ ] Integrate **real-time IoT sensor data** via REST API
- [ ] Add **machine learning flood prediction** dashboard
- [ ] Build **reporting module** with PDF export
- [ ] Add **map heatmap layer** for disaster frequency
- [ ] Deploy on cloud (AWS / DigitalOcean / Heroku)
- [ ] Add **multilingual support** (Hindi, regional languages)
- [ ] Mobile app companion using React Native

---

## 🤝 Contributing

Contributions are welcome! For major changes, please open an issue first.

1. Fork the project
2. Create your feature branch: `git checkout -b feature/NewFeature`
3. Commit: `git commit -m 'Add NewFeature'`
4. Push: `git push origin feature/NewFeature`
5. Open a Pull Request

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

## 🙏 Acknowledgements

- **Leaflet.js** — Open-source interactive maps
- **OpenStreetMap** — Free geographic data and geocoding
- **World Bank** — Water resource statistics & reports
- **National Ganga River Basin Authority (NGRBA)** — Policy reference
- **BHUVAN / ISRO** — India satellite imagery reference

---

<div align="center">

Made with 💙 for India's River Ecosystems

⭐ *Star this repo if you found it useful!*

📧 info@irms.com &nbsp;|&nbsp; 📞 +91 9876543210

</div>
