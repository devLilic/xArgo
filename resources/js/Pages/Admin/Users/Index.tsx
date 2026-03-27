import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type ManagedUser = {
    id: number;
    name: string;
    email: string;
    role: string;
    isActive: boolean;
    deactivatedAt: string | null;
    createdAt: string | null;
};

type UsersIndexProps = {
    users: ManagedUser[];
    status?: string | null;
    can: {
        inviteUsers: boolean;
    };
};

export default function UsersIndex({
    users,
    status = null,
    can,
}: UsersIndexProps) {
    const form = useForm({
        email: '',
    });

    const submitInvitation = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.post(route('admin.invitations.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <>
            <Head title="Users" />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-6xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">
                                    User Admin
                                </p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                                    Internal users
                                </h1>
                                <p className="mt-2 text-sm text-slate-600">
                                    Review access, inspect individual accounts, and manage internal roles.
                                </p>
                            </div>

                            <Link
                                href={route('admin.dashboard')}
                                className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900"
                            >
                                Back to dashboard
                            </Link>
                        </div>
                    </section>

                    {can.inviteUsers ? (
                        <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <div className="max-w-2xl space-y-4">
                                <h2 className="text-xl font-semibold tracking-tight text-slate-900">
                                    Invite user
                                </h2>
                                {status ? (
                                    <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                        {status}
                                    </div>
                                ) : null}
                                <form className="space-y-3" onSubmit={submitInvitation}>
                                    <input
                                        type="email"
                                        value={form.data.email}
                                        onChange={(event) => form.setData('email', event.target.value)}
                                        className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600"
                                        placeholder="person@example.com"
                                        autoComplete="email"
                                        required
                                    />
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

                    <section className="overflow-hidden rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                        <th className="px-6 py-4 font-medium">User</th>
                                        <th className="px-6 py-4 font-medium">Role</th>
                                        <th className="px-6 py-4 font-medium">Status</th>
                                        <th className="px-6 py-4 font-medium">Created</th>
                                        <th className="px-6 py-4 font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {users.map((user) => (
                                        <tr key={user.id} className="text-sm text-slate-700">
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-slate-900">{user.name}</div>
                                                <div className="text-slate-500">{user.email}</div>
                                            </td>
                                            <td className="px-6 py-4">{user.role}</td>
                                            <td className="px-6 py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${
                                                        user.isActive
                                                            ? 'bg-emerald-100 text-emerald-800'
                                                            : 'bg-amber-100 text-amber-800'
                                                    }`}
                                                >
                                                    {user.isActive ? 'active' : 'inactive'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-slate-500">
                                                {user.createdAt ? new Date(user.createdAt).toLocaleDateString() : 'n/a'}
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <Link
                                                    href={route('admin.users.show', user.id)}
                                                    className="text-sm font-medium text-[var(--color-accent)] hover:underline"
                                                >
                                                    View user
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
