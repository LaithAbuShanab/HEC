import { Link, router, usePage } from '@inertiajs/react';
import { Check, LoaderCircle, StickyNote, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { useSite } from '@/hooks/use-site';
import { localized } from '@/lib/site';
import type { Image } from '@/types';
import { CategoryGlyph } from './category-glyph';
import { inputClass } from './quote-field';
import { ResponsiveImage } from './responsive-image';

export type QuoteItem = {
    id: number;
    custom: boolean;
    name: string;
    href: string | null;
    image: Image | null;
    category: string | null;
    variants: { id: number; label: string }[];
    variant: number | null;
    thickness: string | null;
    dimensions: string | null;
    quantity: string | null;
    unit: string | null;
    notes: string | null;
};

type Editable = Pick<QuoteItem, 'thickness' | 'dimensions' | 'quantity' | 'unit' | 'notes'> & { variant: string };

type Status = 'idle' | 'saving' | 'saved';

const smallInput = inputClass.replace('px-4 py-3', 'px-3 py-2.5 text-sm');

export function QuoteItemRow({ item, index, unitsListId }: { item: QuoteItem; index: number; unitsListId: string }) {
    const { locale, ui } = useSite();
    const bag = `item${item.id}`;
    const errors = (usePage().props.errors as Record<string, Record<string, string> | undefined>)[bag] ?? {};
    const [values, setValues] = useState<Editable>({
        variant: item.variant === null ? '' : String(item.variant),
        thickness: item.thickness ?? '',
        dimensions: item.dimensions ?? '',
        quantity: item.quantity ?? '',
        unit: item.unit ?? '',
        notes: item.notes ?? '',
    });
    const [saved, setSaved] = useState<Editable>(values);
    const [status, setStatus] = useState<Status>('idle');
    const [notesOpen, setNotesOpen] = useState(Boolean(item.notes));
    const [removing, setRemoving] = useState(false);
    const url = localized(locale, `request-quote/items/${item.id}`);
    const prefix = `item-${item.id}`;

    const save = (next: Editable) => {
        if (JSON.stringify(next) === JSON.stringify(saved)) {
            return;
        }

        setStatus('saving');
        router.patch(url, { ...next, variant: next.variant === '' ? null : Number(next.variant) }, {
            preserveScroll: true,
            preserveState: true,
            errorBag: bag,
            onSuccess: () => {
                setSaved(next);
                setStatus('saved');
            },
            onError: () => setStatus('idle'),
        });
    };

    const update = (key: keyof Editable, value: string, commit = false) => {
        const next = { ...values, [key]: value };
        setValues(next);

        if (commit) {
            save(next);
        }
    };

    const text = (key: 'thickness' | 'dimensions' | 'quantity' | 'unit' | 'notes') => ({
        value: values[key] ?? '',
        onChange: (event: { target: { value: string } }) => update(key, event.target.value),
        onBlur: () => save(values),
        'aria-invalid': Boolean(errors[key]),
        'aria-describedby': errors[key] ? `${prefix}-${key}-error` : undefined,
    });

    const remove = () => {
        setRemoving(true);
        router.delete(url, { preserveScroll: true, onFinish: () => setRemoving(false) });
    };

    return (
        <li className="relative rounded-2xl border border-steel-200 bg-white p-4 shadow-xs transition sm:p-5" aria-busy={removing}>
            <div className="flex gap-4">
                <div className="relative size-16 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-steel-100 to-steel-200 sm:size-20">
                    {item.image ? (
                        <ResponsiveImage image={item.image} alt="" sizes="80px" />
                    ) : (
                        <CategoryGlyph slug={item.category ?? 'sections-bars'} className="absolute inset-0 m-auto size-10 text-steel-400" />
                    )}
                    <span aria-hidden className="stamp absolute start-1 top-1 rounded bg-navy/85 px-1.5 text-[10px] text-white">
                        {String(index + 1).padStart(2, '0')}
                    </span>
                </div>
                <div className="min-w-0 flex-1">
                    <div className="flex items-start justify-between gap-3">
                        <div className="min-w-0">
                            {item.href ? (
                                <Link href={item.href} className="font-semibold text-navy hover:text-blue">
                                    {item.name}
                                </Link>
                            ) : (
                                <p className="font-semibold break-words text-navy">{item.name}</p>
                            )}
                            {item.custom && <span className="mt-1 inline-block rounded-full bg-gold/15 px-2 py-0.5 text-xs font-medium text-[#8a6a10]">{ui.quote.custom_item}</span>}
                        </div>
                        <div className="flex shrink-0 items-center gap-2">
                            <span aria-live="polite" className="text-xs text-steel-500">
                                {status === 'saving' && (
                                    <span className="inline-flex items-center gap-1">
                                        <LoaderCircle aria-hidden className="size-3.5 animate-spin" />
                                        {ui.quote.saving}
                                    </span>
                                )}
                                {status === 'saved' && (
                                    <span className="inline-flex items-center gap-1 text-emerald-700">
                                        <Check aria-hidden className="size-3.5" />
                                        {ui.quote.saved}
                                    </span>
                                )}
                            </span>
                            <button
                                type="button"
                                onClick={remove}
                                disabled={removing}
                                data-analytics="remove_from_quote"
                                aria-label={ui.quote.remove_item.replace(':name', item.name)}
                                className="inline-flex size-9 items-center justify-center rounded-full text-steel-500 transition hover:bg-rose-50 hover:text-rose-700 disabled:opacity-50"
                            >
                                <Trash2 aria-hidden className="size-4" />
                            </button>
                        </div>
                    </div>

                    <div className="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                        {item.variants.length > 0 && (
                            <div className="col-span-2">
                                <label htmlFor={`${prefix}-variant`} className="mb-1 block text-xs font-medium text-steel-600">
                                    {ui.quote.fields.variant}
                                </label>
                                <select
                                    id={`${prefix}-variant`}
                                    value={values.variant}
                                    onChange={(event) => update('variant', event.target.value, true)}
                                    aria-invalid={Boolean(errors.variant)}
                                    className={smallInput}
                                >
                                    <option value="">{ui.quote.no_variant}</option>
                                    {item.variants.map((variant) => (
                                        <option key={variant.id} value={variant.id}>
                                            {variant.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        )}
                        <div>
                            <label htmlFor={`${prefix}-quantity`} className="mb-1 block text-xs font-medium text-steel-600">
                                {ui.quote.fields.quantity}
                            </label>
                            <input id={`${prefix}-quantity`} type="text" inputMode="decimal" dir="ltr" autoComplete="off" className={smallInput} {...text('quantity')} />
                        </div>
                        <div>
                            <label htmlFor={`${prefix}-unit`} className="mb-1 block text-xs font-medium text-steel-600">
                                {ui.quote.fields.unit}
                            </label>
                            <input id={`${prefix}-unit`} type="text" list={unitsListId} autoComplete="off" className={smallInput} {...text('unit')} />
                        </div>
                        <div className={item.variants.length > 0 ? 'col-span-1 md:col-span-2' : ''}>
                            <label htmlFor={`${prefix}-thickness`} className="mb-1 block text-xs font-medium text-steel-600">
                                {ui.quote.fields.thickness}
                            </label>
                            <input id={`${prefix}-thickness`} type="text" autoComplete="off" className={smallInput} {...text('thickness')} />
                        </div>
                        <div className={item.variants.length > 0 ? 'col-span-1 md:col-span-2' : ''}>
                            <label htmlFor={`${prefix}-dimensions`} className="mb-1 block text-xs font-medium text-steel-600">
                                {ui.quote.fields.dimensions}
                            </label>
                            <input id={`${prefix}-dimensions`} type="text" autoComplete="off" className={smallInput} {...text('dimensions')} />
                        </div>
                    </div>

                    {Object.entries(errors).map(([key, message]) => (
                        <p key={key} id={`${prefix}-${key}-error`} className="mt-2 text-sm font-medium text-rose-700">
                            {message}
                        </p>
                    ))}

                    {notesOpen ? (
                        <div className="mt-3">
                            <label htmlFor={`${prefix}-notes`} className="mb-1 block text-xs font-medium text-steel-600">
                                {ui.quote.fields.notes}
                            </label>
                            <textarea id={`${prefix}-notes`} rows={2} maxLength={500} className={smallInput} {...text('notes')} />
                        </div>
                    ) : (
                        <button
                            type="button"
                            onClick={() => setNotesOpen(true)}
                            className="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-blue hover:text-navy"
                        >
                            <StickyNote aria-hidden className="size-4" />
                            {ui.quote.add_notes}
                        </button>
                    )}
                </div>
            </div>
        </li>
    );
}
