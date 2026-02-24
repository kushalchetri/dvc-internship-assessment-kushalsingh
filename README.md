
**Author:** kushal singh  
**Contact:** kushalchetri121@gmail.com  
**Date:** February 2026  

Overview

This repository contains the completed technical assessment for the Web Development Internship. It includes three distinct projects demonstrating proficiency across HTML, CSS, JavaScript, and PHP/WordPress.

- `/question1` - Responsive Product Card Component
- `/question2` - WordPress Testimonials Plugin
- `/question3` - Weather Dashboard

---

Question 1: Product Card Component

**Path:** `/question1/index.html`

A mobile-first, responsive product card built with semantic HTML5 and Vanilla CSS.
- Uses CSS Flexbox for layout and custom properties (CSS variables) for easy theming.
- Includes a functional quantity selector with state management via Vanilla JavaScript.
- Implements an "Add to Cart" toast notification and robust fallback handling for broken images.

**Usage:** Open `index.html` in any web browser.

Question 2: WordPress Testimonials Plugin

**Path:** `/question2/testimonials-plugin.php`

A lightweight, object-oriented WordPress plugin managing a custom post type for testimonials.
- Registers a `testimonial` Custom Post Type with built-in UI support.
- Implements robust Meta Boxes for client details using WordPress security standards (`wp_nonce_field`, generic sanitization).
- Provides a `[testimonials count="5"]` shortcode to render a fully responsive, vanilla JS-powered carousel without heavy external libraries.

**Usage:** Upload inside `wp-content/plugins/`, activate within WordPress, add testimonials, and use the shortcode on any page.


Question 3: Weather Dashboard

**Path:** `/question3/weather-dashboard.html`

A single-page weather application fetching data from the OpenWeatherMap API.
- Implements concurrent data fetching (`Promise.all()`) for both current conditions and a 5-day forecast.
- Utilizes CSS Grid for the layout and includes comprehensive loading and error-handling states.
- Persists the last searched city locally using `localStorage`.

**Usage:** Open the file, ensure a valid `API_KEY` is set in the `<script>` block, and open in any browser.

---

Technical Considerations

Cross-Browser Compatibility: Standard layouts tailored for modern browsers.
Performance: Minimized DOM writes and concurrent fetch requests.
Estimated Time Spent: ~7 Hours
