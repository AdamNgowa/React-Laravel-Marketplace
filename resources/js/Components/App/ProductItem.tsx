import { Product } from "@/types";
import { Link } from "@inertiajs/react";
import CurrencyFormatter from "../Core/CurrencyFormatter";

function ProductItem({ product }: { product: Product }) {
  return (
    <div className="card bg-base-100 shadow-md hover:shadow-lg transition w-full mx-auto">
      <Link href={route("product.show", product.slug)}>
        <figure className="aspect-square overflow-hidden">
          <img
            src={product.image}
            alt={product.title}
            className="h-full w-full object-cover transform hover:scale-105 transition duration-300"
          />
        </figure>
      </Link>

      <div className="card-body p-4 sm:p-6">
        <h2 className="text-base sm:text-lg font-semibold line-clamp-2">
          {product.title}
        </h2>

        <p className="text-xs sm:text-sm text-gray-600 mt-1">
          by{" "}
          <Link href="/" className="hover:underline font-medium">
            {product.user.name}
          </Link>{" "}
          in{" "}
          <Link href="/" className="hover:underline font-medium">
            {product.department.name}
          </Link>
        </p>

        <div className="card-actions justify-between items-center mt-4">
          <button className="btn btn-primary btn-xs sm:btn-sm md:btn-md">
            Add To Cart
          </button>
          <span className="text-lg sm:text-xl font-bold">
            <CurrencyFormatter amount={product.price} />
          </span>
        </div>
      </div>
    </div>
  );
}

export default ProductItem;
