import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type ForgotPasswordProps = {
    status?: string | null;
};

export default function ForgotPassword({ status = null }: ForgotPasswordProps) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Forgot Password" />

            <div className="mb-4 text-sm text-gray-600">
                Forgot your password? No problem. Just let us know your email
                address and we will email you a password reset link that will
                allow you to choose a new one.
            </div>

            {status ? (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            ) : null}

            <form onSubmit={submit}>
                <TextInput
                    id="email"
                    type="email"
                    name="email"
                    value={data.email}
                    className="mt-1 block w-full"
                    isFocused
                    onChange={(event) => setData('email', event.target.value)}
                />

                <InputError message={errors.email} className="mt-2" />

                <div className="mt-4 flex items-center justify-end">
                    <PrimaryButton className="ms-4" disabled={processing}>
                        Email Password Reset Link
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
