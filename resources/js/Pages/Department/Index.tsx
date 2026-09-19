import ProductItem from "@/Components/App/ProductItem";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Department, PageProps, PaginationProps, Product } from "@/types";
import { Head } from "@inertiajs/react";

function Index({
  appName,
  department,
  products,
}: PageProps<{ department: Department; products: PaginationProps<Product> }>) {
  return (
    <AuthenticatedLayout>
      <Head>
        <title>{department.name}</title>
        <meta name="title" content={department.meta_title} />
        <link
          rel="canonical"
          href={route("product.byDepartment", department.slug)}
        />
        <meta property="og:title" content={department.name} />
        <meta property="og:description" content={department.meta_description} />
        <meta
          property="og:url"
          content={route("product.byDepartment", department.slug)}
        />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content={appName} />
      </Head>
      <div className="container mx-auto">
        <div className="rounded-xl bg-slate-200 px-6 py-10 text-center shadow-sm">
          <h1 className="text-3xl font-bold text-slate-900 sm:text-5xl">
            {department.name}
          </h1>
        </div>
        {products.data.length === 0 && (
          <div className="py-16 px-6 text-center text-2xl text-slate-400">
            No Products found
          </div>
        )}
        <div className="mt-6 grid grid-cols-1 gap-8 p-2 md:grid-cols-2 lg:grid-cols-3">
          {products.data.map((product) => (
            <ProductItem product={product} key={product.id} />
          ))}{" "}
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
export default Index;
