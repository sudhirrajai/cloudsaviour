import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                display: ['Outfit', ...defaultTheme.fontFamily.sans],
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                canvas: 'var(--color-canvas)',
                surface: {
                    DEFAULT: 'var(--color-surface)',
                    elevated: 'var(--color-surface-elevated)',
                    bright: 'var(--color-surface-bright)',
                },
                primary: {
                    DEFAULT: 'var(--color-primary)',
                    dim: 'var(--color-primary-dim)',
                },
                secondary: {
                    DEFAULT: 'var(--color-secondary)',
                },
                tertiary: {
                    DEFAULT: 'var(--color-tertiary)',
                },
                error: {
                    DEFAULT: 'var(--color-error)',
                },
                warning: {
                    DEFAULT: 'var(--color-warning)',
                },
                success: {
                    DEFAULT: 'var(--color-success)',
                },
                border: {
                    ghost: 'var(--color-border-ghost)',
                    active: 'var(--color-border-active)',
                },
                content: {
                    DEFAULT: 'var(--color-content)',
                    variant: 'var(--color-content-variant)',
                }
            },
            boxShadow: {
                'ambient': '0 20px 50px rgba(0, 0, 0, 0.5)',
                'glow': '0 0 15px rgba(59, 130, 246, 0.08)',
                'glass': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
            },
            spacing: {
                '2': '0.4rem',
                '3': '0.6rem',
                '10': '2.25rem',
                '20': '4.5rem',
                '24': '5.5rem',
            }
        },
    },

    plugins: [
        require('@tailwindcss/typography'),
        require('@tailwindcss/forms'),
    ],
};
