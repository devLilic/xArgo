import { Head, Link, useForm } from '@inertiajs/react';
import type { ChangeEvent, FormEvent } from 'react';

type AppOption = { id: number; name: string; appId: string };
type PlanOption = { id: number; appId: number; name: string; code: string; defaultMaxDevices: number };
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
    notes: string | null;
    app: AppOption;
    plan: { id: number; name: string; code: string };
};

type Props = {
    managedLicense: ManagedLicense;
    apps: AppOption[];
    plans: PlanOption[];
    status?: string | null;
    can: { update: boolean; changeStatus: boolean; delete: boolean; restore: boolean };
};

export default function EditLicense({ managedLicense, apps, plans, status = null, can }: Props) {
    const form = useForm({
        app_id: managedLicense.app.id,
        plan_id: managedLicense.plan.id,
        customer_name: managedLicense.customerName ?? '',
        customer_email: managedLicense.customerEmail ?? '',
        max_devices: String(managedLicense.maxDevices),
        expires_at: managedLicense.expiresAt ? managedLicense.expiresAt.slice(0, 16) : '',
        grace_hours: String(managedLicense.graceHours),
        notes: managedLicense.notes ?? '',
    });
    const availablePlans = plans.filter((plan) => plan.appId === Number(form.data.app_id));

    const updateApp = (event: ChangeEvent<HTMLSelectElement>) => {
        const nextAppId = Number(event.target.value);
        const nextPlan = plans.find((plan) => plan.appId === nextAppId);
        form.setData((data) => ({ ...data, app_id: nextAppId, plan_id: nextPlan?.id ?? 0 }));
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            max_devices: Number(data.max_devices),
            grace_hours: Number(data.grace_hours),
            expires_at: data.expires_at || null,
        }));
        form.patch(route('admin.licenses.update', managedLicense.id));
    };

    return (
        <>
            <Head title={managedLicense.licenseKey} />
            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Edit License</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{managedLicense.licenseKey}</h1>
                                <p className="mt-2 text-sm text-slate-600">Public identifier: {managedLicense.publicKey}</p>
                            </div>
                            <div className="flex gap-3">
                                <Link href={route('admin.licenses.show', managedLicense.id)} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Back to detail</Link>
                            </div>
                        </div>
                    </section>
                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        {can.update ? (
                            <form className="grid gap-4 md:grid-cols-2" onSubmit={submit}>
                                <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Application</span><select value={form.data.app_id} onChange={updateApp} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600">{apps.map((app) => <option key={app.id} value={app.id}>{app.name} ({app.appId})</option>)}</select>{form.errors.app_id ? <p className="mt-2 text-sm text-rose-600">{form.errors.app_id}</p> : null}</label>
                                <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Plan</span><select value={form.data.plan_id} onChange={(event) => form.setData('plan_id', Number(event.target.value))} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600">{availablePlans.map((plan) => <option key={plan.id} value={plan.id}>{plan.name} ({plan.code})</option>)}</select>{form.errors.plan_id ? <p className="mt-2 text-sm text-rose-600">{form.errors.plan_id}</p> : null}</label>
                                <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Customer name</span><input type="text" value={form.data.customer_name} onChange={(event) => form.setData('customer_name', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Customer email</span><input type="email" value={form.data.customer_email} onChange={(event) => form.setData('customer_email', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Max devices</span><input type="number" min={1} value={form.data.max_devices} onChange={(event) => form.setData('max_devices', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Grace hours</span><input type="number" min={0} value={form.data.grace_hours} onChange={(event) => form.setData('grace_hours', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                <label className="block md:col-span-2"><span className="mb-2 block text-sm font-medium text-slate-700">Expires at</span><input type="datetime-local" value={form.data.expires_at} onChange={(event) => form.setData('expires_at', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                <label className="block md:col-span-2"><span className="mb-2 block text-sm font-medium text-slate-700">Notes</span><textarea value={form.data.notes} onChange={(event) => form.setData('notes', event.target.value)} className="min-h-28 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                                <div className="md:col-span-2"><button type="submit" disabled={form.processing} className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60">Save license</button></div>
                            </form>
                        ) : <p className="text-sm text-slate-500">Your role does not allow license changes.</p>}
                    </section>
                </div>
            </main>
        </>
    );
}
