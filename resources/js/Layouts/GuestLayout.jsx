import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { Head } from '@inertiajs/react';

export default function GuestLayout({ children, title }) {
    return (
        <>
            <Head title={title} />
            {children}
        </>
    );
}
