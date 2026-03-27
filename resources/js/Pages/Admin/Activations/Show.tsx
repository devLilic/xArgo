import { Head, Link } from '@inertiajs/react';

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
    managedActivation: ManagedActivation;
    status?: string | null;
    can: { rebind: boolean };
};

export default function ShowActivation({ managedActivation, status = null, can }: Props) {
    return (
        <>
            <Head title={managedActivation.activationId} />
            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Activation Detail</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{managedActivation.activationId}</h1>
                                <p className="mt-2 text-sm text-slate-600">{managedActivation.license.licenseKey}</p>
                            </div>
                            <div className="flex gap-3">
                                {can.rebind ? <Link href={route('admin.licenses.activations.rebind.edit', [managedActivation.license.id, managedActivation.id])} className="inline-flex rounded-full bg-[var(--color-accent)] px-4 py-2 text-sm font-semibold text-white transition hover:brightness-95">Review rebind</Link> : null}
                                <Link href={route('admin.activations.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Back to activations</Link>
                            </div>
                        </div>
                    </section>

                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}

                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <dl className="grid gap-4 md:grid-cols-2 text-sm">
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">Machine ID</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.machineId}</dd></div>
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">Installation ID</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.installationId}</dd></div>
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">Device label</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.deviceLabel || 'n/a'}</dd></div>
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">Status</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.status}</dd></div>
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">Reason code</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.lastReasonCode || 'n/a'}</dd></div>
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">Grace until</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.graceUntil ? new Date(managedActivation.graceUntil).toLocaleString() : 'n/a'}</dd></div>
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">First seen</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.firstSeenAt ? new Date(managedActivation.firstSeenAt).toLocaleString() : 'n/a'}</dd></div>
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">Last seen</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.lastSeenAt ? new Date(managedActivation.lastSeenAt).toLocaleString() : 'n/a'}</dd></div>
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">Application</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.app.name} ({managedActivation.app.appId})</dd></div>
                            <div className="rounded-2xl bg-slate-50 p-4"><dt className="text-slate-500">Public key</dt><dd className="mt-1 font-medium text-slate-900">{managedActivation.license.publicKey}</dd></div>
                        </dl>
                    </section>
                </div>
            </main>
        </>
    );
}
