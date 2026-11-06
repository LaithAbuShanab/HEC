import { Link, router, useForm, usePage } from '@inertiajs/react';
import { FileText, ImageIcon, LoaderCircle, Paperclip, Plus, Send, ShieldCheck, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ChangeEvent, DragEvent, FormEvent } from 'react';
import { CategoryGlyph } from '@/components/site/category-glyph';
import { PageHeader } from '@/components/site/page-header';
import { QuoteField, inputClass } from '@/components/site/quote-field';
import { QuoteItemRow } from '@/components/site/quote-item-row';
import type { QuoteItem } from '@/components/site/quote-item-row';
import { useSite } from '@/hooks/use-site';
import SiteLayout from '@/layouts/site-layout';
import { localized } from '@/lib/site';
import { cn } from '@/lib/utils';
import type { SeoProps } from '@/types';

type Honeypot = {
    enabled: boolean;
    nameFieldName: string;
    validFromFieldName: string;
    encryptedValidFrom: string;
};

type CreateProps = {
    items: QuoteItem[];
    itemsLabel: string;
    units: string[];
    fulfillment: { value: string; label: string }[];
    limits: { files: number; sizeMb: number; accept: string };
    honeypot: Honeypot;
    idempotencyKey: string;
    seo: SeoProps;
};

type QuoteForm = {
    idempotency_key: string;
    name: string;
    phone: string;
    company: string;
    email: string;
    city: string;
    fulfillment: string;
    needed_by: string;
    notes: string;
    privacy: boolean;
    attachments: File[];
    [key: string]: string | boolean | File[];
};

const ALLOWED_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];
const FIELD_ORDER = ['items', 'form', 'name', 'phone', 'company', 'email', 'city', 'fulfillment', 'needed_by', 'notes', 'attachments', 'privacy'];

export default function QuoteCreate({ items, itemsLabel, units, fulfillment, limits, honeypot, idempotencyKey, seo }: CreateProps) {
    const { locale, ui, nav } = useSite();
    const q = ui.quote;
    const pageErrors = usePage().props.errors as Record<string, string | Record<string, string>>;
    const summaryRef = useRef<HTMLDivElement>(null);
    const [fileErrors, setFileErrors] = useState<string[]>([]);
    const [dragging, setDragging] = useState(false);

    const form = useForm<QuoteForm>({
        idempotency_key: idempotencyKey,
        name: '',
        phone: '',
        company: '',
        email: '',
        city: '',
        fulfillment: 'undecided',
        needed_by: '',
        notes: '',
        privacy: false,
        attachments: [],
        ...(honeypot.enabled ? { [honeypot.nameFieldName]: '', [honeypot.validFromFieldName]: honeypot.encryptedValidFrom } : {}),
    });

    const errors = form.errors as Record<string, string | undefined>;
    const flatErrors: Record<string, string> = {};

    Object.entries(errors).forEach(([key, message]) => {
        if (!message) {
            return;
        }

        flatErrors[key.startsWith('attachments') ? 'attachments' : key] ??= message;
    });

    if (typeof pageErrors.items === 'string') {
        flatErrors.items ??= pageErrors.items;
    }

    const summary = FIELD_ORDER.filter((key) => flatErrors[key]);

    useEffect(() => {
        if (summary.length > 0) {
            summaryRef.current?.focus();
        }
    }, [form.errors]);

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (form.processing) {
            return;
        }

        form.post(localized(locale, 'request-quote'), { forceFormData: true, preserveScroll: true });
    };

    const addFiles = (incoming: File[]) => {
        const problems: string[] = [];
        const accepted = [...form.data.attachments];

        incoming.forEach((file) => {
            if (!ALLOWED_TYPES.includes(file.type)) {
                problems.push(`${file.name}: ${q.errors.file_type}`);
            } else if (file.size > limits.sizeMb * 1024 * 1024) {
                problems.push(`${file.name}: ${q.errors.file_too_large.replace(':max', String(limits.sizeMb))}`);
            } else if (accepted.length >= limits.files) {
                problems.push(q.errors.too_many_files.replace(':max', String(limits.files)));
            } else if (!accepted.some((existing) => existing.name === file.name && existing.size === file.size)) {
                accepted.push(file);
            }
        });

        setFileErrors([...new Set(problems)]);
        form.setData('attachments', accepted);
    };

    const onFiles = (event: ChangeEvent<HTMLInputElement>) => {
        addFiles(Array.from(event.target.files ?? []));
        event.target.value = '';
    };

    const onDrop = (event: DragEvent<HTMLLabelElement>) => {
        event.preventDefault();
        setDragging(false);
        addFiles(Array.from(event.dataTransfer.files));
    };

    const removeFile = (index: number) => {
        form.setData(
            'attachments',
            form.data.attachments.filter((_, position) => position !== index),
        );
        setFileErrors([]);
    };

    const text = (key: string) => ({
        name: String(key),
        value: String(form.data[key] ?? ''),
        onChange: (event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => form.setData(key, event.target.value),
    });

    const steps = q.steps as unknown as string[];
    const nextSteps = q.next_steps as unknown as string[];
    const activeStep = items.length === 0 ? 0 : 1;

    return (
        <SiteLayout seo={seo}>
            <PageHeader
                title={q.title}
                lead={q.lead}
                crumbs={[{ label: ui.nav.home, href: localized(locale) }, { label: q.title }]}
                aside={
                    <ol className="flex items-center gap-2 text-sm" aria-label={q.title}>
                        {steps.map((step, index) => (
                            <li key={step} className="flex items-center gap-2" aria-current={index === activeStep ? 'step' : undefined}>
                                <span
                                    className={cn(
                                        'stamp flex size-8 items-center justify-center rounded-full border text-xs',
                                        index < activeStep && 'border-gold bg-gold text-navy',
                                        index === activeStep && 'border-gold text-gold shadow-[0_0_18px_rgb(228_180_42/0.35)]',
                                        index > activeStep && 'border-steel-600 text-steel-400',
                                    )}
                                >
                                    {String(index + 1).padStart(2, '0')}
                                </span>
                                <span className={cn(index === activeStep ? 'text-white' : 'text-steel-400', 'hidden sm:inline')}>{step}</span>
                                {index < steps.length - 1 && <span aria-hidden className="h-px w-6 bg-steel-600" />}
                            </li>
                        ))}
                    </ol>
                }
            />

            <div className="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:px-8">
                <div className="min-w-0 space-y-12">
                    {summary.length > 0 && (
                        <div ref={summaryRef} tabIndex={-1} role="alert" className="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-rose-800 focus:outline-none">
                            <p className="font-semibold">{q.error_summary}</p>
                            <ul className="mt-2 list-disc space-y-1 ps-5 text-sm">
                                {summary.map((key) => (
                                    <li key={key}>
                                        {['items', 'form'].includes(key) ? (
                                            flatErrors[key]
                                        ) : (
                                            <a href={`#quote-${key}`} className="underline underline-offset-2">
                                                {flatErrors[key]}
                                            </a>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <section aria-labelledby="quote-items-title" id="quote-items">
                        <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
                            <h2 id="quote-items-title" className="font-display text-2xl font-bold">
                                {q.items_title}
                            </h2>
                            <span className="stamp rounded-full bg-steel-100 px-3 py-1 text-sm text-steel-600">{itemsLabel}</span>
                        </div>

                        {items.length > 0 ? (
                            <ol className="space-y-4">
                                {items.map((item, index) => (
                                    <QuoteItemRow key={item.id} item={item} index={index} unitsListId="quote-units" />
                                ))}
                            </ol>
                        ) : (
                            <div className="rounded-2xl border border-dashed border-steel-300 bg-white px-6 py-12 text-center">
                                <CategoryGlyph slug="sheets-plates" className="mx-auto size-14 text-steel-300" />
                                <p className="mt-5 text-lg font-semibold text-navy">{q.empty_title}</p>
                                <p className="mx-auto mt-2 max-w-md leading-7 text-steel-500">{q.empty_body}</p>
                                <Link href={localized(locale, 'products')} className="mt-6 inline-flex rounded-full bg-navy px-6 py-3 font-semibold text-white transition hover:bg-ink">
                                    {ui.actions.browse_products}
                                </Link>
                            </div>
                        )}

                        <datalist id="quote-units">
                            {units.map((unit) => (
                                <option key={unit} value={unit} />
                            ))}
                        </datalist>

                        <CustomItem />
                    </section>

                    <form onSubmit={submit} noValidate data-analytics="quote_form_start" aria-labelledby="quote-details-title" className="space-y-8">
                        <div>
                            <h2 id="quote-details-title" className="font-display text-2xl font-bold">
                                {q.details_title}
                            </h2>
                            <p className="mt-2 text-steel-500">{q.details_lead}</p>
                        </div>

                        {honeypot.enabled && (
                            <div aria-hidden className="hidden">
                                <input type="text" tabIndex={-1} autoComplete="off" {...text(honeypot.nameFieldName)} />
                                <input type="text" tabIndex={-1} autoComplete="off" {...text(honeypot.validFromFieldName)} />
                            </div>
                        )}

                        <div className="grid gap-5 sm:grid-cols-2">
                            <QuoteField id="quote-name" label={q.fields.name} required error={errors.name}>
                                {(props) => <input {...props} type="text" autoComplete="name" required maxLength={120} {...text('name')} />}
                            </QuoteField>
                            <QuoteField id="quote-phone" label={q.fields.phone} required error={errors.phone}>
                                {(props) => <input {...props} type="tel" dir="ltr" autoComplete="tel" inputMode="tel" required maxLength={32} {...text('phone')} className={cn(props.className, 'text-start')} />}
                            </QuoteField>
                            <QuoteField id="quote-company" label={q.fields.company} optional={q.optional} error={errors.company}>
                                {(props) => <input {...props} type="text" autoComplete="organization" maxLength={160} {...text('company')} />}
                            </QuoteField>
                            <QuoteField id="quote-email" label={q.fields.email} optional={q.optional} error={errors.email}>
                                {(props) => <input {...props} type="email" dir="ltr" autoComplete="email" maxLength={160} {...text('email')} className={cn(props.className, 'text-start')} />}
                            </QuoteField>
                            <QuoteField id="quote-city" label={q.fields.city} required error={errors.city}>
                                {(props) => <input {...props} type="text" autoComplete="address-level2" required maxLength={80} {...text('city')} />}
                            </QuoteField>
                            <QuoteField id="quote-needed_by" label={q.fields.needed_by} optional={q.optional} hint={q.needed_by_hint} error={errors.needed_by}>
                                {(props) => <input {...props} type="date" min={new Date().toISOString().slice(0, 10)} {...text('needed_by')} />}
                            </QuoteField>
                        </div>

                        <fieldset id="quote-fulfillment" aria-describedby="quote-fulfillment-hint">
                            <legend className="mb-1.5 text-sm font-semibold text-navy">{q.fields.fulfillment}</legend>
                            <p id="quote-fulfillment-hint" className="mb-3 text-xs text-steel-500">
                                {q.fulfillment_hint}
                            </p>
                            <div className="grid gap-3 sm:grid-cols-3">
                                {fulfillment.map((option) => (
                                    <label
                                        key={option.value}
                                        className={cn(
                                            'flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm font-medium transition has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-sky',
                                            form.data.fulfillment === option.value ? 'border-navy bg-navy text-white' : 'border-steel-300 bg-white text-navy hover:border-blue/50',
                                        )}
                                    >
                                        <input
                                            type="radio"
                                            name="fulfillment"
                                            value={option.value}
                                            checked={form.data.fulfillment === option.value}
                                            onChange={() => form.setData('fulfillment', option.value)}
                                            className="size-4 accent-gold"
                                        />
                                        {option.label}
                                    </label>
                                ))}
                            </div>
                            {errors.fulfillment && <p className="mt-1.5 text-sm font-medium text-rose-700">{errors.fulfillment}</p>}
                        </fieldset>

                        <QuoteField id="quote-notes" label={q.fields.notes} optional={q.optional} error={errors.notes}>
                            {(props) => <textarea {...props} rows={4} maxLength={3000} {...text('notes')} />}
                        </QuoteField>

                        <div id="quote-attachments">
                            <p className="mb-1.5 flex items-baseline justify-between gap-3 text-sm font-semibold text-navy">
                                <span>{q.attachments_title}</span>
                                <span className="text-xs font-normal text-steel-500">{q.optional}</span>
                            </p>
                            <label
                                htmlFor="quote-files"
                                onDragOver={(event) => {
                                    event.preventDefault();
                                    setDragging(true);
                                }}
                                onDragLeave={() => setDragging(false)}
                                onDrop={onDrop}
                                className={cn(
                                    'flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed px-6 py-8 text-center transition has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-sky',
                                    dragging ? 'border-gold bg-gold/5' : 'border-steel-300 bg-steel-50 hover:border-blue/50',
                                    form.data.attachments.length >= limits.files && 'pointer-events-none opacity-60',
                                )}
                            >
                                <Paperclip aria-hidden className="size-6 text-blue" />
                                <span className="font-medium text-navy">{q.attachments_drop}</span>
                                <span id="quote-files-hint" className="text-xs text-steel-500">
                                    {q.attachments_hint.replace(':size', String(limits.sizeMb)).replace(':max', String(limits.files))}
                                </span>
                                <input
                                    id="quote-files"
                                    type="file"
                                    multiple
                                    accept={limits.accept}
                                    onChange={onFiles}
                                    disabled={form.data.attachments.length >= limits.files}
                                    aria-describedby="quote-files-hint"
                                    className="sr-only"
                                />
                            </label>
                            {form.data.attachments.length > 0 && (
                                <ul className="mt-3 space-y-2">
                                    {form.data.attachments.map((file, index) => (
                                        <li key={`${file.name}-${file.size}`} className="flex items-center gap-3 rounded-xl border border-steel-200 bg-white px-4 py-2.5 text-sm">
                                            {file.type === 'application/pdf' ? <FileText aria-hidden className="size-4 text-ember" /> : <ImageIcon aria-hidden className="size-4 text-blue" />}
                                            <bdi className="min-w-0 flex-1 truncate">{file.name}</bdi>
                                            <span className="stamp text-xs text-steel-500" dir="ltr">
                                                {(file.size / 1024 / 1024).toFixed(1)} MB
                                            </span>
                                            <button
                                                type="button"
                                                onClick={() => removeFile(index)}
                                                aria-label={q.attachments_remove.replace(':name', file.name)}
                                                className="inline-flex size-8 items-center justify-center rounded-full text-steel-500 hover:bg-rose-50 hover:text-rose-700"
                                            >
                                                <X aria-hidden className="size-4" />
                                            </button>
                                        </li>
                                    ))}
                                </ul>
                            )}
                            {[...fileErrors, ...Object.entries(errors).filter(([key, message]) => key.startsWith('attachments') && message).map(([, message]) => message as string)].map((message) => (
                                <p key={message} className="mt-1.5 text-sm font-medium text-rose-700">
                                    {message}
                                </p>
                            ))}
                        </div>

                        <div id="quote-privacy">
                            <label className="flex cursor-pointer items-start gap-3 rounded-xl border border-steel-200 bg-white p-4 text-sm leading-6 text-steel-700 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-sky">
                                <input
                                    type="checkbox"
                                    checked={form.data.privacy}
                                    onChange={(event) => form.setData('privacy', event.target.checked)}
                                    aria-invalid={Boolean(errors.privacy)}
                                    aria-describedby={errors.privacy ? 'quote-privacy-error' : undefined}
                                    className="mt-1 size-4 shrink-0 accent-navy"
                                />
                                <span>
                                    {q.privacy}{' '}
                                    {nav.sections.privacy && (
                                        <a href={localized(locale, 'privacy')} target="_blank" rel="noopener" className="font-medium text-blue underline underline-offset-2">
                                            {q.privacy_link}
                                        </a>
                                    )}
                                </span>
                            </label>
                            {errors.privacy && (
                                <p id="quote-privacy-error" className="mt-1.5 text-sm font-medium text-rose-700">
                                    {errors.privacy}
                                </p>
                            )}
                        </div>

                        {flatErrors.form && <p className="rounded-xl bg-rose-50 p-4 text-sm font-medium text-rose-800">{flatErrors.form}</p>}

                        <div className="flex flex-wrap items-center gap-4">
                            <button
                                type="submit"
                                disabled={form.processing || items.length === 0}
                                aria-disabled={form.processing || items.length === 0}
                                className="inline-flex items-center gap-2 rounded-full bg-gold px-8 py-3.5 font-semibold text-navy shadow-[0_0_28px_rgb(228_180_42/0.35)] transition hover:bg-[#f0c64a] disabled:cursor-not-allowed disabled:opacity-60 disabled:shadow-none"
                            >
                                {form.processing ? <LoaderCircle aria-hidden className="size-5 animate-spin" /> : <Send aria-hidden className="size-5 rtl:-scale-x-100" />}
                                {form.processing ? q.submitting : q.submit}
                            </button>
                            {items.length === 0 && <p className="text-sm text-steel-500">{q.errors.empty}</p>}
                        </div>
                        {form.progress && (
                            <progress value={form.progress.percentage} max={100} className="h-1.5 w-full overflow-hidden rounded-full accent-gold">
                                {form.progress.percentage}%
                            </progress>
                        )}
                    </form>
                </div>

                <aside className="lg:sticky lg:top-[calc(var(--header-height)+1.5rem)] lg:self-start">
                    <div className="steel-texture relative overflow-hidden rounded-3xl p-6 text-white">
                        <div aria-hidden className="blueprint-grid absolute inset-0 opacity-40" />
                        <div className="relative">
                            <h2 className="font-display text-lg font-bold">{q.next_title}</h2>
                            <ol className="mt-5 space-y-4">
                                {nextSteps.map((step, index) => (
                                    <li key={step} className="flex gap-3 text-sm leading-6 text-steel-200">
                                        <span className="stamp flex size-7 shrink-0 items-center justify-center rounded-full border border-gold/60 text-xs text-gold">{index + 1}</span>
                                        {step}
                                    </li>
                                ))}
                            </ol>
                            <p className="mt-6 flex gap-2 border-t border-white/10 pt-5 text-xs leading-5 text-steel-400">
                                <ShieldCheck aria-hidden className="size-4 shrink-0 text-gold" />
                                {ui.home.quote_note}
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </SiteLayout>
    );
}

function CustomItem() {
    const { locale, ui } = useSite();
    const errors = (usePage().props.errors as Record<string, Record<string, string> | undefined>).custom ?? {};
    const [value, setValue] = useState('');
    const [busy, setBusy] = useState(false);

    const add = (event: FormEvent) => {
        event.preventDefault();

        if (value.trim().length < 2 || busy) {
            return;
        }

        setBusy(true);
        router.post(
            localized(locale, 'request-quote/items'),
            { free_text: value.trim() },
            {
                preserveScroll: true,
                errorBag: 'custom',
                onSuccess: () => setValue(''),
                onFinish: () => setBusy(false),
            },
        );
    };

    const message = errors.free_text ?? errors.items;

    return (
        <form onSubmit={add} className="mt-6 rounded-2xl border border-steel-200 bg-steel-50 p-4 sm:p-5">
            <label htmlFor="quote-custom" className="mb-2 block text-sm font-semibold text-navy">
                {ui.quote.custom_title}
            </label>
            <div className="flex flex-col gap-3 sm:flex-row">
                <input
                    id="quote-custom"
                    type="text"
                    value={value}
                    maxLength={250}
                    onChange={(event) => setValue(event.target.value)}
                    placeholder={ui.quote.custom_placeholder}
                    aria-invalid={Boolean(message)}
                    aria-describedby={message ? 'quote-custom-error' : undefined}
                    className={cn(inputClass, 'flex-1')}
                />
                <button
                    type="submit"
                    disabled={busy || value.trim().length < 2}
                    data-analytics="add_to_quote"
                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-navy px-5 py-3 font-semibold text-white transition hover:bg-ink disabled:opacity-50"
                >
                    {busy ? <LoaderCircle aria-hidden className="size-4 animate-spin" /> : <Plus aria-hidden className="size-4" />}
                    {ui.quote.custom_add}
                </button>
            </div>
            {message && (
                <p id="quote-custom-error" className="mt-2 text-sm font-medium text-rose-700">
                    {message}
                </p>
            )}
        </form>
    );
}
