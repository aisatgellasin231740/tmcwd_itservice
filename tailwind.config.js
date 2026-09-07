import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50:  '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
            },
        },
    },

    plugins: [forms],
    safelist: [
        // Status badge classes
        'bg-blue-100', 'text-blue-800',
        'bg-yellow-100', 'text-yellow-800',
        'bg-gray-100', 'text-gray-700',
        'bg-green-100', 'text-green-800',
        'bg-gray-300', 'text-gray-600',
        // Priority badge classes
        'bg-blue-50', 'text-blue-600',
        'bg-yellow-50', 'text-yellow-600',
        'bg-orange-100', 'text-orange-700',
        'bg-red-100', 'text-red-700',
        // SLA indicator classes
        'bg-green-50', 'text-green-700',
        'bg-red-50', 'text-red-600',
        'border-green-200', 'border-yellow-200', 'border-red-200',
    ],
};
