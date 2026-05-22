import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Sign In" />

            {status && (
                <div className="mb-4 text-sm font-semibold text-emerald-400 bg-emerald-950/30 border border-emerald-900/50 rounded-lg p-3">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <InputLabel 
                        htmlFor="email" 
                        value="Administrator Email" 
                        className="block text-xs font-bold uppercase tracking-wider text-slate-400"
                    />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        placeholder="admin@license.com"
                        className="mt-1.5 block w-full bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 rounded-lg px-3.5 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none transition-all duration-200"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-1.5 text-xs text-rose-400" />
                </div>

                <div>
                    <div className="flex items-center justify-between">
                        <InputLabel 
                            htmlFor="password" 
                            value="Secure Password" 
                            className="block text-xs font-bold uppercase tracking-wider text-slate-400"
                        />
                        {canResetPassword && (
                            <Link
                                href={route('password.request')}
                                className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 focus:outline-none transition-all"
                            >
                                Forgot password?
                            </Link>
                        )}
                    </div>

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        placeholder="••••••••••••"
                        className="mt-1.5 block w-full bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-700 rounded-lg px-3.5 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none transition-all duration-200"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-1.5 text-xs text-rose-400" />
                </div>

                <div className="flex items-center justify-between pt-1">
                    <label className="flex items-center cursor-pointer select-none">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData('remember', e.target.checked)
                            }
                            className="rounded bg-slate-950 border-slate-800 text-indigo-650 focus:ring-indigo-600 focus:ring-offset-slate-900 h-4 w-4"
                        />
                        <span className="ms-2 text-xs font-semibold text-slate-400 hover:text-slate-350 transition-all">
                            Remember session
                        </span>
                    </label>
                </div>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full inline-flex items-center justify-center rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/25 hover:shadow-indigo-500/35 transition-all duration-200 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 focus:ring-offset-slate-900 border-none"
                    >
                        {processing ? 'Signing In...' : 'Sign In to Dashboard'}
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}
