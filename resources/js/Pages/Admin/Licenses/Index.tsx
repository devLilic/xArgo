import { Head, Link, router, useForm } from '@inertiajs/react';
import type { ChangeEvent, FormEvent } from 'react';

type AppOption = { id: number; name: string; appId: string };
type PlanOption = { id: number; appId: number; name: string; code: string; defaultMaxDevices: number };
type StatusOption = { value: string; label: string };
type ManagedLicense = {
    id: number;
    publicKey: string;
    licenseKey: string;
    customerName: string | null;
    customerEmail: string | null;
    status: string;
    maxDevices: number;
    expiresAt: string | null;
    graceHours: number;
    deletedAt: string | null;
    createdAt: string | null;
    app: AppOption;
    plan: { id: number; name: string; code: string };
};

type Props = {
    licenses: ManagedLicense[];
    apps: AppOption[];
    plans: PlanOption[];
    statuses: StatusOption[];
    filters: { license_key: string; customer_email: string; app_id: number | null; status: string };
    defaults: { maxDevices: number; graceHours: number };
    status?: string | null;
    can: { create: boolean; export: boolean };
};

export default function LicensesIndex({ licenses, apps, plans, statuses, filters, defaults, status = null, can }: Props) {
    const filterForm = useForm({
        license_key: filters.license_key,
        customer_email: filters.customer_email,
        app_id: filters.app_id ? String(filters.app_id) : '',
        status: filters.status,
    });
    const createForm = useForm({
        app_id: apps[0]?.id ?? 0,
        plan_id: plans.find((plan) => plan.appId === apps[0]?.id)?.id ?? 0,
        customer_name: '',
        customer_email: '',
        status: 'active',
        max_devices: String(defaults.maxDevices),
        expires_at: '',
        grace_hours: String(defaults.graceHours),
        notes: '',
    });
    const availablePlans = plans.filter((plan) => plan.appId === Number(createForm.data.app_id));

    const submitFilters = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.get(route('admin.licenses.index'), {
            license_key: filterForm.data.license_key || undefined,
            customer_email: filterForm.data.customer_email || undefined,
            app_id: filterForm.data.app_id || undefined,
            status: filterForm.data.status || undefined,
        }, { preserveState: true, replace: true });
    };

    const clearFilters = () => {
        filterForm.setData({ license_key: '', customer_email: '', app_id: '', status: '' });
        router.get(route('admin.licenses.index'), {}, { preserveState: true, replace: true });
    };

    const updateCreateApp = (event: ChangeEvent<HTMLSelectElement>) => {
        const nextAppId = Number(event.target.value);
        const nextPlan = plans.find((plan) => plan.appId === nextAppId);
        createForm.setData((data) => ({ ...data, app_id: nextAppId, plan_id: nextPlan?.id ?? 0 }));
    };

    const submitCreate = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        createForm.transform((data) => ({
            ...data,
            max_devices: Number(data.max_devices),
            grace_hours: Number(data.grace_hours),
            expires_at: data.expires_at || null,
        }));
        createForm.post(route('admin.licenses.store'), { preserveScroll: true });
    };

    return (
        <>
            <Head title="Licenses" />
            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-7xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Licenses</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">License admin</h1>
                                <p className="mt-2 text-sm text-slate-600">Search, issue, review, suspend, revoke, and archive licenses across all managed apps.</p>
                            </div>
                            <div className="flex gap-3">
                                {can.export ? (
                                    <Link
                                        href={route('admin.licenses.export', {
                                            license_key: filterForm.data.license_key || undefined,
                                            customer_email: filterForm.data.customer_email || undefined,
                                            app_id: filterForm.data.app_id || undefined,
                                            status: filterForm.data.status || undefined,
                                        })}
                                        className="inline-flex rounded-full border border-emerald-300 px-4 py-2 text-sm font-medium text-emerald-800 transition hover:border-emerald-500"
                                    >
                                        Export CSV
                                    </Link>
                                ) : null}
                                <Link href={route('admin.plans.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Manage plans</Link>
                                <Link href={route('admin.dashboard')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Back to dashboard</Link>
                            </div>
                        </div>
                    </section>

                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <form className="grid gap-4 md:grid-cols-4" onSubmit={submitFilters}>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">License key</span><input type="text" value={filterForm.data.license_key} onChange={(event) => filterForm.setData('license_key', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Customer email</span><input type="text" value={filterForm.data.customer_email} onChange={(event) => filterForm.setData('customer_email', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Application</span><select value={filterForm.data.app_id} onChange={(event) => filterForm.setData('app_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600"><option value="">All apps</option>{apps.map((app) => <option key={app.id} value={app.id}>{app.name}</option>)}</select></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Status</span><select value={filterForm.data.status} onChange={(event) => filterForm.setData('status', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600"><option value="">All statuses</option>{statuses.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}</select></label>
                            <div className="md:col-span-4 flex gap-3">
                                <button type="submit" className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95">Apply filters</button>
                                <button type="button" onClick={clearFilters} className="inline-flex rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Clear</button>
                            </div>
                        </form>
                    </section>

                    {can.create ? (
                        <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <div className="space-y-4">
                                <h2 className="text-xl font-semibold tracking-tight text-slate-900">Create license</h2>
                                {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}
                                <form className="grid gap-4 md:grid-cols-2" onSubmit={submitCreate}>
                                    <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Application</span><select value={createForm.data.app_id} onChange={updateCreateApp} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600">{apps.map((app) => <option key={app.id} value={app.id}>{app.name} ({app.appId})</option>)}</select>{createForm.errors.app_id ? <p className="mt-2 text-sm text-rose-600">{createForm.errors.app_id}</p> : null}</label>
                                    <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Plan</span><select value={createForm.data.plan_id} onChange={(event) => createForm.setData('plan_id', Number(event.target.value))} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600">{availablePlans.map((plan) => <option key={plan.id} value={plan.id}>{plan.name} ({plan.code})</option>)}</select>{createForm.errors.plan_id ? <p className="mt-2 text-sm text-rose-600">{createForm.errors.plan_id}</p> : null}</label>
                                    <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Customer name</span><input type="text" value={createForm.data.customer_name} onChange={(event) => createForm.setData('customer_name', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                    <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Customer email</span><input type="email" value={createForm.data.customer_email} onChange={(event) => createForm.setData('customer_email', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                    <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Initial status</span><select value={createForm.data.status} onChange={(event) => createForm.setData('status', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600">{statuses.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}</select></label>
                                    <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Max devices</span><input type="number" min={1} value={createForm.data.max_devices} onChange={(event) => createForm.setData('max_devices', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                    <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Expires at</span><input type="datetime-local" value={createForm.data.expires_at} onChange={(event) => createForm.setData('expires_at', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                    <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Grace hours</span><input type="number" min={0} value={createForm.data.grace_hours} onChange={(event) => createForm.setData('grace_hours', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                    <label className="block md:col-span-2"><span className="mb-2 block text-sm font-medium text-slate-700">Notes</span><textarea value={createForm.data.notes} onChange={(event) => createForm.setData('notes', event.target.value)} className="min-h-28 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                    <div className="md:col-span-2"><button type="submit" disabled={createForm.processing} className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60">Create license</button></div>
                                </form>
                            </div>
                        </section>
                    ) : null}

                    <section className="overflow-hidden rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50"><tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-500"><th className="px-6 py-4 font-medium">License</th><th className="px-6 py-4 font-medium">Customer</th><th className="px-6 py-4 font-medium">Application</th><th className="px-6 py-4 font-medium">Status</th><th className="px-6 py-4 font-medium">Expires</th><th className="px-6 py-4 font-medium"></th></tr></thead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {licenses.map((license) => (
                                        <tr key={license.id} className="text-sm text-slate-700">
                                            <td className="px-6 py-4"><div className="font-medium text-slate-900">{license.licenseKey}</div><div className="text-slate-500">{license.publicKey}</div></td>
                                            <td className="px-6 py-4"><div className="font-medium text-slate-900">{license.customerName || 'n/a'}</div><div className="text-slate-500">{license.customerEmail || 'n/a'}</div></td>
                                            <td className="px-6 py-4"><div className="font-medium text-slate-900">{license.app.name}</div><div className="text-slate-500">{license.plan.name}</div></td>
                                            <td className="px-6 py-4"><span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${license.deletedAt ? 'bg-slate-200 text-slate-700' : license.status === 'active' ? 'bg-emerald-100 text-emerald-800' : license.status === 'suspended' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'}`}>{license.deletedAt ? 'archived' : license.status}</span></td>
                                            <td className="px-6 py-4 text-slate-500">{license.expiresAt ? new Date(license.expiresAt).toLocaleString() : 'never'}</td>
                                            <td className="px-6 py-4 text-right"><Link href={route('admin.licenses.show', license.id)} className="text-sm font-medium text-[var(--color-accent)] hover:underline">View license</Link></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>
        </>
    );
}
