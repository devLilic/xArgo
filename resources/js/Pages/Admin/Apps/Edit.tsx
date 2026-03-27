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

type EditAppProps = {
    managedApp: ManagedApp;
    status?: string | null;
    can: {
        update: boolean;
    };
};

export default function EditApp({ managedApp, status = null, can }: EditAppProps) {
    const form = useForm({
        name: managedApp.name,
        slug: managedApp.slug,
        app_id: managedApp.appId,
        is_active: managedApp.isActive,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(route('admin.apps.update', managedApp.id));
    };

    return (
        <>
            <Head title={managedApp.name} />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">Application Detail</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{managedApp.name}</h1>
                                <p className="mt-2 text-sm text-slate-600">{managedApp.appId}</p>
                            </div>

                            <div className="flex gap-3">
                                <Link href={route('admin.plans.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                    Manage plans
                                </Link>
                                <Link href={route('admin.apps.index')} className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                    Back to apps
                                </Link>
                            </div>
                        </div>
                    </section>

                    {status ? <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{status}</div> : null}

                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        {can.update ? (
                            <form className="grid gap-4 md:grid-cols-2" onSubmit={submit}>
                                <label className="block">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">Name</span>
                                    <input type="text" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" required />
                                    {form.errors.name ? <p className="mt-2 text-sm text-rose-600">{form.errors.name}</p> : null}
                                </label>
                                <label className="block">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">Slug</span>
                                    <input type="text" value={form.data.slug} onChange={(event) => form.setData('slug', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" required />
                                    {form.errors.slug ? <p className="mt-2 text-sm text-rose-600">{form.errors.slug}</p> : null}
                                </label>
                                <label className="block md:col-span-2">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">App ID</span>
                                    <input type="text" value={form.data.app_id} onChange={(event) => form.setData('app_id', event.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600" required />
                                    {form.errors.app_id ? <p className="mt-2 text-sm text-rose-600">{form.errors.app_id}</p> : null}
                                </label>
                                <label className="inline-flex items-center gap-3 text-sm font-medium text-slate-700 md:col-span-2">
                                    <input type="checkbox" checked={form.data.is_active} onChange={(event) => form.setData('is_active', event.target.checked)} className="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600" />
                                    Application is active
                                </label>
                                <div className="md:col-span-2">
                                    <button type="submit" disabled={form.processing} className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60">
                                        Save application
                                    </button>
                                </div>
                            </form>
                        ) : (
                            <p className="text-sm text-slate-500">Your role does not allow application changes.</p>
                        )}
                    </section>
                </div>
            </main>
        </>
    );
}
