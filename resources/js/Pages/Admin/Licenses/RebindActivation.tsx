import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type ManagedLicense = {
    id: number;
    licenseKey: string;
    publicKey: string;
    app: { name: string; appId: string };
    plan: { name: string; code: string };
};

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
};

type Props = {
    managedLicense: ManagedLicense;
    managedActivation: ManagedActivation;
    status?: string | null;
    can: { rebind: boolean };
};

export default function RebindActivation({ managedLicense, managedActivation, status = null, can }: Props) {
    const form = useForm({
        machine_id: managedActivation.machineId,
        installation_id: managedActivation.installationId,
        device_label: managedActivation.deviceLabel ?? '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(route('admin.licenses.activations.rebind.update', [managedLicense.id, managedActivation.id]));
    };

    return (
        <>
            <Head title={`Rebind ${managedLicense.licenseKey}`} />
            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Manual Rebind</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{managedLicense.licenseKey}</h1>
                                <p className="mt-2 text-sm text-slate-600">Review the current binding context and confirm the replacement device explicitly.</p>
                            </div>
                            <Link href={route('admin.licenses.show', managedLicense.id)} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Back to license</Link>
                        </div>
                    </section>

                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}

                    <section className="grid gap-6 lg:grid-cols-[1fr_1fr]">
                        <article className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <h2 className="text-xl font-semibold tracking-tight text-slate-900">Current binding</h2>
                            <dl className="mt-6 space-y-4 text-sm">
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Activation</dt><dd className="font-medium text-slate-900">{managedActivation.activationId}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Machine ID</dt><dd className="font-medium text-slate-900">{managedActivation.machineId}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Installation ID</dt><dd className="font-medium text-slate-900">{managedActivation.installationId}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Device label</dt><dd className="font-medium text-slate-900">{managedActivation.deviceLabel || 'n/a'}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Status</dt><dd className="font-medium text-slate-900">{managedActivation.status}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Last reason code</dt><dd className="font-medium text-slate-900">{managedActivation.lastReasonCode || 'n/a'}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Grace until</dt><dd className="font-medium text-slate-900">{managedActivation.graceUntil ? new Date(managedActivation.graceUntil).toLocaleString() : 'n/a'}</dd></div>
                            </dl>
                        </article>

                        <article className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <h2 className="text-xl font-semibold tracking-tight text-slate-900">Confirm new binding</h2>
                            {can.rebind ? (
                                <form className="mt-6 space-y-4" onSubmit={submit}>
                                    <label className="block">
                                        <span className="mb-2 block text-sm font-medium text-slate-700">New machine ID</span>
                                        <input type="text" value={form.data.machine_id} onChange={(event) => form.setData('machine_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" required />
                                        {form.errors.machine_id ? <p className="mt-2 text-sm text-rose-600">{form.errors.machine_id}</p> : null}
                                    </label>
                                    <label className="block">
                                        <span className="mb-2 block text-sm font-medium text-slate-700">New installation ID</span>
                                        <input type="text" value={form.data.installation_id} onChange={(event) => form.setData('installation_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" required />
                                        {form.errors.installation_id ? <p className="mt-2 text-sm text-rose-600">{form.errors.installation_id}</p> : null}
                                    </label>
                                    <label className="block">
                                        <span className="mb-2 block text-sm font-medium text-slate-700">Device label</span>
                                        <input type="text" value={form.data.device_label} onChange={(event) => form.setData('device_label', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" />
                                        {form.errors.device_label ? <p className="mt-2 text-sm text-rose-600">{form.errors.device_label}</p> : null}
                                    </label>
                                    <button type="submit" disabled={form.processing} className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60">Confirm rebind</button>
                                </form>
                            ) : (
                                <p className="mt-6 text-sm text-slate-500">Your role does not allow manual rebind actions.</p>
                            )}
                        </article>
                    </section>
                </div>
            </main>
        </>
    );
}
