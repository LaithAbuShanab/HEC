import { cn } from '@/lib/utils';

const TONES: Record<string, string> = {
    in_stock: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    on_order: 'bg-sky-50 text-sky-800 ring-sky-600/20',
    temporarily_unavailable: 'bg-rose-50 text-rose-700 ring-rose-600/20',
    needs_confirmation: 'bg-steel-100 text-steel-600 ring-steel-500/20',
};

export function AvailabilityBadge({ value, label, className }: { value: string; label: string; className?: string }) {
    return (
        <span className={cn('inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset', TONES[value] ?? TONES.needs_confirmation, className)}>
            <span aria-hidden className={cn('size-1.5 rounded-full bg-current', value === 'in_stock' && 'animate-ember')} />
            {label}
        </span>
    );
}
