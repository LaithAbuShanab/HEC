export type Locale = 'ar' | 'en';

export type SharedProps = {
    name: string;
    locale: Locale;
    direction: 'rtl' | 'ltr';
    alternates: Record<Locale, string>;
};
