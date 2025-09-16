# Police DB Web Application

🚔 A fully functional Police Database Web Application with **real-time responsive forms**, **robust SQL integration**, and **role-based access control**.

## Overview
This project is a **Police Database Management Web Application** built with **PHP** and **MySQL**.  
- It was developed in 2020 as the final coursework project for the *Databases, Interfaces and Software Design Principles (DIS)* module at the University of Nottingham.
- Purpose: Demonstrate ability to design and implement a relational database-backed web application with full CRUD and role-based access.  

##
The **visual design was intentionally kept minimal**. The focus was on the underlying logic rather than styling, because the main goal was to:
- Provide **responsive workflows**: **pages react immediately to user input, revealing different sub-sections depending on context** (e.g. when adding an incident, whether a person or vehicle already exists or needs to be created). This was prioritised over visual polish.
- Ensure **robust database integration**: all entities are tightly linked with relational constraints.  
- **Minimise input errors**: mandatory fields, uniqueness checks, prevention of inconsistent records.  
- Deliver **advanced search and filtering**: multi-criteria queries to showcase indexing and optimisation.  
- Apply **role-based access control**: menus and actions differ for admins and standard users.  

<br>

## Demo Screenshot, Insert New Incident Record Page (Only visible to the Admin account)
<br>
<img width="776" height="636" alt="image" src="https://github.com/user-attachments/assets/c2493ad8-c8b1-4d41-8ee7-865432a24ecb" />
<br>


##

**Live demo:** [https://police-db-php-production.up.railway.app/](https://police-db-php-production.up.railway.app/)

**_(See Technical Manual for more details)_**

##
The system allows police officers to record and retrieve information on:
- People involved in traffic incidents  
- Vehicles and ownership records  
- Incidents and reports  
- Offences and applicable penalties  
- Fines imposed  

It also includes full account management with different permissions depending on user role (admin vs standard user).

<br>

## Features
- **Login & Session Management**: Username/password-based login.  
- **People Module**: Search, insert, edit, delete personal records.  
- **Vehicle Module**: Manage vehicles with ownership links to people (existing, new, or unknown owners).  
- **Incident/Report Module**: Record new incidents, edit or delete, and link to people/vehicles/offences.  
- **Offence Module**: Search and manage offence types and penalty limits.  
- **Fines Module**: Admins can assign fines to incidents.  
- **Account Management**: Admins can create, delete, and manage user accounts.  

<br>


## User Roles
- **Admin users**:  
  - Full access including managing accounts, offences, and fines.  
  - Can create or delete non-admin accounts.  
- **Standard users**:  
  - Limited access to record, search, and update incidents, people, and vehicles.  

👉 Initial demo credentials are provided in `accounts.txt` (admin and non-admin accounts).

<br>


## System Design
- **Database**: MySQL with 7 tables  
  - People, Vehicle, Ownership, Offence, Incident, Fines, Users  
  - Relationships enforced with foreign keys (cascade on update/delete)  
- **Frontend/Backend**: PHP (procedural style) with jQuery 3.5.1 and FontAwesome icons  
- **Architecture**: Each functional folder (people, vehicle, report, offence, fines, account) contains search/insert/edit/delete scripts  
- **Security**: Sessions for authentication, basic validation on inputs, simple access control by role  


👉 While the UI reflects the coursework period, the real strength lies in **relational schema design, input validation, and user access management**.

<br>


## Installation (local)
1. Clone repository and set up in a PHP-supported web server (Apache recommended).  
2. Import `sql/queries.txt` into MySQL to create schema and seed initial records.  
3. Configure `dbcon.php` with environment variables:  
   ```
   DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT
   ```  
4. Start server and navigate to `http://localhost/login.php`.  

<br>


## Deployment (Railway)
- Application deployed at [up.railway.app](https://police-db-php-production.up.railway.app/) using PHP + MySQL services.  
- Database credentials are injected as environment variables (`MYSQLHOST`, `MYSQLUSER`, etc.).  

<br>


## Documentation
- **User Manual**: Detailed usage guide with screenshots of all modules.  
- **Technical Manual**: System architecture, database schema, file organisation, and workflows.  



