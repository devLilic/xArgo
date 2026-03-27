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

type RoleOption = {
    value: string;
    label: string;
};

type ShowUserProps = {
    managedUser: ManagedUser;
    availableRoles: RoleOption[];
    status?: string | null;
    can: {
        updateRole: boolean;
        toggleActive: boolean;
    };
};

export default function ShowUser({
    managedUser,
    availableRoles,
    status = null,
    can,
}: ShowUserProps) {
    const roleForm = useForm({
        role: managedUser.role,
    });

    const activityForm = useForm({
        active: managedUser.isActive,
    });

    const submitRole = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        roleForm.patch(route('admin.users.role.update', managedUser.id));
    };

    const toggleActivity = () => {
        activityForm.transform(() => ({
            active: !managedUser.isActive,
        }));

        activityForm.patch(route('admin.users.activity.update', managedUser.id));
    };

    return (
        <>
            <Head title={managedUser.name} />

            <main className="min-h-screen px-6 py-10">
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <section className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                        <div className="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--color-accent)]">
                                    User Detail
                                </p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                                    {managedUser.name}
                                </h1>
                                <p className="mt-2 text-sm text-slate-600">{managedUser.email}</p>
                            </div>

                            <div className="flex gap-3">
                                <span
                                    className={`inline-flex rounded-full px-3 py-2 text-xs font-semibold ${
                                        managedUser.isActive
                                            ? 'bg-emerald-100 text-emerald-800'
                                            : 'bg-amber-100 text-amber-800'
                                    }`}
                                >
                                    {managedUser.isActive ? 'active' : 'inactive'}
                                </span>
                                <Link
                                    href={route('admin.users.index')}
                                    className="inline-flex rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900"
                                >
                                    Back to users
                                </Link>
                            </div>
                        </div>
                    </section>

                    {status ? (
                        <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {status}
                        </div>
                    ) : null}

                    <section className="grid gap-6 lg:grid-cols-[1fr_1fr]">
                        <article className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <h2 className="text-xl font-semibold tracking-tight text-slate-900">
                                Account summary
                            </h2>
                            <dl className="mt-6 space-y-4 text-sm">
                                <div className="flex items-center justify-between gap-4">
                                    <dt className="text-slate-500">Role</dt>
                                    <dd className="font-medium text-slate-900">{managedUser.role}</dd>
                                </div>
                                <div className="flex items-center justify-between gap-4">
                                    <dt className="text-slate-500">Created</dt>
                                    <dd className="font-medium text-slate-900">
                                        {managedUser.createdAt ? new Date(managedUser.createdAt).toLocaleString() : 'n/a'}
                                    </dd>
                                </div>
                                <div className="flex items-center justify-between gap-4">
                                    <dt className="text-slate-500">Deactivated at</dt>
                                    <dd className="font-medium text-slate-900">
                                        {managedUser.deactivatedAt ? new Date(managedUser.deactivatedAt).toLocaleString() : 'n/a'}
                                    </dd>
                                </div>
                            </dl>
                        </article>

                        <article className="rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-panel)] p-8 shadow-[0_24px_80px_rgba(19,34,56,0.08)]">
                            <h2 className="text-xl font-semibold tracking-tight text-slate-900">
                                Access controls
                            </h2>

                            {can.updateRole ? (
                                <form className="mt-6 space-y-4" onSubmit={submitRole}>
                                    <label className="block">
                                        <span className="mb-2 block text-sm font-medium text-slate-700">
                                            Role
                                        </span>
                                        <select
                                            value={roleForm.data.role}
                                            onChange={(event) => roleForm.setData('role', event.target.value)}
                                            className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600"
                                        >
                                            {availableRoles.map((role) => (
                                                <option key={role.value} value={role.value}>
                                                    {role.label}
                                                </option>
                                            ))}
                                        </select>
                                    </label>
                                    {roleForm.errors.role ? (
                                        <p className="text-sm text-rose-600">{roleForm.errors.role}</p>
                                    ) : null}
                                    <button
                                        type="submit"
                                        disabled={roleForm.processing}
                                        className="inline-flex rounded-full bg-[var(--color-accent)] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        Change role
                                    </button>
                                </form>
                            ) : (
                                <p className="mt-6 text-sm text-slate-500">
                                    Your role does not allow role changes.
                                </p>
                            )}

                            <div className="mt-8 border-t border-slate-200 pt-6">
                                {can.toggleActive ? (
                                    <button
                                        type="button"
                                        onClick={toggleActivity}
                                        disabled={activityForm.processing}
                                        className={`inline-flex rounded-full px-5 py-3 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60 ${
                                            managedUser.isActive
                                                ? 'bg-amber-600 hover:bg-amber-700'
                                                : 'bg-emerald-700 hover:bg-emerald-800'
                                        }`}
                                    >
                                        {managedUser.isActive ? 'Deactivate user' : 'Reactivate user'}
                                    </button>
                                ) : (
                                    <p className="text-sm text-slate-500">
                                        Your role does not allow account activation changes.
                                    </p>
                                )}
                            </div>
                        </article>
                    </section>
                </div>
            </main>
        </>
    );
}
