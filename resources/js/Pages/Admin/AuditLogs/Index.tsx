import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type ActorOption = {
    id: number;
    name: string;
    email: string;
};

type AuditLogRecord = {
    id: number;
    action: string;
    entityType: string;
    entityId: number | null;
    createdAt: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    metaPreview: Record<string, unknown>;
};

type Props = {
    auditLogs: AuditLogRecord[];
    actors: ActorOption[];
    filters: {
        user_id: number | null;
        action: string;
        entity_type: string;
        date_from: string;
        date_to: string;
    };
    status?: string | null;
};

export default function AuditLogIndex({ auditLogs, actors, filters, status = null }: Props) {
    const form = useForm({
        user_id: filters.user_id ? String(filters.user_id) : '',
        action: filters.action,
        entity_type: filters.entity_type,
        date_from: filters.date_from,
        date_to: filters.date_to,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        router.get(route('admin.audit-logs.index'), {
            user_id: form.data.user_id || undefined,
            action: form.data.action || undefined,
            entity_type: form.data.entity_type || undefined,
            date_from: form.data.date_from || undefined,
            date_to: form.data.date_to || undefined,
        }, { preserveState: true, replace: true });
    };

    const clear = () => {
        form.setData({
            user_id: '',
            action: '',
            entity_type: '',
            date_from: '',
            date_to: '',
        });

        router.get(route('admin.audit-logs.index'), {}, { preserveState: true, replace: true });
    };

    return (
        <>
            <Head title="Audit Logs" />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-7xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Audit Logs</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Sensitive admin activity history</h1>
                                <p className="mt-2 text-sm text-slate-600">Review who changed what, when it happened, and the captured metadata for each action.</p>
                            </div>
                            <div className="flex gap-3">
                                <Link href={route('admin.dashboard')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">Back to dashboard</Link>
                            </div>
                        </div>
                    </section>

                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}

                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <form className="grid gap-4 md:grid-cols-5" onSubmit={submit}>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Actor</span><select value={form.data.user_id} onChange={(event) => form.setData('user_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600"><option value="">All actors</option>{actors.map((actor) => <option key={actor.id} value={actor.id}>{actor.name} ({actor.email})</option>)}</select></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Action</span><input type="text" value={form.data.action} onChange={(event) => form.setData('action', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" placeholder="admin.license.updated" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Entity</span><input type="text" value={form.data.entity_type} onChange={(event) => form.setData('entity_type', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" placeholder="license" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Date from</span><input type="date" value={form.data.date_from} onChange={(event) => form.setData('date_from', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
                            <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">Date to</span><input type="date" value={form.data.date_to} onChange={(event) => form.setData('date_to', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" /></label>
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
                                        <th className="px-6 py-4 font-medium">When</th>
                                        <th className="px-6 py-4 font-medium">Actor</th>
                                        <th className="px-6 py-4 font-medium">Action</th>
                                        <th className="px-6 py-4 font-medium">Entity</th>
                                        <th className="px-6 py-4 font-medium">Metadata</th>
                                        <th className="px-6 py-4 font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {auditLogs.map((auditLog) => (
                                        <tr key={auditLog.id} className="text-sm text-slate-700">
                                            <td className="px-6 py-4 text-slate-500">{auditLog.createdAt ? new Date(auditLog.createdAt).toLocaleString() : 'n/a'}</td>
                                            <td className="px-6 py-4"><div className="font-medium text-slate-900">{auditLog.user?.name || 'System'}</div><div className="text-slate-500">{auditLog.user?.email || 'No user attached'}</div></td>
                                            <td className="px-6 py-4 font-medium text-slate-900">{auditLog.action}</td>
                                            <td className="px-6 py-4"><div className="font-medium text-slate-900">{auditLog.entityType}</div><div className="text-slate-500">{auditLog.entityId ?? 'n/a'}</div></td>
                                            <td className="px-6 py-4 text-slate-500">{Object.keys(auditLog.metaPreview).slice(0, 3).join(', ') || 'No metadata'}</td>
                                            <td className="px-6 py-4 text-right"><Link href={route('admin.audit-logs.show', auditLog.id)} className="text-sm font-medium text-[var(--color-accent)] hover:underline">View details</Link></td>
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
