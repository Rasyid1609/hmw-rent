import HeaderTitle from '@/Components/HeaderTitle';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { flashMessage } from '@/lib/utils';
import { Link, useForm } from '@inertiajs/react';
import { IconArrowLeft, IconBooks, IconDevices } from '@tabler/icons-react';
import { useRef } from 'react';
import { toast } from 'sonner';

export default function Edit(props) {
    const fileInputCover = useRef(null);

    const { data, setData, reset, post, processing, errors } = useForm({
        title: props.product.title ?? '',
        description: props.product.description ?? '',
        cover: null,
        price: props.product.price ?? 0,
        category_id: props.product.category_id ?? null,
        brand_id: props.product.brand_id ?? null,
        _method: props.page_settings.method,
    });

    const onHandleChange = (e) => setData(e.target.name, e.target.value);

    const onHandleSubmit = (e) => {
        e.preventDefault();
        post(props.page_settings.action, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (success) => {
                const flash = flashMessage(success);
                if (flash) toast[flash.type](flash.message);
            },
        });
    };

    const onHandleReset = () => {
        reset();
        fileInputCover.current.value = null;
    };

    return (
        <div className="flex w-full flex-col pb-32">
            <div className="gp-y-4 mb-8 flex flex-col items-start justify-between lg:flex-row lg:items-center">
                <HeaderTitle
                    title={props.page_settings.title}
                    subTitle={props.page_settings.subtitle}
                    icon={IconDevices}
                />
                <Button variant="orange" size="lg" asChild>
                    <Link href={route('admin.products.index')}>
                        <IconArrowLeft className="size-4" />
                        Kembali
                    </Link>
                </Button>
            </div>
            <Card>
                            <CardContent className="p-6">
                                <form className="space-y-6" onSubmit={onHandleSubmit}>
                                    <div className="grid w-full items-center gap-1.5">
                                        <Label htmlFor="title">Nama Barang</Label>
                                        <Input
                                            name="title"
                                            id="title"
                                            type="text"
                                            placeholder="Masukkan barang..."
                                            value={data.title}
                                            onChange={onHandleChange}
                                        />
                                        {errors.title && <InputError message={errors.title} />}
                                    </div>

                                    <div className="grid w-full items-center gap-1.5">
                                        <Label htmlFor="description">Deskripsi</Label>
                                        <Textarea
                                            name="description"
                                            id="description"
                                            placeholder="Masukkan deskripsi..."
                                            value={data.description}
                                            onChange={onHandleChange}
                                        ></Textarea>
                                        {errors.description && <InputError message={errors.description} />}
                                    </div>

                                    <div className="grid w-full items-center gap-1.5">
                                        <Label htmlFor="release_year">Tahun Terbit</Label>
                                        <Input
                                            name="release_year"
                                            id="release_year"
                                            type="number"
                                            placeholder="Masukkan tahun terbit..."
                                            value={data.release_year}
                                            onChange={onHandleChange}
                                        />
                                        {errors.title && <InputError message={errors.title} />}
                                    </div>

                                    <div className="grid w-full items-center gap-1.5">
                                        <Label htmlFor="cover">Cover</Label>
                                        <Input
                                            name="cover"
                                            id="cover"
                                            type="file"
                                            onChange={(e) => setData(e.target.name, e.target.files[0])}
                                            ref={fileInputCover}
                                        />
                                        {errors.cover && <InputError message={errors.cover} />}
                                    </div>

                                    <div className="grid w-full items-center gap-1.5">
                                        <Label htmlFor="price">Harga</Label>
                                        <Input
                                            name="price"
                                            id="price"
                                            type="number"
                                            placeholder="Masukkan harga..."
                                            value={data.price}
                                            onChange={onHandleChange}
                                        />
                                        {errors.price && <InputError message={errors.price} />}
                                    </div>

                                    <div className="grid w-full items-center gap-1.5">
                                        <Label htmlFor="category_id">Kategori</Label>
                                        <Select
                                            defaultValue={data.category_id}
                                            onValueChange={(value) => setData('category_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue>
                                                    {props.page_data.categories.find(
                                                        (category) => category.value == data.category_id,
                                                    )?.label ?? 'Pilih Kategori'}
                                                </SelectValue>
                                            </SelectTrigger>
                                            <SelectContent>
                                                {props.page_data.categories.map((category, index) => (
                                                    <SelectItem key={index} value={category.value}>
                                                        {category.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.category_id && <InputError message={errors.category_id} />}
                                    </div>

                                    <div className="grid w-full items-center gap-1.5">
                                        <Label htmlFor="brand_id">Brand</Label>
                                        <Select
                                            defaultValue={data.brand_id}
                                            onValueChange={(value) => setData('brand_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue>
                                                    {props.page_data.brands.find(
                                                        (brands) => brands.value == data.brand_id,
                                                    )?.label ?? 'Pilih Brands'}
                                                </SelectValue>
                                            </SelectTrigger>
                                            <SelectContent>
                                                {props.page_data.brands.map((brands, index) => (
                                                    <SelectItem key={index} value={brands.value}>
                                                        {brands.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.brand_id && <InputError message={errors.brand_id} />}
                                    </div>

                                    <div className="grid w-full items-center gap-1.5">
                                        <Label htmlFor="total">Stock</Label>
                                        <Input
                                            name="total"
                                            id="total"
                                            type="number"
                                            placeholder="Masukkan total stok..."
                                            min="0"
                                            value={data.total}
                                            onChange={onHandleChange}
                                        />
                                        {errors.total && <InputError message={errors.total} />}
                                    </div>

                                    <div className="flex justify-end gap-2">
                                        <Button type="button" variant="ghost" size="lg" onClick={onHandleReset}>
                                            Reset
                                        </Button>
                                        <Button type="submit" variant="orange" size="lg">
                                            Save
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
        </div>
    );
}

Edit.layout = (page) => <AppLayout children={page} title={page.props.page_settings.title} />;
