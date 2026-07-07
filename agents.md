##  CRITICAL DATA SAFETY RULES 
We are operating on **PRODUCTION / REAL DATA**. Under no circumstances should you generate scripts, execute commands, or suggest code that performs bulk deletions or destructive operations without explicit, documented user consent.

## 🛑 DATABASE & SCHEMA WIPE PREVENTION
- NEVER run `migrate:fresh`, `db:wipe`, `migrate:reset`, or `migrate:refresh` unless explicitly instructed by the user, as these commands drop production tables and wipe critical data.
- NEVER overwrite, delete, or clean up SQLite database files (`database.sqlite`, etc.) directly from the filesystem.
- When making schema updates or changing database connection configurations, always preserve the existing database path or migrate the existing data safely without dropping target databases.