import ApplicationLogo from '@/Components/ApplicationLogo';
import NavlinkResponsive from '@/Components/NavlinkResponsive';
import { IconAlertCircle,
    IconBook,
    IconBooks,
    IconBrandProducthunt,
    IconBuildingCommunity,
    IconCategory,
    IconChartDots2,
    IconCircleKey,
    IconCreditCardPay,
    IconCreditCardRefund,
    IconDashboard,
    IconKeyframe,
    IconLayoutKanban,
    IconLogout,
    IconMoneybag,
    IconRoute,
    IconSettingsExclamation,
    IconStack3,
    IconUser,
    IconUsersGroup,
    IconVersions, } from '@tabler/icons-react';
import React from 'react'

export default function SidebarResponsive({ url, auth }) {
  return (
    <nav className="grid gap-6 text-lg font-medium">
            <ApplicationLogo />
            <nav className="grid items-start text-sm font-semibold lg:px-4">
                {auth.role.some((role) => ['admin', 'operator', 'member'].includes(role)) && (
                    <>
                        {/* Nav Menu Dashboard */}
                        <div className="px-3 py-2 text-sm font-semibold text-foreground">Dashboard</div>
                        <NavlinkResponsive
                            url={route('dashboard')}
                            active={url.startsWith('/dashboard')}
                            title="Dashboard"
                            icon={IconDashboard}
                        />
                    </>
                )}

                {auth.role.some((role) => ['admin', 'accounting'].includes(role)) && (
                    <>
                        {/* Nav Menu Statistik */}
                        <div className="px-3 py-2 text-sm font-semibold text-foreground">Statistik</div>
                        <NavlinkResponsive
                            url={route('admin.loan-statistics.index')}
                            active={url.startsWith('/admin/loan-statistics')}
                            title="Statistik Peminjaman"
                            icon={IconChartDots2}
                        />
                        <NavlinkResponsive
                            url={route('admin.fine-reports.index')}
                            active={url.startsWith('/admin/fine-reports')}
                            title="Laporan Denda"
                            icon={IconMoneybag}
                        />
                        <NavlinkResponsive
                            url={route('admin.product-stock-reports.index')}
                            active={url.startsWith('/admin/product-stock-reports')}
                            title="Laporan Stok Barang"
                            icon={IconStack3}
                        />
                    </>
                )}

                {auth.role.some((role) => ['operator'].includes(role)) && (
                    <>
                        {/* Nav Menu Master */}
                        <div className="px-3 py-2 text-sm font-semibold text-foreground">Master</div>
                        <NavlinkResponsive
                            url={route('admin.categories.index')}
                            active={url.startsWith('/admin/categories')}
                            title="Kategori"
                            icon={IconCategory}
                        />
                        <NavlinkResponsive
                            url={route('admin.brands.index')}
                            active={url.startsWith('/admin/brands')}
                            title="Brands"
                            icon={IconBuildingCommunity}
                        />
                        <NavlinkResponsive
                            url={route('admin.products.index')}
                            active={url.startsWith('/admin/products')}
                            title="Produk"
                            icon={IconBrandProducthunt}
                        />
                        <NavlinkResponsive
                            url={route('admin.users.index')}
                            active={url.startsWith('/admin/users')}
                            title="Pengguna"
                            icon={IconUsersGroup}
                        />
                        <NavlinkResponsive
                            url={route('admin.fine-settings.create')}
                            active={url.startsWith('/admin/fine-settings')}
                            title="Pengaturan Denda"
                            icon={IconSettingsExclamation}
                        />
                    </>
                )}

                {auth.role.some((role) => ['admin'].includes(role)) && (
                    <>
                        {/* Nav Menu Peran & Izin */}
                        <div className="px-3 py-2 text-sm font-semibold text-foreground">Peran & Izin</div>
                        <NavlinkResponsive
                            url={route('admin.roles.index')}
                            active={url.startsWith('/admin/roles')}
                            title="Peran"
                            icon={IconCircleKey}
                        />
                        <NavlinkResponsive
                            url={route('admin.assign-users.index')}
                            active={url.startsWith('/admin/assign-users')}
                            title="Tetapkan Peran"
                            icon={IconLayoutKanban}
                        />
                        <NavlinkResponsive
                            url={route('admin.route-accesses.index')}
                            active={url.startsWith('/admin/route-accesses')}
                            title="Akses Rute"
                            icon={IconRoute}
                        />
                    </>
                )}

                {auth.role.some((role) => ['admin', 'operator', 'accounting'].includes(role)) && (
                    <>
                        {/* Transaksi */}
                        <div className="px-3 py-2 text-sm font-semibold text-foreground">Transaksi</div>
                        <NavlinkResponsive
                            url={route('admin.loans.index')}
                            active={url.startsWith('/admin/loans')}
                            title="Peminjaman"
                            icon={IconCreditCardPay}
                        />
                        <NavlinkResponsive
                            url={route('admin.return-products.index')}
                            active={url.startsWith('/admin/return-products')}
                            title="Pengembalian"
                            icon={IconCreditCardRefund}
                        />
                    </>
                )}

                {auth.role.some((role) => ['member'].includes(role)) && (
                    <>
                        <NavlinkResponsive
                            url={route('front.products.index')}
                            active={url.startsWith('/products')}
                            title="Produk"
                            icon={IconBrandProducthunt}
                        />
                        <NavlinkResponsive
                            url={route('front.categories.index')}
                            active={url.startsWith('/categories')}
                            title="Kategori"
                            icon={IconCategory}
                        />
                        <NavlinkResponsive
                            url={route('front.loans.index')}
                            active={url.startsWith('/loans')}
                            title="Peminjaman"
                            icon={IconCreditCardPay}
                        />
                        <NavlinkResponsive
                            url={route('front.return-products.index')}
                            active={url.startsWith('/return-products')}
                            title="Pengembalian"
                            icon={IconCreditCardRefund}
                        />
                        <NavlinkResponsive
                            url={route('front.fines.index')}
                            active={url.startsWith('/fines')}
                            title="Denda"
                            icon={IconMoneybag}
                        />
                    </>
                )}

                {/* Lainnya */}
                <div className="px-3 py-2 text-sm font-semibold text-foreground">Lainnya</div>
                <NavlinkResponsive
                    url={route('profile.edit')}
                    active={url.startsWith('/admin/profile')}
                    title="Profile"
                    icon={IconUser}
                />
                <NavlinkResponsive
                    url={route('logout')}
                    title="Logout"
                    icon={IconLogout}
                    method="POST"
                    as="button"
                    className="w-full"
                />
            </nav>
        </nav>
  );
}
