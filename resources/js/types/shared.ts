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

export type SharedProps = {
    name: string;
    locale: Locale;
    direction: 'rtl' | 'ltr';
    alternates: Record<Locale, string>;
    company: CompanyProfile;
};
