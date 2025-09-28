import { Link, usePage } from "@inertiajs/react";
import CurrencyFormatter from "../Core/CurrencyFormatter";
import { PageProps } from "@/types";
import { productRoute } from "@/helpers";

function MiniCartDropdown() {
  const { totalPrice, totalQuantity, miniCartItems } =
    usePage<PageProps>().props;

  return (
    <div className="dropdown dropdown-end">
      {/* Cart button */}
      <div
        tabIndex={0}
        role="button"
        aria-label="Cart"
        className="btn btn-ghost btn-circle"
      >
        <div className="indicator">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 
                 2.293c-.63.63-.184 1.707.707 1.707H17m0 
                 0a2 2 0 100 4 2 2 0 000-4m-8 
                 2a2 2 0 11-4 0 2 2 0 014 0"
            />
          </svg>
          <span className="badge badge-sm indicator-item">{totalQuantity}</span>
        </div>
      </div>

      {/* Dropdown content */}
      <div
        tabIndex={0}
        className="card card-compact dropdown-content bg-base-100 z-[1] mt-3 w-[90vw] sm:w-[420px] md:w-[480px] shadow"
      >
        <div className="card-body">
          {/* Items List */}
          <div className="my-2 max-h-[300px] overflow-auto">
            {miniCartItems.length === 0 && (
              <div className="py-4 text-gray-500 text-center text-sm sm:text-base">
                You don&apos;t have any items yet
              </div>
            )}

            {miniCartItems.map((item) => (
              <div
                key={item.id}
                className="flex gap-3 sm:gap-4 p-2 sm:p-3 border-b last:border-0"
              >
                <Link
                  href={productRoute(item)}
                  className="w-14 h-14 sm:w-16 sm:h-16 flex justify-center"
                >
                  <img
                    src={item.image}
                    alt={item.title}
                    className="h-full w-full object-cover rounded"
                  />
                </Link>
                <div className="flex-1 min-w-0">
                  <h3 className="mb-1 font-semibold text-xs sm:text-sm truncate">
                    <Link href={productRoute(item)}>{item.title}</Link>
                  </h3>
                  <div className="flex text-xs sm:text-sm justify-between">
                    <div>x{item.quantity}</div>
                    <div className="font-medium">
                      <CurrencyFormatter amount={item.quantity * item.price} />
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Summary & actions */}
          <div className="mt-2 border-t pt-2">
            <span className="text-sm sm:text-base font-bold">
              {totalQuantity} items
            </span>
            <span className="text-info block text-sm sm:text-base font-medium">
              <CurrencyFormatter amount={totalPrice} />
            </span>
            <div className="card-actions mt-2">
              <Link
                href={route("cart.index")}
                className="btn btn-primary btn-sm sm:btn-md btn-block"
              >
                View cart
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default MiniCartDropdown;
