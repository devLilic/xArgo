import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ appName, environment, user }) {
    return (
        <>
            <Head title="Licensing Server" />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-6xl flex-col gap-6">
                    <section className="overflow-hidden rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] shadow-[0_24px_80px_rgba(19,34,56,0.10)]">
                        <div className="grid gap-8 px-8 py-10 lg:grid-cols-[1.4fr_0.8fr] lg:px-12">
                            <div className="space-y-5">
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">
                                    Internal Platform
                                </p>
                                <div className="space-y-3">
                                    <h1 className="max-w-3xl text-4xl font-semibold tracking-tight lg:text-6xl">
                                        {appName} licensing server foundation
                                    </h1>
                                    <p className="max-w-2xl text-base leading-7 text-slate-600">
                                        Laravel, Inertia, React, Vite, and Tailwind are wired for an
                                        internal admin panel and synchronous licensing APIs.
                                    </p>
                                    <p className="text-sm text-slate-500">
                                        Signed in as {user?.email}
                                    </p>
                                </div>
                            </div>

                            <div className="rounded-[1.5rem] border border-[var(--color-border)] bg-slate-950 p-6 text-slate-100">
                                <p className="text-xs uppercase tracking-[0.3em] text-emerald-300">
                                    Runtime
                                </p>
                                <dl className="mt-5 space-y-4 text-sm">
                                    <div className="flex items-center justify-between gap-4">
                                        <dt className="text-slate-400">Environment</dt>
                                        <dd className="font-medium">{environment}</dd>
                                    </div>
                                    <div className="flex items-center justify-between gap-4">
                                        <dt className="text-slate-400">Frontend</dt>
                                        <dd className="font-medium">Inertia + React</dd>
                                    </div>
                                    <div className="flex items-center justify-between gap-4">
                                        <dt className="text-slate-400">Queues</dt>
                                        <dd className="font-medium">Database-ready</dd>
                                    </div>
                                    <div className="flex items-center justify-between gap-4">
                                        <dt className="text-slate-400">Deployment</dt>
                                        <dd className="font-medium">Shared hosting safe</dd>
                                    </div>
                                </dl>
                                <div className="mt-6">
                                    <Link
                                        as="button"
                                        href={route('logout')}
                                        method="post"
                                        className="inline-flex rounded-full border border-emerald-400/30 px-4 py-2 text-sm font-medium text-emerald-200 transition hover:border-emerald-300 hover:text-white"
                                    >
                                        Sign out
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </>
    );
}
