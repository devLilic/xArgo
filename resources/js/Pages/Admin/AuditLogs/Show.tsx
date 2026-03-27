import { Head, Link } from '@inertiajs/react';

type AuditLogDetail = {
    id: number;
    action: string;
    entityType: string;
    entityId: number | null;
    createdAt: string | null;
    updatedAt: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    metaJson: Record<string, unknown>;
};

type Props = {
    auditLog: AuditLogDetail;
    can: {
        viewIndex: boolean;
    };
    status?: string | null;
};

export default function AuditLogShow({ auditLog, can, status = null }: Props) {
    return (
        <>
            <Head title={`Audit Log ${auditLog.id}`} />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Audit Detail</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{auditLog.action}</h1>
                                <p className="mt-2 text-sm text-slate-600">Inspect the full actor and metadata payload for this recorded admin action.</p>
                            </div>
                            <div className="flex gap-3">
                                {can.viewIndex ? <Link href={route('admin.audit-logs.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Back to audit logs</Link> : null}
                                <Link href={route('admin.dashboard')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Dashboard</Link>
                            </div>
                        </div>
                    </section>

                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}

                    <section className="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                        <div className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <h2 className="text-lg font-semibold text-slate-900">Event summary</h2>
                            <dl className="mt-6 space-y-4 text-sm">
                                <div className="flex items-start justify-between gap-4"><dt className="text-slate-500">Actor</dt><dd className="text-right text-slate-900">{auditLog.user ? `${auditLog.user.name} (${auditLog.user.email})` : 'System'}</dd></div>
                                <div className="flex items-start justify-between gap-4"><dt className="text-slate-500">Action</dt><dd className="text-right text-slate-900">{auditLog.action}</dd></div>
                                <div className="flex items-start justify-between gap-4"><dt className="text-slate-500">Entity</dt><dd className="text-right text-slate-900">{auditLog.entityType}</dd></div>
                                <div className="flex items-start justify-between gap-4"><dt className="text-slate-500">Entity ID</dt><dd className="text-right text-slate-900">{auditLog.entityId ?? 'n/a'}</dd></div>
                                <div className="flex items-start justify-between gap-4"><dt className="text-slate-500">Created at</dt><dd className="text-right text-slate-900">{auditLog.createdAt ? new Date(auditLog.createdAt).toLocaleString() : 'n/a'}</dd></div>
                                <div className="flex items-start justify-between gap-4"><dt className="text-slate-500">Updated at</dt><dd className="text-right text-slate-900">{auditLog.updatedAt ? new Date(auditLog.updatedAt).toLocaleString() : 'n/a'}</dd></div>
                            </dl>
                        </div>

                        <div className="rounded-[2rem] border border-[var(--color-border)] bg-slate-950 p-8 shadow-[0_24px_80px_rgba(19,34,56,0.12)]">
                            <h2 className="text-lg font-semibold text-white">Metadata</h2>
                            <pre className="mt-6 overflow-x-auto rounded-2xl border border-white/10 bg-black/30 p-4 text-sm leading-6 text-slate-200">{JSON.stringify(auditLog.metaJson, null, 2)}</pre>
                        </div>
                    </section>
                </div>
            </main>
        </>
    );
}
