# Copilot / AI Assistant Guidelines & Instructions

## Project Context
You are working on **LENTERA ILMU**, a modern web application for SD Negeri 02 Maron's library service built with **Laravel 13**, **Vue 3 (Composition API)**, **Inertia.js**, **Tailwind CSS**, and **MySQL**.

---

## Technical Stack Quick Reference

- **Backend Framework**: Laravel 13, PHP 8.3
- **Frontend Stack**: Vue 3 (`<script setup>`), Inertia.js (`@inertiajs/vue3`), Tailwind CSS v4
- **Libraries**:
  - `barryvdh/laravel-dompdf` (PDF Generation)
  - `maatwebsite/excel` (Excel Import/Export)
  - `chart.js` & `vue-chartjs` (Dashboard Charts)
  - `lucide-vue-next` (Lucide Icons)

---

## Coding Rules & Conventions for Copilot

1. **Always Use Vue 3 Composition API**:
   - Write all SFC Vue components using `<script setup>`.
   - Import Inertia components (`Link`, `useForm`, `router`, `usePage`) from `@inertiajs/vue3`.
   
2. **Laravel Controller Pattern**:
   - Return Inertia responses using `Inertia::render('Folder/Page', [ 'data' => $data ])`.
   - Wrap transactional operations in `DB::transaction(function() { ... })`.

3. **Routing**:
   - Use Ziggy route helper (`route('books.index')`) in Vue templates and scripts.

4. **Styling Standards**:
   - Use Tailwind CSS utility classes.
   - Design with rounded cards (`rounded-2xl`), soft shadows (`shadow-sm`), and Poppins typography.
   - Primary colors: Blue (`blue-600`), Emerald (`emerald-500`), Amber (`amber-500`), Rose (`rose-500`).

5. **Language & Locale**:
   - All user-facing UI labels, notifications, tables, and error messages MUST be in **Indonesian (Bahasa Indonesia)**.
   - Database column names and variables remain in English (`title`, `author`, `stock`, `borrow_date`, `due_date`, `return_date`).

6. **File Referencing**:
   - Always refer to documentation files (`prd.md`, `architecture.md`, `schema.md`, `design.md`, `rules.md`, `features.md`, `roadmap.md`) when creating new modules to maintain absolute consistency.
