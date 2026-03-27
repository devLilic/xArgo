import { FormEvent, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

type DashboardProps = {
    appName: string;
    environment: string;
    invitationStatus?: string | null;
    can: {
        inviteUsers: boolean;
        viewUsers: boolean;
        viewApps: boolean;
        viewPlans: boolean;
        viewLicenses: boolean;
        viewHeartbeats: boolean;
        viewAuditLogs: boolean;
    };
    user?: {
        name: string;
        email: string;
        role: string;
    } | null;
};

export default function Dashboard({
    appName,
    environment,
    invitationStatus = null,
    can,
    user = null,
}: DashboardProps) {
    const [emailValue, setEmailValue] = useState('');
    const form = useForm({
        email: '',
    });

    const submitInvitation = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform(() => ({
            email: emailValue,
        }));

        form.post(route('admin.invitations.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setEmailValue('');
                form.reset();
            },
        });
    };

    return (
        <>
            <Head title="Licensing Server" />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-6xl flex-col gap-6">
                    <section className="overflow-hidden rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] shadow-[0_24px_80px_rgba(19,34,56,0.10)]">
                        <div className="grid gap-8 px-8 py-10 lg:grid-cols-[1.15fr_0.85fr] lg:px-12">
                            <div className="space-y-5">
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">
                                    Internal Platform
                                </p>
                                <div className="space-y-3">
                                    <h1 className="max-w-3xl text-4xl font-semibold tracking-tight lg:text-6xl">
                                        {appName} licensing server foundation
                                    </h1>
                                    <p className="max-w-2xl text-base leading-7 text-slate-600">
                                        Internal authentication is active, public registration is closed,
                                        and team access is provisioned through expiring invitations.
                                    </p>
                                    <p className="text-sm text-slate-500">
                                        Signed in as {user?.email} ({user?.role})
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
                                    {can.viewUsers ? (
                                        <Link
                                            href={route('admin.users.index')}
                                            className="mr-3 inline-flex rounded-full border border-white/10 px-4 py-2 text-sm font-medium text-white transition hover:border-white/30"
                                        >
                                            Manage users
                                        </Link>
                                    ) : null}
                                    {can.viewApps ? (
                                        <Link
                                            href={route('admin.apps.index')}
                                            className="mr-3 inline-flex rounded-full border border-white/10 px-4 py-2 text-sm font-medium text-white transition hover:border-white/30"
                                        >
                                            Manage apps
                                        </Link>
                                    ) : null}
                                    {can.viewPlans ? (
                                        <Link
                                            href={route('admin.plans.index')}
                                            className="mr-3 inline-flex rounded-full border border-white/10 px-4 py-2 text-sm font-medium text-white transition hover:border-white/30"
                                        >
                                            Manage plans
                                        </Link>
                                    ) : null}
                                    {can.viewLicenses ? (
                                        <Link
                                            href={route('admin.licenses.index')}
                                            className="mr-3 inline-flex rounded-full border border-white/10 px-4 py-2 text-sm font-medium text-white transition hover:border-white/30"
                                        >
                                            Manage licenses
                                        </Link>
                                    ) : null}
                                    {can.viewHeartbeats ? (
                                        <Link
                                            href={route('admin.heartbeats.index')}
                                            className="mr-3 inline-flex rounded-full border border-white/10 px-4 py-2 text-sm font-medium text-white transition hover:border-white/30"
                                        >
                                            View heartbeats
                                        </Link>
                                    ) : null}
                                    {can.viewAuditLogs ? (
                                        <Link
                                            href={route('admin.audit-logs.index')}
                                            className="mr-3 inline-flex rounded-full border border-white/10 px-4 py-2 text-sm font-medium text-white transition hover:border-white/30"
                                        >
                                            View audit logs
                                        </Link>
                                    ) : null}
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

                    {can.inviteUsers ? (
                        <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="max-w-2xl space-y-4">
                            <div className="space-y-2">
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">
                                    Invite Team Member
                                </p>
                                <h2 className="text-2xl font-semibold tracking-tight">
                                    Send an expiring onboarding link
                                </h2>
                                <p className="text-sm leading-6 text-slate-600">
                                    Invitations are email-bound, tokenized, and expire automatically.
                                    The invited user will set their password during activation.
                                </p>
                            </div>

                            {invitationStatus ? (
                                <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                    {invitationStatus}
                                </div>
                            ) : null}

                            <form className="space-y-3" onSubmit={submitInvitation}>
                                <label className="block">
                                    <span className="mb-2 block text-sm font-medium text-slate-700">
                                        Invitee email
                                    </span>
                                    <input
                                        type="email"
                                        value={emailValue}
                                        onChange={(event) => setEmailValue(event.target.value)}
                                        className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600"
                                        placeholder="person@example.com"
                                        autoComplete="email"
                                        required
                                    />
                                </label>

                                {form.errors.email ? (
                                    <p className="text-sm text-rose-600">{form.errors.email}</p>
                                ) : null}

                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    Send invitation
                                </button>
                            </form>
                        </div>
                        </section>
                    ) : null}
                </div>
            </main>
        </>
    );
}
