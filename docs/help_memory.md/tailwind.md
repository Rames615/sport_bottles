# Tailwind CSS in Sports Bottles

This document explains how [Tailwind CSS](https://tailwindcss.com/) is integrated and used in the Sports Bottles project.

## What is Tailwind CSS?

Tailwind CSS is a utility-first CSS framework that provides low-level utility classes to build custom designs directly in your HTML. Instead of writing custom CSS, you compose a design by applying pre-existing classes like `flex`, `pt-4`, `text-center`, etc.

This approach allows for rapid UI development, ensures consistency, and avoids the common issues of CSS at scale, like specificity wars and growing stylesheet sizes.

## Integration in this Project

This project uses **Symfony UX** with the `symfony/webpack-encore-bundle` and `symfony/stimulus-bundle` to manage front-end assets, including Tailwind CSS.

The main configuration files are:

1.  **`tailwind.config.js`**: This is the standard Tailwind configuration file. It's where we define:
    *   `content`: Paths to all template files (`.html.twig`) and JavaScript files that contain Tailwind class names. This is crucial for Tailwind's Just-in-Time (JIT) compiler to know which classes to generate.
    *   `theme`: Customizations to the default Tailwind theme (colors, fonts, breakpoints, etc.).
    *   `plugins`: Any additional Tailwind plugins.

2.  **`assets/styles/app.css`**: This is the main CSS entry point. It uses the `@tailwind` directives to inject Tailwind's `base`, `components`, and `utilities` layers. Any custom CSS or `@layer` customizations are also added here.

3.  **`webpack.config.js`**: This file, managed by Webpack Encore, is configured to process the CSS with PostCSS and the Tailwind CSS plugin. It handles the compilation of `assets/styles/app.css` into a final CSS file that is included in the browser.

## Build Process

When you run the asset build command (e.g., `npm run dev` or `npm run build`), the following happens:

1.  Webpack Encore starts the build process.
2.  The PostCSS loader processes `assets/styles/app.css`.
3.  The Tailwind CSS plugin scans all the files specified in `tailwind.config.js`'s `content` array.
4.  It generates only the CSS for the utility classes it finds in your files. This is the "Just-in-Time" (JIT) engine, which keeps the final CSS file size extremely small.
5.  The resulting CSS is saved to the `public/build/` directory.

## Customization

### Utility Classes
The primary way to style elements is by adding utility classes directly in the Twig templates.

**Example from `nav.html.twig`:**
```html
<button class="ml-auto md:hidden flex items-center justify-center w-9 h-9 rounded-lg border ...">
    ...
</button>
```
*   `md:hidden`: The button is hidden on medium screens and larger (`md` breakpoint and up). It is visible on screens smaller than `md`.
*   `flex`, `items-center`, `justify-center`: These are flexbox utilities to center the icon inside the button.
*   `w-9`, `h-9`: Sets a fixed width and height.
*   `rounded-lg`: Applies a large border-radius.

### Custom CSS and `@layer`
For more complex or reusable components, we can add custom CSS to `assets/styles/app.css` using the `@layer` directive. This allows us to create our own custom utility classes while still benefiting from Tailwind's features like variants (e.g., `hover:`, `focus:`).

**Example from `assets/styles/app.css`:**
```css
@layer components {
  .no-products-message {
    @apply col-span-full p-12 text-center bg-blue-50 border border-blue-200 rounded-md text-blue-700;
  }
}
```
Here, we've created a `.no-products-message` component class that is composed of several Tailwind utility classes using the `@apply` directive. This keeps our HTML cleaner for repeated components.
