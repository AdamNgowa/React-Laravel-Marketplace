import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Order, PageProps } from "@/types";
import { CheckCircleIcon } from "@heroicons/react/16/solid";
import { Head, Link } from "@inertiajs/react";

function Success({ orders }: PageProps<{ orders: Order[] }>) {
  return (
    <AuthenticatedLayout>
      <Head title="Payment was completed" />

      <div className="w-full max-w-2xl mx-auto py-8 px-4">
        {/* Header */}
        <div className="flex flex-col gap-2 items-center text-center mb-8">
          <CheckCircleIcon className="size-24 text-emerald-600" />
          <div className="text-3xl font-semibold">Payment was completed</div>
          <p className="text-lg text-gray-600 dark:text-gray-300">
            Thanks for your purchase. Your payment was completed successfully.
          </p>
        </div>

        {/* Orders */}
        {orders.map((order) => (
          <div
            key={order.id}
            className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6"
          >
            <h3 className="text-2xl font-bold mb-4">Order Summary</h3>

            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-500">Seller</span>
                <Link href="#" className="hover:underline font-medium">
                  {order.vendorUser.store_name}
                </Link>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Order Number</span>
                <Link href="#" className="hover:underline font-medium">
                  #{order.id}
                </Link>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Items</span>
                <span>{order.orderItems.length}</span>
              </div>
              <div className="flex justify-between font-semibold text-lg">
                <span className="text-gray-700">Total</span>
                <CurrencyFormatter amount={order.total_price} />
              </div>
            </div>

            {/* Order Items */}
            <div className="mt-6 border-t pt-4">
              <h4 className="text-lg font-semibold mb-3">Items Purchased</h4>
              <ul className="divide-y divide-gray-200 dark:divide-gray-700">
                {order.orderItems.map((item) => (
                  <li
                    key={item.id}
                    className="flex items-center justify-between py-3"
                  >
                    <div className="flex items-center gap-4">
                      {item.product.image && (
                        <img
                          src={item.product.image}
                          alt={item.product.title}
                          className="w-16 h-16 object-cover rounded"
                        />
                      )}
                      <div>
                        <div className="font-medium">{item.product.title}</div>
                        <div className="text-sm text-gray-500">
                          Qty: {item.quantity}
                        </div>
                      </div>
                    </div>
                    <CurrencyFormatter amount={item.price * item.quantity} />
                  </li>
                ))}
              </ul>
            </div>

            {/* Actions */}
            <div className="flex justify-between mt-6">
              <Link href="#" className="btn btn-primary">
                View Order Details
              </Link>
              <Link href={route("dashboard")} className="btn">
                Back To Home
              </Link>
            </div>
          </div>
        ))}
      </div>
    </AuthenticatedLayout>
  );
}

export default Success;
