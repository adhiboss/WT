# 🟢 ProcrastiTrack

**ProcrastiTrack** is a professional, gamified task management dashboard designed to help students and professionals conquer procrastination. Built with a robust PHP/MySQL backend and a modern "Cyber-Emerald" dark-mode aesthetic, it seamlessly unifies daily to-dos, academic assignments, and dynamic motivation into a single web application.

## ✨ Features

- **Cyber-Emerald Interface**: A sleek, obsidian dark-mode interface accented with vibrant emerald green for a premium, distraction-free environment.
- **Gamified Task Management**: Add, organize, complete, and delete tasks intuitively using a satisfying progression-focused system.
- **Google Classroom Integration**: Securely import and synchronize your academic assignments directly from Google Classroom via OAuth.
- **Motivational Meme Engine**: Replaces traditional competitive leaderboards with AI-generated motivational and programming-related memes to foster positive productivity.
- **Robust Backend Architecture**: Fully operational PHP API endpoints paired with a MySQL database for seamless data persistence and integrity.
- **Secure Authentication**: Built-in user registration, login routing, and session management system.

## 🛠️ Technology Stack

- **Frontend**: HTML5, Vanilla CSS3 (Custom Cyber-Emerald Design), JavaScript (Dynamic logic & API fetching)
- **Backend**: Native PHP (API endpoints and routing)
- **Database**: MySQL (Relational data architecture via XAMPP)
- **Integrations**: Google Classroom API (OAuth 2.0)

## 🚀 Getting Started

### Prerequisites
- [XAMPP](https://www.apachefriends.org/index.html) or a similar local server environment with PHP and MySQL natively supported.
- *Optional:* An active Google Cloud Console project configured with OAuth Client ID credentials (only for Google Classroom Sync).

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/adhiboss/Procrasti_Track.git
   ```
2. **Setup the Local Database**
   - Start the **Apache** and **MySQL** modules via your XAMPP Control Panel.
   - Open phpMyAdmin (`http://localhost/phpmyadmin`).
   - Create a new database (e.g., `procrastitrack_db` - check your `db.php` file for the exact name).
   - Import the provided `database.sql` file to instantly set up your tables (Users, Tasks, etc.).
3. **Deploy Locally**
   - Ensure the cloned `Procrasti_Track` repository is placed inside your server's public root directory (e.g., the `htdocs` folder in XAMPP `C:\xampp\htdocs\`).
4. **Configuration**
   - Open `db.php` and verify your local MySQL credentials (`root` and empty password are the XAMPP defaults).
5. **Launch!**
   - Open an internet browser and navigate to `http://localhost/Procrasti_Track/index.html` to begin!

## 🧠 The Philosophy
Traditional task managers feel like work. Academic platforms feel like chores. ProcrastiTrack flips the script—designed to look and feel like a high-end, personal AI productivity assistant. It blends serious academic needs with engaging, humor-driven motivation to ensure you actually *want* to finish your work.

---
**Developed by Adithya Gowda**
