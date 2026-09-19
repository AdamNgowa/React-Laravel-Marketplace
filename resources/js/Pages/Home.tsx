import ProductItem from "@/Components/App/ProductItem";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps, PaginationProps, Product } from "@/types";
import { Head, Link } from "@inertiajs/react";

export default function Home({
  products,
}: PageProps<{ products: PaginationProps<Product> }>) {
  return (
    <AuthenticatedLayout>
      <Head title="Home" />
      <div className="min-h-[300px] rounded-xl bg-slate-800 px-6 py-12 text-white shadow-sm">
        <div className="mx-auto max-w-xl text-center">
          <h1 className="text-4xl font-bold sm:text-5xl">Hello there</h1>
          <p className="mt-4 text-slate-200">
            Welcome to our store! Explore our wide range of products and find
            what you need.
          </p>
          <button className="mt-6 rounded-md bg-white px-4 py-2 text-sm font-medium text-slate-900 transition hover:bg-slate-100">
            Get Started
          </button>
        </div>
      </div>

      <div className="mt-6 grid grid-cols-1 gap-4 p-2 md:grid-cols-2 lg:grid-cols-4">
        {products.data.map((product) => (
          <ProductItem product={product} key={product.id} />
        ))}
      </div>
    </AuthenticatedLayout>
  );
}
