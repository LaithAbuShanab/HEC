import { usePage } from '@inertiajs/react';
import type { SharedProps } from '@/types';

export function useSite(): SharedProps {
    return usePage<SharedProps>().props;
}
