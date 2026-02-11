# Software Requirements Specification (SRS) - Paw Pal

## 1. Introduction

The purpose of this document is to outline the functional and non-functional requirements for the Paw Pal platform.

## 2. Project Scope

Paw Pal is a centralized platform for animal welfare, focusing on Pet Adoption, Emergency Rescue, and Community Networking. It connects animal lovers, NGOs, veterinarians, and volunteers.

## 3. User Roles

- **Guest**: Can browse pets, read blogs, and view verified partners.
- **Registered User**: Can submit adoption applications, report rescues, and interact with the community.
- **Volunteer**: Can view assigned rescue tasks and update task status.
- **Rescuer**: Similar to volunteers but often specialized in handling emergency response.
- **Admin**: Full control over the system, including user management, pet listings, application review, and rescue coordination.

## 4. Functional Requirements

### 4.1. Account Management

- **FR1**: Users shall be able to register and log in.
- **FR2**: Profile management for users (contact info, address).
- **FR3**: Role-based access control (RBAC) to restrict dashboard access.

### 4.2. Pet Adoption & Rehoming

- **FR4**: Users shall be able to browse available pets with filters (type, breed, age).
- **FR5**: Users shall be able to submit adoption applications with custom messages.
- **FR6**: Admins shall be able to add, edit, or remove pet listings.
- **FR7**: Admins shall be able to approve or reject adoption applications.

### 4.3. Rescue Module

- **FR8**: Users shall be able to report a rescue by providing location, animal type, urgency, and photos.
- **FR9**: Rescuers/Volunteers shall be notified of new reports (simulated via dashboard view).
- **FR10**: Admins shall be able to assign rescue tasks to specific volunteers/rescuers.
- **FR11**: Volunteers shall be able to update the status of assigned tasks (In Progress, Completed).

### 4.4. Community & Content

- **FR12**: Admins shall be able to publish blog posts and success stories.
- **FR13**: Users shall be able to read blog posts.

### 4.5. Verified Partners

- **FR14**: The platform shall display a list of verified partners (breeders, shelters, vets).

## 5. Non-Functional Requirements

### 5.1. Performance

- **NFR1**: The website should load within 2 seconds on standard broadband connections.
- **NFR2**: Animations (GSAP) should maintain 60fps for a smooth user experience.

### 5.2. Security

- **NFR3**: Password hashing for user security (currently implemented as plain text in dev, needs bcrypt for production).
- **NFR4**: SQL injection prevention using prepared statements (mysqli).

### 5.3. Usability

- **NFR5**: Mobile-first responsive design.
- **NFR6**: Intuitive navigation with clear call-to-action (CTA) buttons.

### 5.4. Reliability

- **NFR7**: Systematic database backups (indicated by the `backup` folder in the project structure).

---

_Last Updated: February 2, 2026_
