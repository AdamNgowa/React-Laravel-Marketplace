import { Product } from "@/types";
import { Link } from "@inertiajs/react";
import CurrencyFormatter from "../Core/CurrencyFormatter";

function ProductItem({ product }: { product: Product }) {
  return (
    <div className="w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
      <Link href={route("product.show", product.slug)}>
        <div className="aspect-square overflow-hidden bg-slate-100">
          <img
            src={product.image}
            alt={product.title}
            className="h-full w-full object-cover transition duration-300 hover:scale-105"
          />
        </div>
      </Link>

      <div className="space-y-3 p-4 sm:p-5">
        <h2 className="line-clamp-2 text-base font-semibold text-slate-900 sm:text-lg">
          {product.title}
        </h2>

        <p className="text-xs text-slate-600 sm:text-sm">
          by{" "}
          <Link
            href={route("vendor.profile", product.user.store_name)}
            className="font-medium text-slate-800 hover:underline"
          >
            {product.user.name}
          </Link>{" "}
          in{" "}
          <Link
            href={route("product.byDepartment", product.department.slug)}
            className="font-medium text-slate-800 hover:underline"
          >
            {product.department.name}
          </Link>
        </p>

        <div className="flex items-center justify-between gap-3 pt-2">
          <Link
            href={route("product.show", product.slug)}
            className="inline-flex items-center justify-center rounded-md bg-slate-900 px-3 py-2 text-xs font-medium text-white transition hover:bg-slate-700 sm:text-sm"
          >
            Choose Options
          </Link>

          <span className="text-lg font-bold text-slate-900 sm:text-xl">
            <CurrencyFormatter amount={product.price} />
          </span>
        </div>
      </div>
    </div>
  );
}

export default ProductItem;
