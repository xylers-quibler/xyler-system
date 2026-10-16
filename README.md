# Xyler School System

A school management system in PHP and MySQL, made as a school project.

## Who uses it

| Role | What they do |
|---|---|
| Admin | Approves new accounts, manages users |
| Dean | Subjects, prerequisites, enrollment |
| Teacher | Enters grades |
| Student | Sees subjects, grades and balance |
| Records | Student records and transcripts |
| Finance | Tuition and fees |
| Cashier | Takes payments |

## Run it on XAMPP

1. Copy this folder into `C:\xampp\htdocs\` (for example `C:\xampp\htdocs\xyler-system`).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. In phpMyAdmin, create a database called `school_system` and import `school_system.sql`.
4. Run the files in `migrations/` in this order (phpMyAdmin › SQL):
   `add_grade_4th.sql`, `add_tuition_to_courses.sql`, `alter_prerequisite_to_text.sql`,
   then `create_sample_transcript.sql` if you want sample data (`cleanup_sample_transcript.sql` removes it).
5. Open <http://localhost/xyler-system/>.

The default admin login is **admin / admin123**. Change it after the first sign-in.
Database settings (user `root`, no password) are in `config/database.php`.

## Folders

```
index.php            sends each role to its dashboard
pages/               login, register, profile and one dashboard per role
includes/            sign-in check, header, footer, helpers
config/database.php  database connection (also creates missing tables)
assets/              styles and script
migrations/          database changes made after school_system.sql
uploads/             profile pictures (not saved in git)
```
