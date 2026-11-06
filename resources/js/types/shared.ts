export type Locale = 'ar' | 'en';

export type ContactLink = {
    display: string;
    href: string;
};

export type Weekday = 'saturday' | 'sunday' | 'monday' | 'tuesday' | 'wednesday' | 'thursday' | 'friday';

export type OpeningHours = {
    days: Weekday[];
    opens: string;
    closes: string;
};

export type CompanyProfile = {
    name: string;
    legalName: string | null;
    tagline: string | null;
    phone: ContactLink | null;
    whatsapp: ContactLink | null;
    email: string | null;
    address: string | null;
    mapsUrl: string | null;
    geo: { lat: number; lng: number } | null;
    hours: OpeningHours[] | null;
    hoursNote: string | null;
    serviceAreas: string | null;
    social: Partial<Record<'facebook' | 'instagram' | 'youtube' | 'linkedin' | 'tiktok', string>>;
};

export type NavCategory = {
    slug: string;
    name: string;
    products: number;
};

export type SiteNav = {
    categories: NavCategory[];
    sections: Record<'services' | 'industries' | 'guides' | 'work' | 'faq' | 'about', boolean>;
};

export type HowStep = {
    title: string;
    body: string;
};

export type SiteStrings = {
    skip_to_content: string;
    brand: string;
    nav: Record<string, string>;
    actions: Record<string, string>;
    home: Record<string, string> & {
        how_steps: HowStep[];
        points: HowStep[];
        popular_terms: string[];
        line_examples: string[];
    };
    footer: Record<string, string>;
    weekdays: Record<string, string>;
    specs: Record<string, string>;
    filters: Record<string, string>;
    catalog: Record<string, string>;
    search: Record<string, string>;
};

export type SharedProps = {
    name: string;
    locale: Locale;
    direction: 'rtl' | 'ltr';
    alternates: Record<Locale, string>;
    company: CompanyProfile;
    nav: SiteNav;
    ui: SiteStrings;
};

export type SeoProps = {
    title: string;
    description: string | null;
    canonical: string;
    alternates: Record<string, string>;
    robots: string;
    image: string;
    type: string;
    locale: string;
    siteName: string;
    jsonLd: Record<string, unknown>[];
};

export type Image = {
    src: string;
    srcset: string | null;
    alt: string;
    width: number | null;
    height: number | null;
};

export type ProductCardData = {
    slug: string;
    category: string | null;
    categoryName: string | null;
    name: string;
    summary: string | null;
    image: Image | null;
    availability: { value: string; label: string };
    condition: string | null;
    variants: number | null;
};

export type PageLink = { page: number; url: string; active: boolean };

export type Paginated<T> = {
    data: T[];
    currentPage: number;
    lastPage: number;
    links: PageLink[];
    prev: string | null;
    next: string | null;
};
