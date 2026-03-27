import { Head, Link, useForm } from '@inertiajs/react';
import type { ChangeEvent, FormEvent } from 'react';

type AppOption = {
    id: number;
    name: string;
    appId: string;
};

type DurationOption = {
    value: string;
    label: string;
};

type ManagedPlan = {
    id: number;
    app: AppOption;
    name: string;
    code: string;
    durationType: string;
    durationDays: number | null;
    defaultMaxDevices: number;
    isActive: boolean;
    createdAt: string | null;
};

type EditPlanProps = {
    managedPlan: ManagedPlan;
    apps: AppOption[];
    durationTypes: DurationOption[];
    status?: string | null;
    can: {
        update: boolean;
    };
};

export default function EditPlan({ managedPlan, apps, durationTypes, status = null, can }: EditPlanProps) {
    const form = useForm({
        app_id: managedPlan.app.id,
        name: managedPlan.name,
        code: managedPlan.code,
        duration_type: managedPlan.durationType,
        duration_days: managedPlan.durationDays ? String(managedPlan.durationDays) : '',
        default_max_devices: String(managedPlan.defaultMaxDevices),
        is_active: managedPlan.isActive,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            duration_days: data.duration_type === 'permanent' || data.duration_days === '' ? null : Number(data.duration_days),
            default_max_devices: Number(data.default_max_devices),
        }));

        form.patch(route('admin.plans.update', managedPlan.id));
    };

    const updateDurationType = (event: ChangeEvent<HTMLSelectElement>) => {
        const value = event.target.value;
        form.setData('duration_type', value);

        if (value === 'permanent') {
            form.setData('duration_days', '');
        }
    };

    return (
        <>
            <Head title={managedPlan.name} />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Plan Detail</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{managedPlan.name}</h1>
                                <p className="mt-2 text-sm text-slate-600">{managedPlan.code} for {managedPlan.app.name}</p>
                            </div>

                            <div className="flex gap-3">
                                <Link href={route('admin.apps.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                    Manage apps
                                </Link>
                                <Link href={route('admin.plans.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                    Back to plans
                                </Link>
                            </div>
                        </div>
                    </section>

                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}

                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        {can.update ? (
                            <form className="grid gap-4 md:grid-cols-2" onSubmit={submit}>
                                <label className="block">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">Application</span>
                                    <select value={form.data.app_id} onChange={(event) => form.setData('app_id', Number(event.target.value))} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600">
                                        {apps.map((app) => (
                                            <option key={app.id} value={app.id}>
                                                {app.name} ({app.appId})
                                            </option>
                                        ))}
                                    </select>
                                    {form.errors.app_id ? <p className="mt-2 text-sm text-rose-600">{form.errors.app_id}</p> : null}
                                </label>
                                <label className="block">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">Plan name</span>
                                    <input type="text" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" required />
                                    {form.errors.name ? <p className="mt-2 text-sm text-rose-600">{form.errors.name}</p> : null}
                                </label>
                                <label className="block">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">Plan code</span>
                                    <input type="text" value={form.data.code} onChange={(event) => form.setData('code', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" required />
                                    {form.errors.code ? <p className="mt-2 text-sm text-rose-600">{form.errors.code}</p> : null}
                                </label>
                                <label className="block">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">Duration type</span>
                                    <select value={form.data.duration_type} onChange={updateDurationType} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600">
                                        {durationTypes.map((type) => (
                                            <option key={type.value} value={type.value}>
                                                {type.label}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className="block">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">Duration days</span>
                                    <input type="number" min={1} value={form.data.duration_days} onChange={(event) => form.setData('duration_days', event.target.value)} disabled={form.data.duration_type === 'permanent'} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600 disabled:bg-slate-100" />
                                    {form.errors.duration_days ? <p className="mt-2 text-sm text-rose-600">{form.errors.duration_days}</p> : null}
                                </label>
                                <label className="block">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">Default max devices</span>
                                    <input type="number" min={1} value={form.data.default_max_devices} onChange={(event) => form.setData('default_max_devices', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" required />
                                    {form.errors.default_max_devices ? <p className="mt-2 text-sm text-rose-600">{form.errors.default_max_devices}</p> : null}
                                </label>
                                <label className="inline-flex items-center gap-3 text-sm font-medium text-slate-700 md:col-span-2">
                                    <input type="checkbox" checked={form.data.is_active} onChange={(event) => form.setData('is_active', event.target.checked)} className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600" />
                                    Plan is active
                                </label>
                                <div className="md:col-span-2">
                                    <button type="submit" disabled={form.processing} className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60">
                                        Save plan
                                    </button>
                                </div>
                            </form>
                        ) : (
                            <p className="text-sm text-slate-500">Your role does not allow plan changes.</p>
                        )}
                    </section>
                </div>
            </main>
        </>
    );
}
