import { cn } from '@/lib/utils';

type LogoRevealProps = {
    className?: string;
    variant?: 'color' | 'white';
    label: string;
};

export function LogoReveal({ className, variant = 'color', label }: LogoRevealProps) {
    return (
        <span className={cn('hec-logo-reveal relative inline-block aspect-square', className)}>
            <img
                src={`/images/brand/mark-${variant}.webp`}
                alt={label}
                width={512}
                height={512}
                className="size-full object-contain"
                decoding="async"
            />
            <span aria-hidden className="hec-logo-reveal__spark" />
        </span>
    );
}
