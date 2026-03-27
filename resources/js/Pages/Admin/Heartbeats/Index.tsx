import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type AppOption = { id: number; name: string; appId: string };

type ManagedHeartbeat = {
    id: number;
    appVersion: string;
    receivedAt: string | null;
    ipAddress: string | null;
    reasonCode: string | null;
    activation: {
        id: number;
        activationId: string;
        machineId: string;
        installationId: string;
        status: string;
    };
    license: {
        id: number;
        licenseKey: string;
        publicKey: string;
    };
    app: {
        id: number;
        name: string;
        appId: string;
    };
};

type Props = {
    heartbeats: ManagedHeartbeat[];
    apps: AppOption[];
    filters: {
        app_id: number | null;
        license_key: string;
        machine_id: string;
        installation_id: string;
        activation_id: string;
    };
    status?: string | null;
};

export default function HeartbeatIndex({ heartbeats, apps, filters, status = null }: Props) {
    const form = useForm({
        app_id: filters.app_id ? String(filters.app_id) : '',
        license_key: filters.license_key,
        machine_id: filters.machine_id,
        installation_id: filters.installation_id,
        activation_id: filters.activation_id,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        router.get(route('admin.heartbeats.index'), {
            app_id: form.data.app_id || undefined,
            license_key: form.data.license_key || undefined,
            machine_id: form.data.machine_id || undefined,
            installation_id: form.data.installation_id || undefined,
            activation_id: form.data.activation_id || undefined,
        }, { preserveState: true, replace: true });
    };

    const clear = () => {
        form.setData({
            app_id: '',
            license_key: '',
            machine_id: '',
            installation_id: '',
            activation_id: '',
        });

        router.get(route('admin.heartbeats.index'), {}, { preserveState: true, replace: true });
    };

    return (
        <>
            <Head title="Heartbeats" />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-7xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Heartbeats</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Recent device heartbeat records</h1>
                                <p className="mt-2 text-sm text-slate-600">Review recent license activity across apps, licenses, and bound device identifiers.</p>
                            </div>
                            <div className="flex gap-3">
                                <Link href={route('admin.activations.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">View activations</Link>
                                <Link href={route('admin.dashboard')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Back to dashboard</Link>
                            </div>
                        </div>
                    </section>

                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}

                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <form className="grid gap-4 md:grid-cols-5" onSubmit={submit}>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Application</span><select value={form.data.app_id} onChange={(event) => form.setData('app_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600"><option value="">All apps</option>{apps.map((app) => <option key={app.id} value={app.id}>{app.name}</option>)}</select></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">License key</span><input type="text" value={form.data.license_key} onChange={(event) => form.setData('license_key', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Machine ID</span><input type="text" value={form.data.machine_id} onChange={(event) => form.setData('machine_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Installation ID</span><input type="text" value={form.data.installation_id} onChange={(event) => form.setData('installation_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Activation ID</span><input type="text" value={form.data.activation_id} onChange={(event) => form.setData('activation_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
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
                                        <th className="px-6 py-4 font-medium">Received</th>
                                        <th className="px-6 py-4 font-medium">Activation</th>
                                        <th className="px-6 py-4 font-medium">License</th>
                                        <th className="px-6 py-4 font-medium">Device</th>
                                        <th className="px-6 py-4 font-medium">App version</th>
                                        <th className="px-6 py-4 font-medium">Reason</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {heartbeats.map((heartbeat) => (
                                        <tr key={heartbeat.id} className="text-sm text-slate-700">
                                            <td className="px-6 py-4 text-slate-500">{heartbeat.receivedAt ? new Date(heartbeat.receivedAt).toLocaleString() : 'n/a'}</td>
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-slate-900">{heartbeat.activation.activationId}</div>
                                                <div className="text-slate-500">{heartbeat.activation.status}</div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-slate-900">{heartbeat.license.licenseKey}</div>
                                                <div className="text-slate-500">{heartbeat.app.name}</div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-slate-900">{heartbeat.activation.machineId}</div>
                                                <div className="text-slate-500">{heartbeat.activation.installationId}</div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-slate-900">{heartbeat.appVersion}</div>
                                                <div className="text-slate-500">{heartbeat.ipAddress || 'no IP recorded'}</div>
                                            </td>
                                            <td className="px-6 py-4 text-slate-500">{heartbeat.reasonCode || 'ok'}</td>
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
