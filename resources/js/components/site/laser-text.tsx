import type { ElementType, ReactNode } from 'react';
import { cn } from '@/lib/utils';

type LaserTextProps = {
    as?: ElementType;
    className?: string;
    children: ReactNode;
};

export function LaserText({ as: Tag = 'span', className, children }: LaserTextProps) {
    return (
        <Tag className={cn('laser-wrap', className)}>
            <span className="laser-line">{children}</span>
            <span aria-hidden className="laser-beam" />
        </Tag>
    );
}
