import { Link, usePage } from "@inertiajs/react";
import { PropsWithChildren, ReactNode, useState } from "react";
import NavBar from "@/Components/App/NavBar";

export default function AuthenticatedLayout({
  header,
  children,
}: PropsWithChildren<{ header?: ReactNode }>) {
  const props = usePage().props;
  const user = props.auth.user;

  const [showingNavigationDropdown, setShowingNavigationDropdown] =
    useState(false);

  return (
    <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
      <NavBar />
      {props.error && (
        <div className="container mx-auto px-8 mt-8 text-red-500">
          <div className="alert alert-danger">{props.error}</div>
        </div>
      )}

      <main>{children}</main>
    </div>
  );
}
