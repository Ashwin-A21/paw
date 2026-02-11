# Paw Pal Project: Complete Documentation Package

---

## 1. Project Overview

**Paw Pal** is a comprehensive web-based platform designed to revolutionize pet welfare through adoption, rescue, and community engagement. It bridges the gap between street animals in need and compassionate individuals by providing structured tools for management and response.

### Core Features

- **Real-time Rescue Reporting**: SOS alerts with image uploads and geo-tagging.
- **Verified Adoption Pipeline**: A structured process for pet adoption and rehoming.
- **Role-based Dashboards**: Customized interfaces for Admins, Users, and Volunteers/Rescuers.
- **Modern Aesthetic**: A premium, responsive design built with Tailwind CSS and GSAP animations.

---

## 2. Software Requirements Specification (SRS)

### 2.1. Introduction

The purpose of this document is to outline the functional and non-functional requirements for the Paw Pal platform.

### 2.2. User Roles

- **Guest**: Can browse pets, read blogs, and view verified partners.
- **Registered User**: Can submit adoption applications, report rescues, and interact with the community.
- **Volunteer**: Can view assigned rescue tasks and update task status.
- **Rescuer**: Specialized in handling emergency response.
- **Admin**: Full control over user management, pet listings, and rescue coordination.

### 2.3. Functional Requirements

- **Account Management**: User registration, login, and RBAC (Role-Based Access Control).
- **Pet Adoption**: Filtered pet browsing and application submission.
- **Rescue Module**: SOS reporting with location, urgency, and image data. Tracking of rescue status from "Reported" to "Rescued".
- **Task Management**: Admin assignment of tasks to specific volunteers with priority levels.
- **Content Management**: Blog publishing and success story sharing.

### 2.4. Non-Functional Requirements

- **Performance**: Standard load times under 2 seconds.
- **Security**: SQL injection prevention and session management.
- **Usability**: Mobile-first responsive design with intuitive navigation.

---

## 3. Design & Architecture Specifications

### 3.1. Technology Stack

- **Frontend**: HTML5, Tailwind CSS, GSAP (Animations), Lucide Icons.
- **Backend**: PHP.
- **Database**: MySQL.
- **Logic**: Vanilla JavaScript for magnetic effects and parallax.

### 3.2. UI/UX Design System

- **Color Palette**:
  - Paw BG: `#F9F8F6`
  - Paw Dark: `#2D2825`
  - Paw Accent: `#D4A373`
  - Paw Alert: `#E07A5F`
- **Typography**: `Cormorant Garamond` (Serif) and `Plus Jakarta Sans` (Sans-serif).
- **Visuals**: Glassmorphism (`backdrop-filter`), Magnetic Hover Effects, and GSAP-driven ScrollTriggers.

---

## 4. Module Documentation

### 4.1. Root Modules

- `index.php`: Main landing page with platform statistics.
- `adopt.php`: Pet gallery with category filtering.
- `rescue.php`: Dispatch interface for reporting emergencies.
- `blogs.php`: Community knowledge base.

### 4.2. Administrative Module (`/admin`)

- `admin/index.php`: High-level command stats.
- `admin/pets.php`: CRUD operations for pet inventory.
- `admin/applications.php`: Workflow for approving/rejecting adoptions.
- `admin/rescues.php`: Coordinator view for assigning rescue teams.

### 4.3. Volunteer/Rescuer Module (`/volunteer`)

- `volunteer/index.php`: Personal task board and active rescue feeds.
- `volunteer/tasks.php`: Task detailing and progress updating.

---

## 5. Database Schema

### 5.1. Key Tables

- **`users`**: Stores credentials, contact info, and role (`admin`, `user`, `volunteer`, `rescuer`).
- **`pets`**: Inventory of animals with attributes (type, breed, age, gender, status).
- **`adoption_applications`**: Junction table linking users to pets with application status.
- **`rescue_reports`**: Logs for animal emergencies including urgency and geolocation.
- **`tasks`**: Discrete actions assigned to volunteers linked to rescue reports.

### 5.2. Relationships

- **Admin -> Pets**: 1 to Many.
- **User -> Applications**: 1 to Many.
- **Rescue Report -> Tasks**: 1 to Many.

---

## 6. User Guide & Glossary

### 6.1. Getting Started

- **Adoption**: Browse the gallery, log in, and click "Adopt" to start the process.
- **Rescue**: Use the SOS reporting tool. Ensure photos are clear and location description is accurate.
- **volunteering**: Check your dashboard daily for "Assigned" or "In Progress" tasks.

### 6.2. Glossary

- **SOS Alert**: A critical rescue report requiring immediate field response.
- **Verified Partner**: Pre-vetted NGO or Vet featured on the platform.
- **RBAC**: Security method ensuring volunteers cannot access admin-only data.

---

## 7. Changelog

### [Current Version] - 2026-02-02

- **Added**: Consolidated documentation package.
- **Refined**: UI responsiveness and GSAP animation timings.
- **Optimized**: Redirection logic for role-based dashboards.

---

_Created by Antigravity AI - February 2, 2026_
