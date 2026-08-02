# Development Rules & Coding Standards

## Project: LENTERA ILMU - SD Negeri 02 Maron

---

## 1. General Engineering Principles

- **Single Responsibility Principle**: Ensure controllers remain thin by delegating complex business logic (e.g. stock calculation, late day calculation) to Service classes or Eloquent Model methods.
- **Never Hardcode Schema or Configuration**: Store environment parameters (Google Maps link, school info, logos) in database `settings` table or `.env`.
- **Strict Typing & Validation**: All incoming requests MUST be validated using Laravel Form Requests or `$request->validate()` before processing.
- **Transactional Integrity**: All multi-step database mutations (such as borrowing creation + book stock deduction, or return creation + status update + stock increment) MUST be wrapped inside `DB::transaction()`.

---

## 2. Laravel Backend Guidelines (Laravel 13)

- **Controllers**:
  - Return Inertia responses: `Inertia::render('Books/Index', [ 'books' => $books ])`.
  - Use Eloquent Resource or clean array transformations to prevent leaking unintended model attributes.
- **Models & Relationships**:
  - Always define explicit `$fillable` array on Eloquent models.
  - Define type casting for dates and numbers (`protected $casts = ['borrow_date' => 'date', ...]`).
  - Use clear relationship method names (`category()`, `borrowings()`, `returns()`, `member()`, `book()`).
- **Database & Migrations**:
  - Write descriptive migration file names and ensure `down()` methods correctly rollback changes.
  - Add foreign key constraints with `cascadeOnDelete()` or `restrictOnDelete()` based on data retention rules.

---

## 3. Vue 3 & Inertia.js Frontend Guidelines

- **Composition API `<script setup>`**:
  - Use Vue 3 `<script setup>` syntax across all Vue components.
  - Declare explicit props using `defineProps()` and emits with `defineEmits()`.
- **Inertia Form Handling**:
  - Use `useForm()` helper from `@inertiajs/vue3` for form submissions to automatically get loading states, validation error handling, and reset capabilities.
- **Reusability**:
  - Keep UI components inside `resources/js/Components/` modular and pure.
  - Avoid duplicate styling; use Tailwind utility patterns or wrapper components.

---

## 4. UI & Tailwind CSS Standards

- **Color Palette Consistency**:
  - Use defined colors: Primary Blue (`blue-600`), Emerald (`emerald-500`), Amber (`amber-500`), Rose (`rose-500`).
- **Responsive Layout**:
  - Design for mobile-first or desktop-first with proper breakpoints (`sm:`, `md:`, `lg:`, `xl:`).
  - All data tables MUST be horizontally scrollable or responsive on smaller screens.
- **Typography & Formatting**:
  - Use `font-poppins` as the global default font.
  - Apply clean formatting for dates (e.g. `DD MMMM YYYY` format in Indonesian locale).

---

## 5. Security & Error Handling

- **Role Authorization**:
  - Protect routes with middleware (`auth`, `role:Admin,Pustakawan`).
  - Never trust client-side role checks alone; always verify permissions server-side.
- **Flash Messages**:
  - Share flash messages (`success`, `error`, `warning`) via Inertia middleware to display toast notifications upon actions.
- **File Storage**:
  - Only allow image uploads (`jpg`, `png`, `webp`, `jpeg`) for book covers, gallery photos, and settings logo.
  - Store uploaded files using `Storage::disk('public')`.
