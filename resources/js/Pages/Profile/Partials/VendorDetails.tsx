import InputError from "@/Components/Core/InputError";
import InputLabel from "@/Components/Core/InputLabel";
import Modal from "@/Components/Core/Modal";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import SecondaryButton from "@/Components/Core/SecondaryButton";
import TextInput from "@/Components/Core/TextInput";
import { useForm, usePage } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

export default function VendorDetails({
  className = "",
}: {
  className?: string;
}) {
  const [showBecomeVendorConfirmation, setShowBecomeVendorConfirmation] =
    useState(false);
  const [successMessage, setSuccessMessage] = useState("");

  const user = usePage().props.auth.user;
  const token = usePage().props.csrf_token;

  const { data, setData, errors, post, processing, recentlySuccessful } =
    useForm({
      store_name: user.vendor?.store_name || user.name,
      store_address: user.vendor?.store_address || "",
    });

  /** Format store name as slug */
  const onStoreNameChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
    const formattedValue = ev.target.value.toLowerCase().replace(/\s+/g, "-");
    setData("store_name", formattedValue);
  };

  /** Submit new vendor request */
  const becomeVendor: FormEventHandler = (ev) => {
    ev.preventDefault();
    post(route("vendor.store"), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        setSuccessMessage("You can now create and publish products.");
      },
    });
  };

  /** Update existing vendor details */
  const updateVendor: FormEventHandler = (ev) => {
    ev.preventDefault();
    post(route("vendor.store"), {
      preserveScroll: true,
      onSuccess: () => {
        setSuccessMessage("Your details were updated.");
      },
    });
  };

  const closeModal = () => {
    setShowBecomeVendorConfirmation(false);
  };

  return (
    <section className={className}>
      {recentlySuccessful && successMessage && (
        <div className="fixed right-4 top-20 z-[1000]">
          <div className="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-lg">
            <span>{successMessage}</span>
          </div>
        </div>
      )}

      <header>
        <h2 className="flex justify-between mb-8 text-lg font-medium text-gray-900 dark:text-gray-100">
          Vendor Details
          {user.vendor?.status && (
            <span
              className={`rounded-full px-2.5 py-1 text-xs font-medium ${
                user.vendor.status === "pending"
                  ? "bg-amber-100 text-amber-700"
                  : user.vendor.status === "rejected"
                    ? "bg-red-100 text-red-700"
                    : "bg-emerald-100 text-emerald-700"
              }`}
            >
              {user.vendor.status_label}
            </span>
          )}
        </h2>
      </header>

      <div>
        {/* Become Vendor Button */}
        {!user.vendor && (
          <PrimaryButton
            disabled={processing}
            onClick={() => setShowBecomeVendorConfirmation(true)}
          >
            Become a Vendor
          </PrimaryButton>
        )}

        {/* Vendor Update Form */}
        {user.vendor && (
          <>
            <form onSubmit={updateVendor}>
              <div className="mb-4">
                <InputLabel htmlFor="store_name" value="Store Name " />
                <TextInput
                  id="store_name"
                  className="mt-1 block w-full"
                  value={data.store_name}
                  onChange={onStoreNameChange}
                  required
                  autoComplete="off"
                />
                <InputError className="mt-2" message={errors.store_name} />
              </div>

              <div className="mb-4">
                <InputLabel htmlFor="store_address" value="Store Address" />
                <textarea
                  id="store_address"
                  className="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                  value={data.store_address}
                  onChange={(e) => setData("store_address", e.target.value)}
                  placeholder="Enter your store address"
                ></textarea>
                <InputError className="mt-2" message={errors.store_address} />
              </div>

              <div className="flex items-center gap-4">
                <PrimaryButton disabled={processing}>Update</PrimaryButton>
              </div>
            </form>

            {!user.stripe_account_id ? (
              <form
                action={route("stripe.connect")}
                method="post"
                className="my-8"
              >
                <input type="hidden" name="_token" value={token} />
                <PrimaryButton type="submit" className="w-full">
                  Connect with Stripe
                </PrimaryButton>
              </form>
            ) : (
              <div className="flex justify-center my-6">
                <span className="px-4 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-lg">
                  ✅ Your Stripe account is connected.
                </span>
              </div>
            )}
          </>
        )}
      </div>

      {/* Become Vendor Confirmation Modal */}
      <Modal show={showBecomeVendorConfirmation} onClose={closeModal}>
        <form onSubmit={becomeVendor} className="p-8">
          <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
            Are you sure you want to become a vendor?
          </h2>
          <div className="mt-6 flex justify-end">
            <SecondaryButton onClick={closeModal}>Cancel</SecondaryButton>
            <PrimaryButton disabled={processing} className="ms-6">
              Confirm
            </PrimaryButton>
          </div>
        </form>
      </Modal>
    </section>
  );
}
