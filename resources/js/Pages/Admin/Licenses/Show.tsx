import { Head, Link, router } from '@inertiajs/react';

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
    lastValidatedAt: string | null;
    deletedAt: string | null;
    createdAt: string | null;
    app: { id: number; name: string; appId: string };
    plan: { id: number; name: string; code: string };
    activations: {
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
    }[];
};

type Props = {
    managedLicense: ManagedLicense;
    status?: string | null;
    can: { update: boolean; changeStatus: boolean; delete: boolean; restore: boolean; rebindActivation: boolean };
};

export default function ShowLicense({ managedLicense, status = null, can }: Props) {
    const updateStatus = (statusAction: 'suspend' | 'revoke' | 'reactivate') => {
        router.patch(route('admin.licenses.status.update', managedLicense.id), { status_action: statusAction });
    };

    return (
        <>
            <Head title={managedLicense.licenseKey} />
            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">License Detail</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{managedLicense.licenseKey}</h1>
                                <p className="mt-2 text-sm text-slate-600">{managedLicense.publicKey}</p>
                            </div>
                            <div className="flex gap-3">
                                <span className={`inline-flex rounded-full px-3 py-2 text-xs font-semibold ${managedLicense.deletedAt ? 'bg-slate-200 text-slate-700' : managedLicense.status === 'active' ? 'bg-emerald-100 text-emerald-800' : managedLicense.status === 'suspended' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'}`}>{managedLicense.deletedAt ? 'archived' : managedLicense.status}</span>
                                <Link href={route('admin.licenses.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Back to licenses</Link>
                            </div>
                        </div>
                    </section>

                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}

                    <section className="grid gap-6 lg:grid-cols-[1fr_1fr]">
                        <article className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <h2 className="text-xl font-semibold tracking-tight text-slate-900">License summary</h2>
                            <dl className="mt-6 space-y-4 text-sm">
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Application</dt><dd className="font-medium text-slate-900">{managedLicense.app.name}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Plan</dt><dd className="font-medium text-slate-900">{managedLicense.plan.name}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Customer</dt><dd className="font-medium text-slate-900">{managedLicense.customerEmail || managedLicense.customerName || 'n/a'}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Max devices</dt><dd className="font-medium text-slate-900">{managedLicense.maxDevices}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Grace hours</dt><dd className="font-medium text-slate-900">{managedLicense.graceHours}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Expires</dt><dd className="font-medium text-slate-900">{managedLicense.expiresAt ? new Date(managedLicense.expiresAt).toLocaleString() : 'never'}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Last validated</dt><dd className="font-medium text-slate-900">{managedLicense.lastValidatedAt ? new Date(managedLicense.lastValidatedAt).toLocaleString() : 'n/a'}</dd></div>
                                <div className="flex items-center justify-between gap-4"><dt className="text-slate-500">Created</dt><dd className="font-medium text-slate-900">{managedLicense.createdAt ? new Date(managedLicense.createdAt).toLocaleString() : 'n/a'}</dd></div>
                            </dl>
                            <div className="mt-6 rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">{managedLicense.notes || 'No internal notes.'}</div>
                        </article>

                        <article className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <h2 className="text-xl font-semibold tracking-tight text-slate-900">Management</h2>
                            <div className="mt-6 flex flex-wrap gap-3">
                                {can.update ? <Link href={route('admin.licenses.edit', managedLicense.id)} className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95">Edit license</Link> : null}
                                {can.changeStatus && !managedLicense.deletedAt ? <button type="button" onClick={() => updateStatus('reactivate')} className="inline-flex rounded-full border border-emerald-300 px-5 py-3 text-sm font-semibold text-emerald-800 transition hover:border-emerald-500">Reactivate</button> : null}
                                {can.changeStatus && !managedLicense.deletedAt ? <button type="button" onClick={() => updateStatus('suspend')} className="inline-flex rounded-full border border-amber-300 px-5 py-3 text-sm font-semibold text-amber-800 transition hover:border-amber-500">Suspend</button> : null}
                                {can.changeStatus && !managedLicense.deletedAt ? <button type="button" onClick={() => updateStatus('revoke')} className="inline-flex rounded-full border border-rose-300 px-5 py-3 text-sm font-semibold text-rose-800 transition hover:border-rose-500">Revoke</button> : null}
                                {can.delete ? <button type="button" onClick={() => router.delete(route('admin.licenses.destroy', managedLicense.id))} className="inline-flex rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-800 transition hover:border-slate-500">Archive</button> : null}
                                {can.restore ? <button type="button" onClick={() => router.patch(route('admin.licenses.restore', managedLicense.id))} className="inline-flex rounded-full border border-sky-300 px-5 py-3 text-sm font-semibold text-sky-800 transition hover:border-sky-500">Restore</button> : null}
                            </div>
                        </article>
                    </section>

                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex items-center justify-between gap-4">
                            <div>
                                <h2 className="text-xl font-semibold tracking-tight text-slate-900">Device activations</h2>
                                <p className="mt-2 text-sm text-slate-600">Review current device bindings and manually rebind when an installation must be moved.</p>
                            </div>
                        </div>
                        <div className="mt-6 overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                        <th className="px-4 py-3 font-medium">Activation</th>
                                        <th className="px-4 py-3 font-medium">Device</th>
                                        <th className="px-4 py-3 font-medium">Status</th>
                                        <th className="px-4 py-3 font-medium">Last seen</th>
                                        <th className="px-4 py-3 font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {managedLicense.activations.map((activation) => (
                                        <tr key={activation.id} className="text-sm text-slate-700">
                                            <td className="px-4 py-3">
                                                <div className="font-medium text-slate-900">{activation.activationId}</div>
                                                <div className="text-slate-500">{activation.installationId}</div>
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="font-medium text-slate-900">{activation.deviceLabel || 'Unnamed device'}</div>
                                                <div className="text-slate-500">{activation.machineId}</div>
                                            </td>
                                            <td className="px-4 py-3">
                                                <div>{activation.status}</div>
                                                <div className="text-slate-500">{activation.lastReasonCode || 'no reason code'}</div>
                                            </td>
                                            <td className="px-4 py-3 text-slate-500">
                                                {activation.lastSeenAt ? new Date(activation.lastSeenAt).toLocaleString() : 'n/a'}
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                {can.rebindActivation ? (
                                                    <Link
                                                        href={route('admin.licenses.activations.rebind.edit', [managedLicense.id, activation.id])}
                                                        className="text-sm font-medium text-[var(--color-accent)] hover:underline"
                                                    >
                                                        Review rebind
                                                    </Link>
                                                ) : null}
                                            </td>
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
