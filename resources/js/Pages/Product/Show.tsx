import Carousel from "@/Components/Core/Carousel";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps, Product, VariationTypeOption } from "@/types";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import { arraysAreEqual } from "@/helpers";

function Show({
  appName,
  product,
  variationOptions,
}: PageProps<{
  product: Product;
  variationOptions: number[];
}>) {
  const form = useForm<{
    option_ids: Record<string, number>;
    quantity: number;
    price: number | null;
  }>({
    option_ids: {},
    quantity: 1,
    price: null,
  });

  const { url } = usePage();
  const [selectedOptions, setSelectedOptions] = useState<
    Record<number, VariationTypeOption>
  >({});

  const images = useMemo(() => {
    for (let typeId in selectedOptions) {
      const option = selectedOptions[typeId];
      if (option.images.length > 0) return option.images;
    }
    return product.images;
  }, [product, selectedOptions]);

  const computedProduct = useMemo(() => {
    const selectedOptionIds = Object.values(selectedOptions)
      .map((op) => op.id)
      .sort();

    for (let variation of product.variations) {
      const optionIds = variation.variation_type_option_ids.sort();
      if (arraysAreEqual(selectedOptionIds, optionIds)) {
        return {
          price: variation.price,
          quantity:
            variation.quantity === null ? Number.MAX_VALUE : variation.quantity,
        };
      }
    }
    return {
      price: product.price,
      quantity: product.quantity,
    };
  }, [product, selectedOptions]);

  useEffect(() => {
    for (let type of product.variationTypes) {
      const selectedOptionId: number = variationOptions[type.id];
      chooseOption(
        type.id,
        type.options.find((op) => op.id == selectedOptionId) || type.options[0],
        false
      );
    }
  }, []);

  const getOptionIdsMap = (newOptions: object) => {
    return Object.fromEntries(
      Object.entries(newOptions).map(([a, b]) => [a, b.id])
    );
  };

  const chooseOption = (
    typeId: number,
    option: VariationTypeOption,
    updateRouter: boolean = true
  ) => {
    setSelectedOptions((prevSelectedOptions) => {
      const newOptions = {
        ...prevSelectedOptions,
        [typeId]: option,
      };

      if (updateRouter) {
        router.get(
          url,
          {
            options: getOptionIdsMap(newOptions),
          },
          {
            preserveScroll: true,
            preserveState: true,
          }
        );
      }

      return newOptions;
    });
  };

  const onQuantityChange = (ev: React.ChangeEvent<HTMLSelectElement>) => {
    form.setData("quantity", parseInt(ev.target.value));
  };

  const addToCart = () => {
    form.post(route("cart.store", product.id), {
      preserveScroll: true,
      preserveState: true,
    });
  };

  const renderProductVariationTypes = () => {
    return product.variationTypes.map((type) => (
      <div key={type.id} className="mt-4">
        <b className="block mb-2 text-sm sm:text-base">{type.name}</b>

        {type.type === "Image" && (
          <div className="flex flex-wrap gap-3 mb-4">
            {type.options.map((option) => (
              <div
                key={option.id}
                onClick={() => chooseOption(type.id, option)}
                className="cursor-pointer"
              >
                {option.images?.[0] && (
                  <img
                    src={option.images[0].url}
                    alt={option.name}
                    className={
                      "w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-md border-2 transition-all duration-200 " +
                      (selectedOptions[type.id]?.id === option.id
                        ? "border-primary ring-2 ring-primary"
                        : "border-gray-300 hover:border-primary/50")
                    }
                  />
                )}
              </div>
            ))}
          </div>
        )}

        {type.type === "Radio" && (
          <div className="flex flex-wrap gap-3 mb-4">
            {type.options.map((option) => (
              <label
                key={option.id}
                className="flex items-center gap-2 cursor-pointer"
              >
                <input
                  onChange={() => chooseOption(type.id, option)}
                  type="radio"
                  value={option.id}
                  checked={selectedOptions[type.id]?.id === option.id}
                  name={"variation_type_" + type.id}
                  className="radio radio-primary"
                />
                <span className="text-sm sm:text-base">{option.name}</span>
              </label>
            ))}
          </div>
        )}
      </div>
    ));
  };

  return (
    <AuthenticatedLayout>
      <Head title={product.title} />

      <div className="container mx-auto px-4 sm:px-6 py-6">
        <div className="grid gap-8 grid-cols-1 lg:grid-cols-12">
          {/* Left Column: Carousel */}
          <div className="lg:col-span-6">
            <Carousel images={images} />
          </div>

          {/* Right Column: Product Details */}
          <div className="lg:col-span-6 flex flex-col gap-5 sm:gap-6">
            <h1 className="text-xl sm:text-2xl font-bold">{product.title}</h1>

            <p className="text-xs sm:text-sm text-white mt-1">
              by{" "}
              <Link
                href={route("vendor.profile", product.user.store_name)}
                className="hover:underline font-medium"
              >
                {product.user.name}
              </Link>{" "}
              in{" "}
              <Link
                href={route("product.byDepartment", product.department.slug)}
                className="hover:underline font-medium"
              >
                {product.department.name}
              </Link>
            </p>

            <div className="text-2xl sm:text-3xl font-semibold">
              <CurrencyFormatter amount={computedProduct.price} />
            </div>

            {renderProductVariationTypes()}

            {computedProduct.quantity < 10 && (
              <div className="text-error my-1 sm:my-2 text-sm">
                Only {computedProduct.quantity} left in stock!
              </div>
            )}

            {/* Quantity + Add to Cart */}
            <div className="flex flex-col sm:flex-row gap-3 sm:gap-4 mb-6">
              <select
                value={form.data.quantity}
                onChange={onQuantityChange}
                className="select select-bordered w-full sm:w-auto"
              >
                {Array.from({
                  length: Math.min(10, computedProduct.quantity),
                }).map((_, i) => (
                  <option value={i + 1} key={i + 1}>
                    Quantity: {i + 1}
                  </option>
                ))}
              </select>

              <button
                onClick={addToCart}
                className="btn btn-primary w-full sm:w-auto"
              >
                Add To Cart
              </button>
            </div>

            <div>
              <h2 className="text-lg sm:text-xl font-bold mt-4 mb-2">
                About the Item
              </h2>
              <div
                className="wysiwyg-output prose max-w-none text-sm sm:text-base"
                dangerouslySetInnerHTML={{ __html: product.description }}
              />
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

export default Show;
