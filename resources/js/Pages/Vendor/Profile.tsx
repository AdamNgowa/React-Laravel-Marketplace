import ProductItem from "@/Components/App/ProductItem";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps, PaginationProps, Product, Vendor } from "@/types";
import { Head } from "@inertiajs/react";

function Profile({
  vendor,
  products,
}: PageProps<{ vendor: Vendor; products: PaginationProps<Product> }>) {
  return (
    <AuthenticatedLayout>
      <Head title={vendor.store_name + "Profile Page"} />
      <div
        className="relative min-h-[320px] overflow-hidden rounded-xl bg-slate-800"
        style={{
          backgroundImage:
            "url(https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1200&q=80)",
          backgroundSize: "cover",
          backgroundPosition: "center",
        }}
      >
        <div className="absolute inset-0 bg-slate-900/60" />
        <div className="relative flex min-h-[320px] items-center justify-center text-center text-white">
          <div className="max-w-md px-6">
            <h1 className="text-4xl font-bold sm:text-5xl">
              {vendor.store_name}
            </h1>
          </div>
        </div>
      </div>
      <div className="container mx-auto">
        <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 p-8">
          {products.data.map((product) => (
            <ProductItem product={product} key={product.id} />
          ))}
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
export default Profile;
