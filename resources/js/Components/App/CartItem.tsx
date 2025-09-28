import { productRoute } from "@/helpers";
import { CartItem as CartItemType } from "@/types";
import { Link, router, useForm } from "@inertiajs/react";
import TextInput from "../Core/TextInput";
import { useState } from "react";
import CurrencyFormatter from "../Core/CurrencyFormatter";

function CartItem({ item }: { item: CartItemType }) {
  const deleteForm = useForm({ option_ids: item.option_ids });
  const [error, setError] = useState("");

  const onDeleteClick = () => {
    deleteForm.delete(route("cart.destroy", item.product_id), {
      preserveScroll: true,
    });
  };

  const handleQuantityChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
    setError("");
    router.put(
      route("cart.update", item.product_id),
      {
        quantity: ev.target.value,
        option_ids: item.option_ids,
      },
      {
        preserveScroll: true,
        onError: (errors) => {
          setError(Object.values(errors)[0]);
        },
      }
    );
  };

  return (
    <>
      <div
        key={item.id}
        className="flex flex-col sm:flex-row gap-4 sm:gap-6 p-3"
      >
        {/* Product Image */}
        <Link
          href={productRoute(item)}
          className="w-full sm:w-32 sm:min-w-32 sm:min-h-32 flex justify-center self-start"
        >
          <img
            src={item.image}
            className="max-w-full max-h-32 object-contain"
            alt={item.title}
          />
        </Link>

        {/* Product Info */}
        <div className="flex flex-col flex-1">
          <div className="flex-1">
            <h3 className="mb-2 text-sm font-semibold">
              <Link href={productRoute(item)}> {item.title} </Link>
            </h3>
            <div className="text-xs flex flex-wrap gap-2">
              {item.options.map((option) => (
                <div key={option.id}>
                  <strong>{option.type.name}</strong>: {option.name}
                </div>
              ))}
            </div>
          </div>

          {/* Actions + Price */}
          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-4 gap-3">
            <div className="flex flex-wrap gap-2 items-center">
              <div className="text-sm">Quantity:</div>
              <div
                className={error ? "tooltip tooltip-open tooltip-error " : ""}
                data-tip={error}
              >
                <TextInput
                  type="number"
                  defaultValue={item.quantity}
                  onBlur={handleQuantityChange}
                  className="input-sm w-16"
                />
              </div>
              <button onClick={onDeleteClick} className="btn btn-sm btn-ghost">
                Delete
              </button>
              <button className="btn btn-sm btn-ghost">Save For Later</button>
            </div>

            <div className="font-bold text-base sm:text-lg">
              <CurrencyFormatter amount={item.price * item.quantity} />
            </div>
          </div>
        </div>
      </div>
      <div className="divider"></div>
    </>
  );
}
export default CartItem;
