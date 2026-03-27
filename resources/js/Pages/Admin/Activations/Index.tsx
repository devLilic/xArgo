import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type AppOption = { id: number; name: string; appId: string };
type StatusOption = { value: string; label: string };
type ManagedActivation = {
    id: number;
    activationId: string;
    machineId: string;
    installationId: string;
    deviceLabel: string | null;
    status: string;
    firstSeenAt: string | null;
    lastSeenAt: string | null;
    graceUntil: string | null;
    lastReasonCode: string | null;
    license: { id: number; licenseKey: string; publicKey: string };
    app: { id: number; name: string; appId: string };
};

type Props = {
    activations: ManagedActivation[];
    apps: AppOption[];
    statuses: StatusOption[];
    filters: {
        machine_id: string;
        installation_id: string;
        license_key: string;
        app_id: string;
        status: string;
    };
    status?: string | null;
};

export default function ActivationIndex({ activations, apps, statuses, filters, status = null }: Props) {
    const form = useForm({
        machine_id: filters.machine_id,
        installation_id: filters.installation_id,
        license_key: filters.license_key,
        app_id: filters.app_id,
        status: filters.status,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.get(route('admin.activations.index'), {
            machine_id: form.data.machine_id || undefined,
            installation_id: form.data.installation_id || undefined,
            license_key: form.data.license_key || undefined,
            app_id: form.data.app_id || undefined,
            status: form.data.status || undefined,
        }, { preserveState: true, replace: true });
    };

    const clear = () => {
        form.setData({
            machine_id: '',
            installation_id: '',
            license_key: '',
            app_id: '',
            status: '',
        });
        router.get(route('admin.activations.index'), {}, { preserveState: true, replace: true });
    };

    return (
        <>
            <Head title="Activations" />
            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-7xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Activations</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Device activation records</h1>
                                <p className="mt-2 text-sm text-slate-600">Inspect machine bindings, reason codes, and heartbeat visibility across all issued licenses.</p>
                            </div>
                            <div className="flex gap-3">
                                <Link href={route('admin.licenses.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Manage licenses</Link>
                                <Link href={route('admin.dashboard')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Back to dashboard</Link>
                            </div>
                        </div>
                    </section>

                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}

                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <form className="grid gap-4 md:grid-cols-5" onSubmit={submit}>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Machine ID</span><input type="text" value={form.data.machine_id} onChange={(event) => form.setData('machine_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Installation ID</span><input type="text" value={form.data.installation_id} onChange={(event) => form.setData('installation_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">License key</span><input type="text" value={form.data.license_key} onChange={(event) => form.setData('license_key', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">App ID</span><input type="text" value={form.data.app_id} onChange={(event) => form.setData('app_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" placeholder="xargo.desktop" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Status</span><select value={form.data.status} onChange={(event) => form.setData('status', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600"><option value="">All statuses</option>{statuses.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}</select></label>
                            <div className="md:col-span-5 flex gap-3">
                                <button type="submit" className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95">Apply filters</button>
                                <button type="button" onClick={clear} className="inline-flex rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Clear</button>
                            </div>
                        </form>
                    </section>

                    <section className="overflow-hidden rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                        <th className="px-6 py-4 font-medium">Activation</th>
                                        <th className="px-6 py-4 font-medium">License</th>
                                        <th className="px-6 py-4 font-medium">Device</th>
                                        <th className="px-6 py-4 font-medium">Status</th>
                                        <th className="px-6 py-4 font-medium">First seen</th>
                                        <th className="px-6 py-4 font-medium">Last seen</th>
                                        <th className="px-6 py-4 font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {activations.map((activation) => (
                                        <tr key={activation.id} className="text-sm text-slate-700">
                                            <td className="px-6 py-4"><div className="font-medium text-slate-900">{activation.activationId}</div><div className="text-slate-500">{activation.installationId}</div></td>
                                            <td className="px-6 py-4"><div className="font-medium text-slate-900">{activation.license.licenseKey}</div><div className="text-slate-500">{activation.app.name}</div></td>
                                            <td className="px-6 py-4"><div className="font-medium text-slate-900">{activation.deviceLabel || 'Unnamed device'}</div><div className="text-slate-500">{activation.machineId}</div></td>
                                            <td className="px-6 py-4"><div>{activation.status}</div><div className="text-slate-500">{activation.lastReasonCode || 'no reason code'}</div></td>
                                            <td className="px-6 py-4 text-slate-500">{activation.firstSeenAt ? new Date(activation.firstSeenAt).toLocaleString() : 'n/a'}</td>
                                            <td className="px-6 py-4 text-slate-500">{activation.lastSeenAt ? new Date(activation.lastSeenAt).toLocaleString() : 'n/a'}</td>
                                            <td className="px-6 py-4 text-right"><Link href={route('admin.activations.show', activation.id)} className="text-sm font-medium text-[var(--color-accent)] hover:underline">View activation</Link></td>
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
