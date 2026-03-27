import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type AcceptInvitationProps = {
    invitation: {
        id: number;
        email: string;
        expiresAt: string;
        token: string;
    };
};

export default function AcceptInvitation({ invitation }: AcceptInvitationProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: invitation.token,
        name: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        post(route('invitations.activate', {
            invitation: invitation.id,
        }), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Accept Invitation" />

            <div className="mb-6 space-y-2">
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">
                    Activate your internal account
                </h1>
                <p className="text-sm leading-6 text-slate-600">
                    You are activating access for <strong>{invitation.email}</strong>. This
                    invitation expires on {new Date(invitation.expiresAt).toLocaleString()}.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="name" value="Full name" />
                    <TextInput
                        id="name"
                        type="text"
                        name="name"
                        value={data.name}
                        className="mt-1 block w-full"
                        autoComplete="name"
                        isFocused
                        onChange={(event) => setData('name', event.target.value)}
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={invitation.email}
                        className="mt-1 block w-full bg-slate-100"
                        disabled
                    />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(event) => setData('password', event.target.value)}
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password_confirmation" value="Confirm password" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(event) => setData('password_confirmation', event.target.value)}
                    />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <PrimaryButton className="w-full justify-center" disabled={processing}>
                    Activate account
                </PrimaryButton>
            </form>
        </GuestLayout>
    );
}
