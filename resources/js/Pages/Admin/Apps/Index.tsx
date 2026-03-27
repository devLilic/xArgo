import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type ManagedApp = {
    id: number;
    name: string;
    slug: string;
    appId: string;
    isActive: boolean;
    createdAt: string | null;
};

type AppsIndexProps = {
    apps: ManagedApp[];
    status?: string | null;
    can: {
        create: boolean;
    };
};

export default function AppsIndex({ apps, status = null, can }: AppsIndexProps) {
    const form = useForm({
        name: '',
        slug: '',
        app_id: '',
        is_active: true,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(route('admin.apps.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <>
            <Head title="Applications" />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-6xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">
                                    Application Admin
                                </p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                                    Managed applications
                                </h1>
                                <p className="mt-2 text-sm text-slate-600">
                                    Register Electron apps, maintain unique identifiers, and control whether each app is active.
                                </p>
                            </div>

                            <div className="flex gap-3">
                                <Link
                                    href={route('admin.plans.index')}
                                    className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900"
                                >
                                    Manage plans
                                </Link>
                                <Link
                                    href={route('admin.dashboard')}
                                    className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900"
                                >
                                    Back to dashboard
                                </Link>
                            </div>
                        </div>
                    </section>

                    {can.create ? (
                        <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <div className="max-w-3xl space-y-4">
                                <h2 className="text-xl font-semibold tracking-tight text-slate-900">
                                    Create application
                                </h2>
                                {status ? (
                                    <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                        {status}
                                    </div>
                                ) : null}
                                <form className="grid gap-4 md:grid-cols-2" onSubmit={submit}>
                                    <label className="block">
                                        <span className="mb-2 block text-sm font-medium text-slate-700">Name</span>
                                        <input type="text" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" placeholder="X Argo Desktop" required />
                                        {form.errors.name ? <p className="mt-2 text-sm text-rose-600">{form.errors.name}</p> : null}
                                    </label>
                                    <label className="block">
                                        <span className="mb-2 block text-sm font-medium text-slate-700">Slug</span>
                                        <input type="text" value={form.data.slug} onChange={(event) => form.setData('slug', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" placeholder="x-argo-desktop" required />
                                        {form.errors.slug ? <p className="mt-2 text-sm text-rose-600">{form.errors.slug}</p> : null}
                                    </label>
                                    <label className="block md:col-span-2">
                                        <span className="mb-2 block text-sm font-medium text-slate-700">App ID</span>
                                        <input type="text" value={form.data.app_id} onChange={(event) => form.setData('app_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" placeholder="com.xargo.desktop" required />
                                        {form.errors.app_id ? <p className="mt-2 text-sm text-rose-600">{form.errors.app_id}</p> : null}
                                    </label>
                                    <label className="inline-flex items-center gap-3 text-sm font-medium text-slate-700 md:col-span-2">
                                        <input type="checkbox" checked={form.data.is_active} onChange={(event) => form.setData('is_active', event.target.checked)} className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600" />
                                        Application is active
                                    </label>
                                    <div className="md:col-span-2">
                                        <button type="submit" disabled={form.processing} className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60">
                                            Create application
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </section>
                    ) : null}

                    <section className="overflow-hidden rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                        <th className="px-6 py-4 font-medium">Application</th>
                                        <th className="px-6 py-4 font-medium">App ID</th>
                                        <th className="px-6 py-4 font-medium">Status</th>
                                        <th className="px-6 py-4 font-medium">Created</th>
                                        <th className="px-6 py-4 font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {apps.map((app) => (
                                        <tr key={app.id} className="text-sm text-slate-700">
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-slate-900">{app.name}</div>
                                                <div className="text-slate-500">{app.slug}</div>
                                            </td>
                                            <td className="px-6 py-4">{app.appId}</td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${app.isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                                                    {app.isActive ? 'active' : 'inactive'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-slate-500">
                                                {app.createdAt ? new Date(app.createdAt).toLocaleDateString() : 'n/a'}
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <Link href={route('admin.apps.edit', app.id)} className="text-sm font-medium text-[var(--color-accent)] hover:underline">
                                                    Edit app
                                                </Link>
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
