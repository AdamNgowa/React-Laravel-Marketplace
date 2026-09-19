import { Link, useForm, usePage } from "@inertiajs/react";
import MiniCartDropdown from "./MiniCartDropdown";
import { FormEventHandler, useState } from "react";
import { PageProps } from "@/types";
import {
  MagnifyingGlassIcon,
  Bars3Icon,
  XMarkIcon,
} from "@heroicons/react/24/outline";

function NavBar() {
  const { auth, departments, keyword } = usePage<PageProps>().props;
  const { user } = auth;

  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);

  const searchForm = useForm<{ keyword: string }>({
    keyword: keyword || "",
  });

  const { url } = usePage();

  const onSubmit: FormEventHandler = (e) => {
    e.preventDefault();
    searchForm.get(url, {
      preserveScroll: true,
      preserveState: true,
    });
  };

  return (
    <>
      <div className="sticky top-0 z-50 border-b border-slate-200 bg-white shadow-sm">
        <div className="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 sm:px-6">
          <div className="flex flex-1 items-center gap-2">
            <button
              className="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 lg:hidden"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              aria-label="Toggle menu"
            >
              {mobileMenuOpen ? (
                <XMarkIcon className="h-5 w-5" />
              ) : (
                <Bars3Icon className="h-5 w-5" />
              )}
            </button>

            <Link href="/" className="text-xl font-semibold text-slate-900">
              LaraStore
            </Link>
          </div>

          <div className="hidden flex-1 justify-center sm:flex">
            <form
              onSubmit={onSubmit}
              className="flex w-full max-w-md md:max-w-lg"
            >
              <input
                value={searchForm.data.keyword}
                onChange={(e) => searchForm.setData("keyword", e.target.value)}
                className="w-full rounded-l-md border border-r-0 border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-500"
                placeholder="Search"
              />
              <button
                type="submit"
                className="rounded-r-md border border-slate-300 bg-slate-100 px-3 text-slate-700 transition hover:bg-slate-200"
              >
                <MagnifyingGlassIcon className="h-4 w-4" />
              </button>
            </form>
          </div>

          <div className="flex items-center gap-3">
            <MiniCartDropdown />

            {user ? (
              <div className="relative">
                <button
                  type="button"
                  aria-expanded={userMenuOpen}
                  onClick={() => setUserMenuOpen((prev) => !prev)}
                  className="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-700"
                >
                  {user.name}
                </button>
                {userMenuOpen && (
                  <div className="absolute right-0 mt-2 w-52 rounded-md border border-slate-200 bg-white p-2 shadow-lg">
                    <Link
                      href={route("profile.update")}
                      className="block rounded px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
                      onClick={() => setUserMenuOpen(false)}
                    >
                      Profile
                    </Link>
                    <Link
                      href={route("logout")}
                      as="button"
                      method="post"
                      className="block w-full rounded px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100"
                      onClick={() => setUserMenuOpen(false)}
                    >
                      Logout
                    </Link>
                  </div>
                )}
              </div>
            ) : (
              <div className="hidden gap-2 sm:flex">
                <Link
                  href={route("login")}
                  className="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                  Login
                </Link>
                <Link
                  href={route("register")}
                  className="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-700"
                >
                  Register
                </Link>
              </div>
            )}
          </div>
        </div>

        <div className="sm:hidden border-t border-slate-200 bg-white px-3 py-2">
          <form onSubmit={onSubmit} className="flex w-full">
            <input
              value={searchForm.data.keyword}
              onChange={(e) => searchForm.setData("keyword", e.target.value)}
              className="w-full rounded-l-md border border-r-0 border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-500"
              placeholder="Search"
            />
            <button
              type="submit"
              className="rounded-r-md border border-slate-300 bg-slate-100 px-3 text-slate-700 transition hover:bg-slate-200"
            >
              <MagnifyingGlassIcon className="h-4 w-4" />
            </button>
          </form>
        </div>

        <div
          className={`overflow-hidden border-t border-slate-200 bg-white transition-all duration-300 lg:overflow-visible lg:max-h-none lg:border-t-0 ${
            mobileMenuOpen ? "max-h-96 py-2" : "max-h-0 py-0"
          }`}
        >
          <div className="mx-auto max-w-7xl px-3 py-2 lg:px-6">
            <ul className="flex flex-col gap-1 text-center lg:flex-row lg:items-center lg:justify-center lg:gap-2">
              {departments.map((department) => (
                <li key={department.id}>
                  <Link
                    href={route("product.byDepartment", department.slug)}
                    onClick={() => setMobileMenuOpen(false)}
                    className="block rounded-md px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-100 hover:text-slate-900"
                  >
                    {department.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </>
  );
}

export default NavBar;
