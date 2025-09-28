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
      <div className="container mx-auto p-4 sm:p-8 flex flex-col lg:flex-row gap-6">
        {/* Main Cart Section */}
        <div className="card flex-1 bg-white dark:bg-gray-800 order-2 lg:order-1">
          <div className="card-body">
            <h2 className="text-lg font-bold">Shopping Cart</h2>
            <div className="my-4">
              {Object.keys(cartItems).length === 0 && (
                <div className="py-4 text-gray-500 text-center">
                  You don't have any items yet.
                </div>
              )}
              {Object.values(cartItems).map((cartItem) => (
                <div key={cartItem.user.id} className="mb-6">
                  {/* Seller Info + Partial Checkout */}
                  <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-4 border-b border-gray-300 mb-4 gap-3">
                    <Link className="underline" href="/">
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
                      <button className="btn btn-sm btn-ghost flex items-center gap-1 w-full sm:w-auto">
                        <CreditCardIcon className="size-5" />
                        Pay only for this seller
                      </button>
                    </form>
                  </div>

                  {/* Seller's Items */}
                  {cartItem.items.map((item) => (
                    <CartItem item={item} key={item.id} />
                  ))}
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Sidebar (Checkout Summary) */}
        <div className="card bg-white dark:bg-gray-800 lg:min-w-[260px] order-1 lg:order-2">
          <div className="card-body sticky top-4">
            <div className="text-sm sm:text-base">
              Subtotal ({totalQuantity} items):{" "}
              <span className="font-bold">
                <CurrencyFormatter amount={totalPrice} />
              </span>
            </div>
            <form action={route("cart.checkout")} method="post">
              <input type="hidden" name="_token" value={csrf_token} />
              <PrimaryButton className="rounded-full flex items-center gap-2 w-full mt-4">
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
