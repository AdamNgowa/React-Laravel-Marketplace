import CartItem from "@/Components/App/CartItem";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { GroupedCartItems, PageProps } from "@/types";
import CreditCardIcon from "@heroicons/react/24/outline/CreditCardIcon";
import { Head, Link } from "@inertiajs/react";

function Index({
  csrf_token,
  cartItems,
  totalPrice,
  totalQuantity,
}: PageProps<{
  cartItems: Record<number, GroupedCartItems>;
}>) {
  return (
    <AuthenticatedLayout>
      <Head title="Your Cart" />
      <div className="container mx-auto flex flex-col gap-6 p-4 sm:p-8 lg:flex-row">
        <div className="order-2 flex-1 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:order-1">
          <h2 className="text-lg font-bold text-slate-900">Shopping Cart</h2>
          <div className="my-4">
            {Object.keys(cartItems).length === 0 && (
              <div className="py-4 text-center text-slate-500">
                You don't have any items yet.
              </div>
            )}
            {Object.values(cartItems).map((cartItem) => (
              <div key={cartItem.user.id} className="mb-6">
                <div className="mb-4 flex flex-col items-start justify-between gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center">
                  <Link className="text-slate-700 underline" href="/">
                    {cartItem.user.name}
                  </Link>
                  <form
                    action={route("cart.checkout")}
                    method="post"
                    className="w-full sm:w-auto"
                  >
                    <input type="hidden" name="_token" value={csrf_token} />
                    <input
                      type="hidden"
                      name="vendor_id"
                      value={cartItem.user.id}
                    />
                    <button className="flex w-full items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 sm:w-auto">
                      <CreditCardIcon className="size-5" />
                      Pay only for this seller
                    </button>
                  </form>
                </div>

                {cartItem.items.map((item) => (
                  <CartItem item={item} key={item.id} />
                ))}
              </div>
            ))}
          </div>
        </div>

        <div className="order-1 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:order-2 lg:min-w-[260px]">
          <div className="sticky top-4">
            <div className="text-sm text-slate-700 sm:text-base">
              Subtotal ({totalQuantity} items):{" "}
              <span className="font-bold text-slate-900">
                <CurrencyFormatter amount={totalPrice} />
              </span>
            </div>
            <form action={route("cart.checkout")} method="post">
              <input type="hidden" name="_token" value={csrf_token} />
              <PrimaryButton className="mt-4 flex w-full items-center justify-center gap-2 rounded-full">
                <CreditCardIcon className="size-5 sm:size-6" />
                Proceed To Checkout
              </PrimaryButton>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
export default Index;
