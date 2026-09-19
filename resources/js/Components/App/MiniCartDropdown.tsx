import { Link, usePage } from "@inertiajs/react";
import CurrencyFormatter from "../Core/CurrencyFormatter";
import { PageProps } from "@/types";
import { productRoute } from "@/helpers";

function MiniCartDropdown() {
  const { totalPrice, totalQuantity, miniCartItems } =
    usePage<PageProps>().props;

  return (
    <div className="relative">
      <div
        tabIndex={0}
        role="button"
        aria-label="Cart"
        className="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50"
      >
        <div className="relative">
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
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4m-8 2a2 2 0 11-4 0 2 2 0 014 0"
            />
          </svg>
          <span className="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-slate-900 px-1 text-[10px] font-semibold text-white">
            {totalQuantity}
          </span>
        </div>
      </div>

      <div className="absolute right-0 z-[1] mt-3 w-[90vw] rounded-lg border border-slate-200 bg-white p-3 shadow-lg sm:w-[420px] md:w-[480px]">
        <div className="space-y-3">
          {miniCartItems.length === 0 && (
            <div className="py-4 text-center text-sm text-slate-500 sm:text-base">
              You don&apos;t have any items yet
            </div>
          )}

          {miniCartItems.map((item) => (
            <div
              key={item.id}
              className="flex gap-3 border-b border-slate-200 pb-3 last:border-b-0 last:pb-0 sm:gap-4"
            >
              <Link
                href={productRoute(item)}
                className="flex h-14 w-14 shrink-0 items-center justify-center sm:h-16 sm:w-16"
              >
                <img
                  src={item.image}
                  alt={item.title}
                  className="h-full w-full rounded object-cover"
                />
              </Link>
              <div className="min-w-0 flex-1">
                <h3 className="truncate text-xs font-semibold sm:text-sm">
                  <Link href={productRoute(item)}>{item.title}</Link>
                </h3>
                <div className="mt-1 flex items-center justify-between text-xs sm:text-sm">
                  <div>x{item.quantity}</div>
                  <div className="font-medium text-slate-700">
                    <CurrencyFormatter amount={item.quantity * item.price} />
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        <div className="mt-3 border-t border-slate-200 pt-3">
          <div className="text-sm font-semibold sm:text-base">
            {totalQuantity} items
          </div>
          <div className="mt-1 text-sm font-medium text-slate-700 sm:text-base">
            <CurrencyFormatter amount={totalPrice} />
          </div>
          <div className="mt-3">
            <Link
              href={route("cart.index")}
              className="block w-full rounded-md bg-slate-900 px-3 py-2 text-center text-sm font-medium text-white transition hover:bg-slate-700"
            >
              View cart
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}

export default MiniCartDropdown;
