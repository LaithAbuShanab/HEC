import { MessageCircle, Phone } from 'lucide-react';
import { useSite } from '@/hooks/use-site';

export function FloatingContact() {
    const { company, ui } = useSite();

    if (!company.whatsapp && !company.phone) {
        return null;
    }

    return (
        <div className="pointer-events-none fixed end-4 bottom-[max(1rem,env(safe-area-inset-bottom))] z-40 flex flex-col gap-2">
            {company.whatsapp && (
                <a
                    href={company.whatsapp.href}
                    target="_blank"
                    rel="noopener"
                    aria-label={ui.actions.whatsapp}
                    data-analytics="whatsapp_click"
                    className="pointer-events-auto grid size-12 place-items-center rounded-full bg-[#1f9d55] text-white shadow-lg ring-1 ring-white/30 transition hover:scale-105"
                >
                    <MessageCircle aria-hidden className="size-6" />
                </a>
            )}
            {company.phone && (
                <a
                    href={company.phone.href}
                    aria-label={ui.actions.call}
                    data-analytics="phone_click"
                    className="pointer-events-auto grid size-12 place-items-center rounded-full bg-navy text-white shadow-lg ring-1 ring-white/30 transition hover:scale-105"
                >
                    <Phone aria-hidden className="size-5" />
                </a>
            )}
        </div>
    );
}
