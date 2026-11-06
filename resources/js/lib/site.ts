import type { Locale } from '@/types';

export function localized(locale: Locale, path = ''): string {
    const clean = path.replace(/^\/+|\/+$/g, '');

    return clean === '' ? `/${locale}` : `/${locale}/${clean}`;
}

export function otherLocale(locale: Locale): Locale {
    return locale === 'ar' ? 'en' : 'ar';
}

export function prefersReducedMotion(): boolean {
    return typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
